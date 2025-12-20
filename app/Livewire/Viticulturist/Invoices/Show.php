<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Invoice $invoice;
    public $invoice_id;

    public function mount($invoice)
    {
        $this->invoice_id = $invoice;
        $this->loadInvoice();
    }

    public function loadInvoice()
    {
        $user = Auth::user();
        $this->invoice = Invoice::forUser($user->id)
            ->with(['client', 'clientAddress', 'items.tax', 'items.harvest'])
            ->findOrFail($this->invoice_id);
    }

    public function render()
    {
        return view('livewire.viticulturist.invoices.show')
            ->layout('layouts.app');
    }
}
