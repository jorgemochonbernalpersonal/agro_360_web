<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\ContainerStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    public function __construct(
        private ContainerStockService $stockService
    ) {}

    /**
     * Al crear una factura draft: reserva stock para todos sus items de cosecha.
     * (Los items creados después disparan InvoiceItemObserver::created, no este método.)
     * También captura el snapshot de facturación del cliente.
     */
    public function created(Invoice $invoice): void
    {
        try {
            $invoice->load('items.harvest');

            if ($invoice->status === 'draft') {
                DB::transaction(function () use ($invoice) {
                    foreach ($invoice->items as $item) {
                        if (! $item->harvest_id) {
                            continue;
                        }
                        $this->stockService->reserveStock($item->harvest, $item);
                    }
                });
            }

            if (! $invoice->order_date) {
                $invoice->updateQuietly(['order_date' => now()]);
            }

            $this->populateBillingSnapshot($invoice);
        } catch (\Exception $e) {
            Log::error('[InvoiceObserver] Error al crear factura', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Antes de guardar: valida transiciones de estado prohibidas.
     * Lanza excepción para impedir el guardado.
     */
    public function updating(Invoice $invoice): void
    {
        $oldStatus         = $invoice->getOriginal('status');
        $newStatus         = $invoice->status;
        $oldDeliveryStatus = $invoice->getOriginal('delivery_status');
        $newDeliveryStatus = $invoice->delivery_status;

        // Prohibir cancelar una factura ya enviada/emitida formalmente
        // Los borradores (draft) sí se pueden cancelar aunque tengan número asignado
        if ($newStatus === 'cancelled' && $oldStatus === 'sent') {
            throw new \Exception(
                'No se puede cancelar la factura ' . $invoice->invoice_number . '. Debe crear una factura rectificativa.'
            );
        }

        // Prohibir cancelar una entrega ya marcada como entregada
        if ($oldDeliveryStatus === 'delivered' && $newDeliveryStatus === 'cancelled') {
            throw new \Exception(
                'No se puede cancelar una entrega ya marcada como entregada. Cancele la factura completa si corresponde.'
            );
        }
    }

    /**
     * Al actualizar una factura: gestiona transiciones de estado y stock.
     */
    public function updated(Invoice $invoice): void
    {
        $oldStatus         = $invoice->getOriginal('status');
        $newStatus         = $invoice->status;
        $oldDeliveryStatus = $invoice->getOriginal('delivery_status');
        $newDeliveryStatus = $invoice->delivery_status;
        $oldPaymentStatus  = $invoice->getOriginal('payment_status');
        $newPaymentStatus  = $invoice->payment_status;

        try {
            // delivery_status tiene prioridad sobre status para el stock
            if ($oldDeliveryStatus !== $newDeliveryStatus) {
                $this->handleDeliveryStatusChange($invoice, $oldDeliveryStatus, $newDeliveryStatus);
            } elseif ($oldStatus !== $newStatus) {
                $this->handleStatusChange($invoice, $oldStatus, $newStatus);
            }

            if ($oldPaymentStatus !== $newPaymentStatus) {
                $this->handlePaymentStatusChange($invoice, $oldPaymentStatus, $newPaymentStatus);
            }
        } catch (\Exception $e) {
            Log::error('[InvoiceObserver] Error al actualizar factura', [
                'invoice_id'          => $invoice->id,
                'old_status'          => $oldStatus,
                'new_status'          => $newStatus,
                'old_delivery_status' => $oldDeliveryStatus,
                'new_delivery_status' => $newDeliveryStatus,
                'error'               => $e->getMessage(),
            ]);
        }
    }

    /**
     * Al eliminar una factura: libera todo el stock.
     */
    public function deleting(Invoice $invoice): void
    {
        try {
            $this->releaseAllStock($invoice, $invoice->status);
        } catch (\Exception $e) {
            Log::error('[InvoiceObserver] Error al eliminar factura', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Transiciones de estado
    // -------------------------------------------------------------------------

    protected function handleDeliveryStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        // Registrar fecha de albarán al entregar
        if ($newStatus === 'delivered' && ! $invoice->delivery_note_date) {
            $invoice->updateQuietly(['delivery_note_date' => now()]);
        }

        // El estado de entrega es puramente informativo: NO mueve stock.
        // Los movimientos de stock los gestiona exclusivamente invoice.status.
        // Los guards de transición prohibida están en updating().
    }

    protected function handleStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        $invoice->load('items.harvest');

        // draft → sent: confirmar ventas (el invoice_number ya se asigna al crear)
        // Fallback: si la factura no tiene número (facturas antiguas), generarlo aquí
        if ($oldStatus === 'draft' && $newStatus === 'sent') {
            if (! $invoice->invoice_number) {
                $settings      = \App\Models\InvoicingSetting::getOrCreateForUser($invoice->user_id);
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();
                $invoice->updateQuietly(['invoice_number' => $invoiceNumber]);
            }
            // Re-captura el snapshot con los datos del cliente en el momento de emisión.
            // Sobreescribe el snapshot inicial para reflejar cualquier cambio posterior al borrador.
            $this->populateBillingSnapshot($invoice);
            $this->convertReservationsToSales($invoice);
        }

        // sent → draft: revertir ventas a reservas
        elseif ($oldStatus === 'sent' && $newStatus === 'draft') {
            $this->convertSalesToReservations($invoice);
        }

        // Cualquier estado → cancelled: liberar stock
        // (el guard que impide cancelar si oldStatus=sent está en updating())
        elseif ($newStatus === 'cancelled') {
            $this->releaseAllStock($invoice, $oldStatus);
        }
    }

    protected function handlePaymentStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        if ($newStatus === 'paid' && ! $invoice->payment_date) {
            $invoice->updateQuietly(['payment_date' => now()]);
        } elseif ($oldStatus === 'paid' && $newStatus !== 'paid') {
            $invoice->updateQuietly(['payment_date' => null]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers de stock
    // -------------------------------------------------------------------------

    protected function convertReservationsToSales(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                if (! $item->harvest_id) {
                    continue;
                }
                $this->stockService->confirmSale(
                    $item->harvest,
                    $item,
                    $invoice->invoice_number ?? ''
                );
            }
        });

        Log::info('[InvoiceObserver] Reservas convertidas a ventas', [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    protected function convertSalesToReservations(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                if (! $item->harvest_id) {
                    continue;
                }
                $this->stockService->revertSaleToReservation($item->harvest, $item);
            }
        });

        Log::info('[InvoiceObserver] Ventas revertidas a reservas', [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    protected function releaseAllStock(Invoice $invoice, string $fromStatus): void
    {
        $invoice->loadMissing('items.harvest');

        DB::transaction(function () use ($invoice, $fromStatus) {
            foreach ($invoice->items as $item) {
                if (! $item->harvest_id) {
                    continue;
                }
                $this->stockService->releaseFromInvoice($item->harvest, $item, $fromStatus);
            }
        });

        Log::info('[InvoiceObserver] Todo el stock liberado', [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'from_status'    => $fromStatus,
        ]);
    }

    // -------------------------------------------------------------------------
    // Snapshot de facturación
    // -------------------------------------------------------------------------

    /**
     * Captura los datos del cliente en los campos billing_* de la factura.
     * Se llama en created() y en el momento de emitir (draft→sent) para
     * garantizar que la factura siempre refleja los datos vigentes del cliente
     * en el momento de su emisión formal.
     */
    private function populateBillingSnapshot(Invoice $invoice): void
    {
        // Forzar recarga para capturar cambios posteriores a la creación del borrador
        $invoice->load('client');

        $client = $invoice->client;

        if (! $client) {
            return;
        }

        // Determinar la dirección a usar: la de la factura, la por defecto, o ninguna
        $address = null;

        if ($invoice->client_address_id) {
            $address = \App\Models\ClientAddress::with(['municipality', 'province'])
                ->find($invoice->client_address_id);
        }

        if (! $address) {
            $address = $client->addresses()
                ->with(['municipality', 'province'])
                ->where('is_default', true)
                ->first();
        }

        $invoice->updateQuietly([
            'billing_company_name'     => $client->company_name,
            'billing_company_document' => $client->company_document ?: $client->particular_document,
            'billing_first_name'       => ($address?->first_name) ?: $client->first_name,
            'billing_last_name'        => ($address?->last_name) ?: $client->last_name,
            'billing_email'            => ($address?->email) ?: $client->email,
            'billing_phone'            => ($address?->phone) ?: $client->phone,
            'billing_address'          => $address?->address,
            'billing_postal_code'      => $address?->postal_code,
            'billing_city'             => $address?->municipality?->name,
            'billing_state'            => $address?->province?->name,
            'billing_country'          => 'España',
        ]);
    }
}
