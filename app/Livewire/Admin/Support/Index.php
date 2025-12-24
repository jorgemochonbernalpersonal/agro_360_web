<?php

namespace App\Livewire\Admin\Support;

use App\Models\SupportTicket;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $filterStatus = 'all';
    public $filterType = 'all';
    public $selectedTicket = null;
    public $newComment = '';
    public $assignTo = '';

    protected $queryString = ['search', 'filterStatus', 'filterType'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectTicket($ticketId)
    {
        $this->selectedTicket = SupportTicket::with(['user', 'comments.user', 'assignedTo'])
            ->findOrFail($ticketId);
        $this->newComment = '';
        $this->assignTo = $this->selectedTicket->assigned_to ?? '';
    }

    public function closeTicketDetail()
    {
        $this->selectedTicket = null;
        $this->newComment = '';
        $this->assignTo = '';
    }

    public function assignTicket()
    {
        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $this->selectedTicket->update([
            'assigned_to' => $this->assignTo ?: null,
        ]);

        $this->selectedTicket = $this->selectedTicket->fresh(['assignedTo']);
        $this->toastSuccess('Ticket asignado correctamente.');
    }

    public function changeStatus($status)
    {
        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $this->selectedTicket->update(['status' => $status]);
        
        if ($status === 'resolved') {
            $this->selectedTicket->resolve();
        } elseif ($status === 'closed') {
            $this->selectedTicket->close();
        }

        $this->selectedTicket = $this->selectedTicket->fresh();
        $this->toastSuccess('Estado del ticket actualizado.');
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:3',
        ]);

        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $this->selectedTicket->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $this->newComment,
            'is_internal' => false,
        ]);

        $this->newComment = '';
        $this->selectedTicket = $this->selectedTicket->fresh(['comments.user']);
        $this->toastSuccess('Comentario añadido.');
    }

    public function render()
    {
        $query = SupportTicket::with(['user', 'assignedTo'])
            ->latest();

        // Filtrar por búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtrar por estado
        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'open') {
                $query->open();
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        // Filtrar por tipo
        if ($this->filterType !== 'all') {
            $query->ofType($this->filterType);
        }

        $tickets = $query->paginate(20);

        // Stats globales
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::open()->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('livewire.admin.support.index', [
            'tickets' => $tickets,
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Tickets de Soporte - Admin - Agro365',
            'description' => 'Gestiona todos los tickets de soporte del sistema',
        ]);
    }
}

