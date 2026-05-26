<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Winery\AbstractIndex;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    use WithInvoiceActions;

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

    protected function filterDefaults(): array
    {
        return ['search' => '', 'viticulturistFilter' => '', 'paymentFilter' => ''];
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function cancel(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId);
        if (!$invoice) return;

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('Esta liquidación ya está cancelada.'));
            return;
        }

        if ($invoice->payment_status === 'paid') {
            $this->toastError(__('No se puede cancelar una liquidación ya pagada.'));
            return;
        }

        // Cancelar status + delivery_status → el observer libera stock de cosecha
        $invoice->update([
            'status'          => 'cancelled',
            'delivery_status' => 'cancelled',
        ]);
        $this->toastSuccess(__('Liquidación cancelada. Las recepciones quedan disponibles para una nueva liquidación.'));
    }

    protected function getEmailRecipient(Invoice $invoice): ?string
    {
        return $invoice->viticulturist?->email;
    }

    protected function markPaidSuccessMessage(): string
    {
        return __('Liquidación marcada como pagada.');
    }

    protected function sendEmailSuccessMessage(string $email): string
    {
        return "Liquidación enviada a {$email}.";
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    protected function baseQuery(): Builder
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'grape_purchase')
            ->with('viticulturist');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                  ->orWhereHas('viticulturist', fn($q2) =>
                      $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                  );
            });
        }

        if ($this->viticulturistFilter) {
            $query->where('viticulturist_id', $this->viticulturistFilter);
        }

        if ($this->paymentFilter) {
            $query->where('payment_status', $this->paymentFilter);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('invoice_date')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['invoice_date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $viticulturistIds = WineryViticulturist::where('winery_id', $this->wineryId())
            ->where('source', 'own')
            ->pluck('viticulturist_id');

        if (auth()->user()->isProducer()) {
            $viticulturistIds = $viticulturistIds->push($this->wineryId())->unique();
        }

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')->get(['id', 'name']);

        $base = Invoice::where('user_id', $this->wineryId())->where('invoice_type', 'grape_purchase');

        $stats = [
            'total'        => (clone $base)->count(),
            'paid'         => (clone $base)->where('payment_status', 'paid')->count(),
            'pending'      => (clone $base)->where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->count(),
            'pending_amount' => (clone $base)->where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->sum('total_amount'),
        ];

        return [
            'invoices'       => $entries,
            'viticulturists' => $viticulturists,
            'stats'          => $stats,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'grape_purchase')
            ->with($with)
            ->find($id);
    }
}
