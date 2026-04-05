<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class GrapePurchaseInvoiceIssuedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected Invoice $invoice
    ) {}

    public function notificationCategory(): string
    {
        return 'invoice';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $winery  = $invoice->user?->name ?? '—';

        return (new MailMessage)
            ->subject('Nueva liquidación de vendimia — ' . $invoice->invoice_number)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery . '** ha emitido una liquidación de vendimia a tu nombre.')
            ->line(new HtmlString(
                '<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;padding:16px;border-radius:8px;margin:16px 0;">
                    <p style="margin:0 0 8px 0;"><strong>Nº Liquidación:</strong> ' . e($invoice->invoice_number) . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Ref. Albarán:</strong> ' . e($invoice->delivery_note_code ?? '—') . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Fecha:</strong> ' . ($invoice->invoice_date?->format('d/m/Y') ?? '—') . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Base imponible:</strong> ' . number_format((float) $invoice->subtotal, 2) . ' €</p>
                    <p style="margin:0 0 8px 0;"><strong>Retención IRPF:</strong> -' . number_format((float) $invoice->tax_amount, 2) . ' €</p>
                    <p style="margin:0;font-size:18px;font-weight:bold;color:#166534;">A cobrar: ' . number_format((float) $invoice->total_amount, 2) . ' €</p>
                 </div>'
            ))
            ->action('Ver mis liquidaciones', AppLink::url(route('viticulturist.invoices.grape-purchase.index'), 'agro365://invoices'))
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $invoice = $this->invoice;
        $winery  = $invoice->user?->name ?? '—';

        return [
            'type'       => 'invoice_issued',
            'icon'       => 'banknotes',
            'color'      => 'green',
            'title'      => 'Nueva liquidación de vendimia',
            'body'       => "La bodega {$winery} ha emitido la liquidación {$invoice->invoice_number} por " . number_format((float) $invoice->total_amount, 2) . " €.",
            'link'       => route('viticulturist.invoices.grape-purchase.index'),
            'invoice_id' => $invoice->id,
        ];
    }
}
