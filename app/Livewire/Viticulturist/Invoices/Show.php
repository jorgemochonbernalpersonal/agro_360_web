<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;

    public function mount($invoice)
    {
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($invoice instanceof Invoice) {
            $this->invoice = $invoice;
        } else {
            $user = Auth::user();
            $this->invoice = Invoice::forUser($user->id)
                ->with(['client', 'clientAddress', 'items.tax', 'items.harvest'])
                ->findOrFail($invoice);
        }
    }

    public function render()
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'N/A';
        $clientName = $this->invoice->client->full_name ?? 'Cliente';
        return view('livewire.viticulturist.invoices.show')
            ->layout('layouts.app', [
                'title' => 'Factura ' . $invoiceNumber . ' - ' . $clientName . ' - Agro365',
                'description' => 'Detalles de la factura ' . $invoiceNumber . ' para ' . $clientName . '. Items, importes, estado de pago y observaciones.',
            ]);
    }
}
