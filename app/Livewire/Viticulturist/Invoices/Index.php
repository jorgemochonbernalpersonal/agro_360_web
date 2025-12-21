<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterPaymentStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterPaymentStatus' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Invoice::forUser($user->id)
            ->with(['client', 'items']);

        // Filtros
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus) {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('delivery_note_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function($subQ) {
                      $subQ->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%')
                           ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => Invoice::forUser($user->id)->count(),
            'draft' => Invoice::forUser($user->id)->where('status', 'draft')->count(),
            'sent' => Invoice::forUser($user->id)->where('status', 'sent')->count(),
            'paid' => Invoice::forUser($user->id)->paid()->count(),
            'unpaid' => Invoice::forUser($user->id)->unpaid()->count(),
            'overdue' => Invoice::forUser($user->id)->overdue()->count(),
        ];

        return view('livewire.viticulturist.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Facturas / Pedidos - Agro365',
            'description' => 'Gestiona tus facturas y pedidos. Facturación integrada desde la vendimia hasta el pago.',
        ]);
    }
}
