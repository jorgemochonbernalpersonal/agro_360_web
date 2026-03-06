<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Invoice;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications, WithInvoiceActions;

    public string $search              = '';
    public string $viticulturistFilter = '';
    public string $paymentFilter       = '';

    protected $queryString = [
        'search'              => ['except' => ''],
        'viticulturistFilter' => ['except' => ''],
        'paymentFilter'       => ['except' => ''],
    ];

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingViticulturistFilter(): void { $this->resetPage(); }
    public function updatingPaymentFilter(): void       { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search              = '';
        $this->viticulturistFilter = '';
        $this->paymentFilter       = '';
        $this->resetPage();
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Cancel a grape purchase invoice.
     * No stock movement needed — just marks invoice as cancelled
     * so harvests become available for re-invoicing.
     */
    public function cancel(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId);
        if (!$invoice) return;

        if ($invoice->status === 'cancelled') {
            $this->toastError('Esta liquidación ya está cancelada.');
            return;
        }

        if ($invoice->payment_status === 'paid') {
            $this->toastError('No se puede cancelar una liquidación ya pagada.');
            return;
        }

        $invoice->update(['status' => 'cancelled']);
        $this->toastSuccess('Liquidación cancelada. Las recepciones quedan disponibles para una nueva liquidación.');
    }

    protected function getEmailRecipient(Invoice $invoice): ?string
    {
        return $invoice->viticulturist?->email;
    }

    protected function markPaidSuccessMessage(): string
    {
        return 'Liquidación marcada como pagada.';
    }

    protected function sendEmailSuccessMessage(string $email): string
    {
        return "Liquidación enviada a {$email}.";
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $wineryId = Auth::id();

        $query = Invoice::where('user_id', $wineryId)
            ->where('invoice_type', 'grape_purchase')
            ->with('viticulturist')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                  ->orWhereHas('viticulturist', function ($q2) use ($term) {
                      $q2->whereRaw('LOWER(name) LIKE ?', [$term]);
                  });
            });
        }

        if ($this->viticulturistFilter) {
            $query->where('viticulturist_id', $this->viticulturistFilter);
        }

        if ($this->paymentFilter) {
            $query->where('payment_status', $this->paymentFilter);
        }

        $invoices = $query->paginate(15);

        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->where('source', 'own')
            ->pluck('viticulturist_id');

        $viticulturists = \App\Models\User::whereIn('id', $viticulturistIds)
            ->orderBy('name')->get(['id', 'name']);

        $statsBase = Invoice::where('user_id', $wineryId)->where('invoice_type', 'grape_purchase');
        $stats = [
            'total'   => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('payment_status', 'unpaid')->count(),
            'paid'    => (clone $statsBase)->where('payment_status', 'paid')->count(),
            'amount'  => (float) (clone $statsBase)->sum('total_amount'),
        ];

        return view('livewire.winery.billing.grape-purchase.index', [
            'invoices'       => $invoices,
            'viticulturists' => $viticulturists,
            'stats'          => $stats,
        ])->layout('layouts.app');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findInvoice(int $id): ?Invoice
    {
        return Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'grape_purchase')
            ->find($id);
    }
}
