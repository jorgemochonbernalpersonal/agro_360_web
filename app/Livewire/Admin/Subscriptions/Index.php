<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithReadOnlyGuard, WithToastNotifications;

    public string $search = '';

    public string $filterStatus = 'all';

    public string $filterPlan = 'all';

    protected $queryString = ['search', 'filterStatus', 'filterPlan'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlan(): void
    {
        $this->resetPage();
    }

    public function cancelSubscription(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $subscription = Subscription::with('user')->findOrFail($id);

        if ($subscription->status === Subscription::STATUS_CANCELLED) {
            $this->toastError(__('La suscripción ya está cancelada.'));

            return;
        }

        SecurityLogger::logSecurityEvent('subscription_cancelled_by_admin', [
            'admin_id' => Auth::id(),
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'user_email' => $subscription->user?->email,
            'plan_type' => $subscription->plan_type,
            'amount' => $subscription->amount,
        ]);

        $subscription->cancel();
        $this->toastSuccess(__('Suscripción cancelada correctamente.'));
    }

    public function exportCsv(): mixed
    {
        $subscriptions = Subscription::with('user')
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPlan !== 'all', fn ($q) => $q->where('plan_type', $this->filterPlan))
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($subscriptions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Usuario', 'Email', 'Plan', 'Estado', 'Importe (€)', 'Inicio', 'Fin', 'Cancelado', 'PayPal ID']);
            foreach ($subscriptions as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->user->name ?? '',
                    $s->user->email ?? '',
                    $s->plan_type === 'monthly' ? 'Mensual' : 'Anual',
                    $s->status,
                    number_format((float) $s->amount, 2),
                    $s->starts_at->format('d/m/Y') ?? '',
                    $s->ends_at->format('d/m/Y') ?? '',
                    $s->cancelled_at?->format('d/m/Y') ?? '',
                    $s->paypal_subscription_id ?? '',
                ]);
            }
            fclose($handle);
        }, 'suscripciones-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Subscription::with('user:id,name,email,role')
            ->when($this->search, function ($q) {
                $term = '%'.mb_strtolower($this->search).'%';
                $q->whereHas('user', fn ($uq) => $uq
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                );
            })
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPlan !== 'all', fn ($q) => $q->where('plan_type', $this->filterPlan))
            ->orderByDesc('created_at');

        $subscriptions = $query->paginate(20);

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', Subscription::STATUS_ACTIVE)->count(),
            'cancelled' => Subscription::where('status', Subscription::STATUS_CANCELLED)->count(),
            'expired' => Subscription::where('status', Subscription::STATUS_EXPIRED)->count(),
            'revenue_total' => (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'revenue_this_year' => (float) Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        // Monthly revenue breakdown for current year
        $monthlyRevenue = Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereYear('paid_at', now()->year)
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as payments')
            )
            ->groupBy(DB::raw('MONTH(paid_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyNewSubs = Subscription::whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as new_subs')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyCancelled = Subscription::whereYear('cancelled_at', now()->year)
            ->whereNotNull('cancelled_at')
            ->select(
                DB::raw('MONTH(cancelled_at) as month'),
                DB::raw('COUNT(*) as cancelled')
            )
            ->groupBy(DB::raw('MONTH(cancelled_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyStats = collect(range(1, now()->month))->map(fn ($m) => [
            'month' => $m,
            'label' => now()->setMonth($m)->translatedFormat('M'),
            'revenue' => (float) ($monthlyRevenue[$m]->revenue ?? 0),
            'payments' => (int) ($monthlyRevenue[$m]->payments ?? 0),
            'new_subs' => (int) ($monthlyNewSubs[$m]->new_subs ?? 0),
            'cancelled' => (int) ($monthlyCancelled[$m]->cancelled ?? 0),
        ]);

        $maxRevenue = $monthlyStats->max('revenue') ?: 1;

        // Cohort retention: group by start month (last 6 months), show active/cancelled/churn
        $cohortData = Subscription::select(
            DB::raw('YEAR(starts_at) as year'),
            DB::raw('MONTH(starts_at) as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'active' AND ends_at > NOW() THEN 1 ELSE 0 END) as active"),
            DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
            DB::raw("SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired"),
            DB::raw('COALESCE(SUM(amount), 0) as revenue')
        )
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy(DB::raw('YEAR(starts_at)'), DB::raw('MONTH(starts_at)'))
            ->orderByDesc(DB::raw('YEAR(starts_at)'))
            ->orderByDesc(DB::raw('MONTH(starts_at)'))
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total;
                $active = (int) $row->active;
                $retention = $total > 0 ? round($active / $total * 100) : 0;
                $monthName = now()->setYear($row->year)->setMonth($row->month)->translatedFormat('M Y');

                return [
                    'label' => $monthName,
                    'total' => $total,
                    'active' => $active,
                    'cancelled' => (int) $row->cancelled,
                    'expired' => (int) $row->expired,
                    'revenue' => (float) $row->revenue,
                    'retention' => $retention,
                ];
            });

        // Revenue by role
        $revenueByRole = collect();
        try {
            $revenueByRole = DB::table('payments')
                ->join('subscriptions', 'subscriptions.id', '=', 'payments.subscription_id')
                ->join('users', 'users.id', '=', 'subscriptions.user_id')
                ->where('payments.status', Payment::STATUS_COMPLETED)
                ->select(
                    'users.role',
                    DB::raw('SUM(payments.amount) as revenue'),
                    DB::raw('COUNT(DISTINCT users.id) as customers'),
                    DB::raw('COUNT(payments.id) as payments_count')
                )
                ->groupBy('users.role')
                ->orderByDesc('revenue')
                ->get();
        } catch (\Throwable) {
        }

        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'monthlyStats' => $monthlyStats,
            'maxRevenue' => $maxRevenue,
            'cohortData' => $cohortData,
            'revenueByRole' => $revenueByRole,
        ]);
    }
}
