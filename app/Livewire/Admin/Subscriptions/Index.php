<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Payment;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search       = '';
    public string $filterStatus = 'all';
    public string $filterPlan   = 'all';

    protected $queryString = ['search', 'filterStatus', 'filterPlan'];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterPlan(): void   { $this->resetPage(); }

    public function cancelSubscription(int $id): void
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->status === Subscription::STATUS_CANCELLED) {
            $this->toastError('La suscripción ya está cancelada.');
            return;
        }

        $subscription->cancel();
        $this->toastSuccess('Suscripción cancelada correctamente.');
    }

    public function exportCsv(): mixed
    {
        $subscriptions = Subscription::with('user')
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPlan !== 'all', fn($q) => $q->where('plan_type', $this->filterPlan))
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($subscriptions) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Usuario', 'Email', 'Plan', 'Estado', 'Importe (€)', 'Inicio', 'Fin', 'Cancelado', 'PayPal ID']);
            foreach ($subscriptions as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->user?->name ?? '',
                    $s->user?->email ?? '',
                    $s->plan_type === 'monthly' ? 'Mensual' : 'Anual',
                    $s->status,
                    number_format($s->amount, 2),
                    $s->starts_at?->format('d/m/Y') ?? '',
                    $s->ends_at?->format('d/m/Y') ?? '',
                    $s->cancelled_at?->format('d/m/Y') ?? '',
                    $s->paypal_subscription_id ?? '',
                ]);
            }
            fclose($handle);
        }, 'suscripciones-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Subscription::with('user:id,name,email,role')
            ->when($this->search, function ($q) {
                $term = '%' . mb_strtolower($this->search) . '%';
                $q->whereHas('user', fn($uq) => $uq
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                );
            })
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPlan !== 'all', fn($q) => $q->where('plan_type', $this->filterPlan))
            ->orderByDesc('created_at');

        $subscriptions = $query->paginate(20);

        $stats = [
            'total'             => Subscription::count(),
            'active'            => Subscription::where('status', Subscription::STATUS_ACTIVE)->count(),
            'cancelled'         => Subscription::where('status', Subscription::STATUS_CANCELLED)->count(),
            'expired'           => Subscription::where('status', Subscription::STATUS_EXPIRED)->count(),
            'revenue_total'     => (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'revenue_this_year' => (float) Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'stats'         => $stats,
        ]);
    }
}
