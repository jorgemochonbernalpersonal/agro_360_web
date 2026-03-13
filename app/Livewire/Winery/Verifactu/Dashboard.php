<?php

namespace App\Livewire\Winery\Verifactu;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Invoice;
use App\Models\SifRecord;
use App\Services\VerifactuService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination, WithToastNotifications;

    public string $tab = 'pending';

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    // ── Actions ────────────────────────────────────────────────────────────────

    public function sendInvoice(int $id): void
    {
        $invoice = $this->findInvoice($id);

        if (!$invoice) {
            $this->toastError('Factura no encontrada.');
            return;
        }

        $service = app(VerifactuService::class);
        $result  = $service->send($invoice);

        if ($result['success']) {
            $this->toastSuccess('Factura verificada y enviada correctamente. CSV: ' . $result['csv']);
        } else {
            $this->toastError('Error al verificar: ' . implode(' / ', $result['errors']));
        }
    }

    public function sendAll(): void
    {
        $service  = app(VerifactuService::class);
        $invoices = $this->pendingQuery()->get();
        $ok       = 0;
        $err      = 0;

        foreach ($invoices as $invoice) {
            $result = $service->send($invoice);
            $result['success'] ? $ok++ : $err++;
        }

        if ($err === 0) {
            $this->toastSuccess("Se han verificado {$ok} facturas correctamente.");
        } else {
            $this->toastError("Verificadas: {$ok}. Con errores: {$err}. Revisa la pestaña Errores.");
        }
    }

    public function retryInvoice(int $id): void
    {
        $invoice = $this->findInvoice($id);

        if (!$invoice) {
            $this->toastError('Factura no encontrada.');
            return;
        }

        $service = app(VerifactuService::class);
        $result  = $service->retry($invoice);

        if ($result['success']) {
            $this->toastSuccess('Factura reintentada correctamente. CSV: ' . $result['csv']);
        } else {
            $this->toastError('Error: ' . implode(' / ', $result['errors']));
        }
    }

    public function cancelInvoice(int $id): void
    {
        $invoice = $this->findInvoice($id);

        if (!$invoice) {
            $this->toastError('Factura no encontrada.');
            return;
        }

        $service = app(VerifactuService::class);
        $result  = $service->cancel($invoice);

        if ($result['success']) {
            $this->toastSuccess('Anulación registrada. La factura vuelve a estado pendiente.');
        } else {
            $this->toastError('Error al anular: ' . implode(' / ', $result['errors']));
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $userId = Auth::id();

        $stats = [
            'pending' => Invoice::forUser($userId)->whereNotNull('invoice_number')->where('sif_status', 'pendiente')->where('sif_excluded', false)->whereNotIn('status', ['draft'])->count(),
            'sent'    => Invoice::forUser($userId)->whereNotNull('invoice_number')->where('sif_status', 'aceptado')->count(),
            'errors'  => Invoice::forUser($userId)->whereNotNull('invoice_number')->where('sif_status', 'error')->count(),
        ];

        $pending = null;
        $sent    = null;
        $errors  = null;
        $config  = null;

        if ($this->tab === 'pending') {
            $pending = $this->pendingQuery()->with(['client'])->paginate(20);
        } elseif ($this->tab === 'sent') {
            $sent = Invoice::forUser($userId)
                ->whereNotNull('invoice_number')
                ->where('sif_status', 'aceptado')
                ->with(['client'])
                ->orderByDesc('sif_sent_at')
                ->paginate(20);
        } elseif ($this->tab === 'errors') {
            $errors = Invoice::forUser($userId)
                ->whereNotNull('invoice_number')
                ->where('sif_status', 'error')
                ->with(['client', 'sifRecords' => fn($q) => $q->where('status', 'ER')->latest()->limit(1)])
                ->orderByDesc('updated_at')
                ->paginate(20);
        } elseif ($this->tab === 'config') {
            $config = app(VerifactuService::class)->getConfig();
        }

        return view('livewire.winery.verifactu.dashboard', compact(
            'stats', 'pending', 'sent', 'errors', 'config'
        ))->layout('layouts.app');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function pendingQuery()
    {
        return Invoice::forUser(Auth::id())
            ->whereNotNull('invoice_number')
            ->where('sif_status', 'pendiente')
            ->where('sif_excluded', false)
            ->whereNotIn('status', ['draft'])
            ->orderByDesc('invoice_date');
    }

    public function excludeInvoice(int $id): void
    {
        $invoice = $this->findInvoice($id);

        if (!$invoice) {
            $this->toastError('Factura no encontrada.');
            return;
        }

        $invoice->update(['sif_excluded' => true]);
        $this->toastSuccess('Factura excluida de Verifactu.');
    }

    public function excludeAll(): void
    {
        $count = $this->pendingQuery()->update(['sif_excluded' => true]);
        $this->toastSuccess("{$count} facturas excluidas de Verifactu.");
    }

    private function findInvoice(int $id): ?Invoice
    {
        return Invoice::where('user_id', Auth::id())
            ->whereNotNull('invoice_number')
            ->find($id);
    }
}
