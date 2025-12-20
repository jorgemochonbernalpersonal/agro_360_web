<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
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
        return view('livewire.viticulturist.invoices.show')
            ->layout('layouts.app');
    }
}
