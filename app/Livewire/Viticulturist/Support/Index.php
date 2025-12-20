<?php

namespace App\Livewire\Viticulturist\Support;

use App\Models\SupportTicket;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $filterStatus = 'all';
    public $filterType = 'all';
    public $selectedTicket = null;
    public $newComment = '';

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
    }

    public function closeTicketDetail()
    {
        $this->selectedTicket = null;
        $this->newComment = '';
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
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
            'is_internal' => false,
        ]);

        $this->newComment = '';
        $this->selectedTicket = $this->selectedTicket->fresh(['comments.user']);
        $this->toastSuccess('Comentario añadido.');
    }

    public function closeTicket($ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        if ($ticket->user_id !== Auth::id()) {
            $this->toastError('No tienes permiso para cerrar este ticket.');
            return;
        }

        $ticket->close();
        $this->toastSuccess('Ticket cerrado.');
        $this->selectedTicket = null;
    }

    public function reopenTicket($ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        if ($ticket->user_id !== Auth::id()) {
            $this->toastError('No tienes permiso para reabrir este ticket.');
            return;
        }

        $ticket->reopen();
        $this->toastSuccess('Ticket reabierto.');
        $this->selectTicket($ticketId);
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = SupportTicket::with(['user', 'assignedTo'])
            ->forUser($user->id)
            ->latest();

        // Filtrar por búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
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

        $tickets = $query->paginate(10);

        // Stats
        $stats = [
            'total' => SupportTicket::forUser($user->id)->count(),
            'open' => SupportTicket::forUser($user->id)->open()->count(),
            'in_progress' => SupportTicket::forUser($user->id)->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::forUser($user->id)->where('status', 'resolved')->count(),
        ];

        return view('livewire.viticulturist.support.index', [
            'tickets' => $tickets,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
