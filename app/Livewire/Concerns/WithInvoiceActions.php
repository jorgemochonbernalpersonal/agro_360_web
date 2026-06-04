<?php

namespace App\Livewire\Concerns;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Acciones comunes de facturación reutilizables entre Index components.
 *
 * Requiere:
 *   - WithToastNotifications (toastSuccess / toastError)
 *   - private/protected findInvoice(int $id, array $with = []): ?Invoice
 *
 * Personalizable sobreescribiendo:
 *   - getEmailRecipient(Invoice $invoice): ?string
 *   - markPaidSuccessMessage(): string
 *   - sendEmailSuccessMessage(string $email): string
 */
trait WithInvoiceActions
{
    // ── Marcar pagada ─────────────────────────────────────────────────────────

    public function markPaid(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('No se puede marcar como pagada una factura cancelada.'));

            return;
        }

        if ($invoice->payment_status === 'paid') {
            $this->toastError(__('Esta factura ya está marcada como pagada.'));

            return;
        }

        // InvoiceObserver detecta el cambio y establece payment_date automáticamente
        $invoice->update(['payment_status' => 'paid']);
        $this->toastSuccess($this->markPaidSuccessMessage());
    }

    // ── Enviar por email ──────────────────────────────────────────────────────

    public function sendEmail(int $id): void
    {
        $invoice = $this->findInvoice($id, [
            'client', 'viticulturist', 'items', 'user.profile.province',
        ]);
        if (! $invoice) {
            return;
        }

        $email = $this->getEmailRecipient($invoice);

        if (! $email) {
            $this->toastError(__('No hay email registrado para el destinatario.'));

            return;
        }

        try {
            $senderName = Auth::user()->name ?? '';

            Mail::to($email)->send(new InvoiceMail($invoice, $senderName));

            $invoice->update(['sent' => true]);

            $this->toastSuccess($this->sendEmailSuccessMessage($email));

        } catch (\Exception $e) {
            Log::error('Error al enviar factura por email: '.$e->getMessage(), [
                'invoice_id' => $id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError(__('Error al enviar el email. Inténtalo de nuevo.'));
        }
    }

    protected function markPaidSuccessMessage(): string
    {
        return __('Factura marcada como pagada.');
    }

    /**
     * Destinatario del email.
     * Por defecto: email de facturación o email del cliente.
     * GrapePurchase sobreescribe para usar viticulturist->email.
     */
    protected function getEmailRecipient(Invoice $invoice): ?string
    {
        return $invoice->billing_email ?: $invoice->client?->email;
    }

    protected function sendEmailSuccessMessage(string $email): string
    {
        return "Documento enviado a {$email}.";
    }
}
