<?php

namespace App\Livewire\Producer\Invoices;

use App\Livewire\Concerns\WithInvoiceFormRules;
use App\Livewire\Concerns\WithProducerInvoiceItems;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InvoicingSetting;
use App\Services\ProducerInvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithInvoiceFormRules, WithProducerInvoiceItems, WithToastNotifications;

    public string $client_id = '';

    public string $client_address_id = '';

    public string $invoice_date = '';

    public string $delivery_note_date = '';

    public string $payment_type = '';

    public string $observations = '';

    public string $observations_invoice = '';

    public string $delivery_note_code = '';

    public string $delivery_note_code_auto = '';

    public bool $delivery_note_code_modified = false;

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->delivery_note_date = now()->toDateString();

        $this->loadTaxes();

        $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
        $this->delivery_note_code_auto = $settings->getDeliveryNotePreview();
        $this->delivery_note_code = $this->delivery_note_code_auto;

        $this->availableClients = Client::forUser(Auth::id())->active()->get();
        $this->loadHarvests();
        $this->loadLots();
    }

    public function updatedDeliveryNoteCode(string $value): void
    {
        $this->delivery_note_code_modified = ($value !== $this->delivery_note_code_auto);
    }

    public function save()
    {
        $this->validate();

        $taxRates = $this->availableTaxes->keyBy('id');
        $noteCode = null;

        try {
            $invoice = app(ProducerInvoiceService::class)->createInvoice(
                Auth::id(),
                [
                    'client_id' => $this->client_id,
                    'client_address_id' => $this->client_address_id ?: null,
                    'delivery_note_date' => $this->delivery_note_date ?: now(),
                    'order_date' => $this->invoice_date,
                    'invoice_date' => $this->invoice_date,
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                    'observations_invoice' => $this->observations_invoice ?: null,
                    'delivery_note_code_modified' => $this->delivery_note_code_modified,
                    'delivery_note_code_custom' => $this->delivery_note_code,
                ],
                $this->items,
                $taxRates,
            );

            $this->toastSuccess("Albarán {$invoice->delivery_note_code} creado. Emítelo para generar el número de factura.");

            return $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al crear factura de productor: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException
                ? $e->getMessage()
                : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $campaigns = Campaign::where('viticulturist_id', Auth::id())
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.producer.invoices.create', [
            'campaigns' => $campaigns,
        ])->layout('layouts.app', ['title' => __('Crear albarán - Agro365')]);
    }

    protected function rules(): array
    {
        return $this->invoiceCreateRules('harvest,wine,service,other');
    }

    protected function messages(): array
    {
        return [
            'client_address_id.required' => __('Debes seleccionar un cliente con dirección. Este cliente no tiene direcciones configuradas.'),
            'items.required' => __('Debes añadir al menos un ítem a la factura.'),
            'items.min' => __('Debes añadir al menos un ítem a la factura.'),
        ];
    }
}
