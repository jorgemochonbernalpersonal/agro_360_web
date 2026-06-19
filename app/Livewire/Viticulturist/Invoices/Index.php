<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithCorrectiveInvoice;
use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Invoice;
use App\Models\InvoicingSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithCorrectiveInvoice, WithInvoiceActions, WithPagination, WithToastNotifications;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterPaymentStatus = '';

    public bool $emitirModal = false;

    public ?int $emitirInvoiceId = null;

    public string $emitirDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterPaymentStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterPaymentStatus = '';
        $this->resetPage();
    }

    public function openEmitirModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede emitir una factura en estado borrador.'));

            return;
        }

        $this->emitirInvoiceId = $id;
        $this->emitirDate = now()->toDateString();
        $this->emitirModal = true;
    }

    public function closeEmitirModal(): void
    {
        $this->emitirModal = false;
        $this->emitirInvoiceId = null;
        $this->emitirDate = '';
        $this->resetValidation();
    }

    public function confirmEmitir(): void
    {
        $this->validate(
            ['emitirDate' => 'required|date'],
            ['emitirDate.required' => __('La fecha de factura es obligatoria.')]
        );

        $invoice = $this->findInvoice($this->emitirInvoiceId);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'draft') {
            $this->toastError(__('Esta factura ya no está en borrador.'));
            $this->closeEmitirModal();

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                // InvoiceObserver.updated detecta draft→sent y llama confirmSale() + billing snapshot
                $invoice->update([
                    'status' => 'sent',
                    'invoice_date' => $this->emitirDate,
                    'invoice_number' => $invoiceNumber,
                ]);
            });

            $this->closeEmitirModal();
            $this->toastSuccess(__('Factura emitida correctamente.'));

        } catch (\Exception $e) {
            Log::error('Error al emitir factura: '.$e->getMessage(), ['invoice_id' => $this->emitirInvoiceId]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al emitir la factura.'));
        }
    }

    public function delete(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'draft') {
            $this->toastError(__('Solo se pueden eliminar facturas en estado borrador.'));

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Release any reserved stock before deleting (same path as cancel)
                $invoice->update(['status' => 'cancelled']);
                $invoice->updateQuietly(['delivery_status' => 'cancelled']);
                $invoice->delete();
            });
            $this->toastSuccess(__('Factura eliminada correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error al eliminar factura: '.$e->getMessage(), ['invoice_id' => $id]);
            $this->toastError(__('Error al eliminar la factura.'));
        }
    }

    public function cancel(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'draft') {
            $this->toastError(__('Las facturas emitidas no se pueden cancelar directamente. Crea una factura rectificativa.'));

            return;
        }

        try {
            // Only update status so InvoiceObserver.updated fires handleStatusChange
            // → releaseAllStock(). Updating both status+delivery_status simultaneously
            // would hit the delivery_status branch first (elseif) and skip stock release.
            $invoice->update(['status' => 'cancelled']);
            $invoice->updateQuietly(['delivery_status' => 'cancelled']);
            $this->toastSuccess(__('Factura cancelada y stock liberado.'));

        } catch (\Exception $e) {
            Log::error('Error al cancelar factura: '.$e->getMessage(), ['invoice_id' => $id]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al cancelar la factura.'));
        }
    }

    #[Layout('layouts.app', [
        'title' => 'Facturas / Pedidos - Agro365',
        'description' => 'Gestiona tus facturas y pedidos.',
    ])]
    public function render()
    {
        $user = Auth::user();

        $query = Invoice::forUser($user->id)
            ->with(['client', 'items'])
            ->withCount('correctives')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus) {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhere('delivery_note_code', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', function ($subQ) {
                        $subQ->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $invoices = $query->paginate(12);

        $base = Invoice::forUser($user->id);
        $stats = [
            'total' => (clone $base)->count(),
            'issued' => (clone $base)->where('status', 'issued')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'pending_amount' => (clone $base)->where('payment_status', 'unpaid')->where('status', 'issued')->sum('total_amount'),
        ];

        return view('livewire.viticulturist.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::forUser(Auth::id())
            ->with($with)
            ->find($id);
    }
}
