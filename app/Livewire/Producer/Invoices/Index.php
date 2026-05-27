<?php

namespace App\Livewire\Producer\Invoices;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search         = '';
    public string $statusFilter   = '';
    public string $deliveryFilter = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'statusFilter'   => ['except' => ''],
        'deliveryFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingStatusFilter(): void   { $this->resetPage(); }
    public function updatingDeliveryFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search         = '';
        $this->statusFilter   = '';
        $this->deliveryFilter = '';
        $this->resetPage();
    }

    #[Layout('layouts.app', [
        'title'       => 'Mis albaranes - Agro365',
        'description' => 'Gestiona tus albaranes y facturas mixtas.',
    ])]
    public function render()
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'producer_sale')
            ->with('client')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('delivery_note_code', 'like', '%' . $this->search . '%')
                   ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('client', fn ($sub) =>
                       $sub->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%')
                           ->orWhere('company_name', 'like', '%' . $this->search . '%')
                   )
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->deliveryFilter, fn ($q) => $q->where('delivery_status', $this->deliveryFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.producer.invoices.index', [
            'invoices' => $invoices,
        ]);
    }
}
