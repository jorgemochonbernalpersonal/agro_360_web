<?php

namespace App\Livewire\Viticulturist\Invoices\GrapePurchase;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $wineryFilter  = '';
    public string $paymentFilter = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'wineryFilter'  => ['except' => ''],
        'paymentFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingWineryFilter(): void  { $this->resetPage(); }
    public function updatingPaymentFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = $this->wineryFilter = $this->paymentFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $viticulturistId = Auth::id();

        $query = Invoice::where('viticulturist_id', $viticulturistId)
            ->where('invoice_type', 'grape_purchase')
            ->with('user');

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                  ->orWhereHas('user', fn ($q2) =>
                      $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                  );
            });
        }

        if ($this->wineryFilter) {
            $query->where('user_id', $this->wineryFilter);
        }

        if ($this->paymentFilter) {
            $query->where('payment_status', $this->paymentFilter);
        }

        $query->orderByDesc('invoice_date')->orderByDesc('id');

        $invoices = $query->paginate(15);

        // Bodegas que me han emitido liquidaciones
        $wineries = User::whereIn('id',
            Invoice::where('viticulturist_id', $viticulturistId)
                ->where('invoice_type', 'grape_purchase')
                ->distinct()
                ->pluck('user_id')
        )->orderBy('name')->get(['id', 'name']);

        // Totales de resumen
        $baseQuery = Invoice::where('viticulturist_id', $viticulturistId)
            ->where('invoice_type', 'grape_purchase')
            ->where('status', '!=', 'cancelled');

        $stats = [
            'total_amount' => (clone $baseQuery)->sum('total_amount'),
            'paid_amount'  => (clone $baseQuery)->where('payment_status', 'paid')->sum('total_amount'),
            'pending_count' => (clone $baseQuery)->where('payment_status', 'unpaid')->count(),
        ];

        return view('livewire.viticulturist.invoices.grape-purchase.index', [
            'invoices' => $invoices,
            'wineries' => $wineries,
            'stats'    => $stats,
        ])->layout('layouts.app');
    }
}
