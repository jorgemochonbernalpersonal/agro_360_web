<?php

namespace App\Livewire\Admin\Support;

use App\Models\CannedResponse;
use App\Models\SupportTicket;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search          = '';
    public $filterStatus    = 'all';
    public $filterType      = 'all';
    public $filterPriority  = 'all';
    public $selectedTicket    = null;
    public $newComment        = '';
    public bool $isInternal   = false;   // nota interna del ticket abierto
    public bool $showInternal = false;   // toggle para usuarios demo/test
    public $assignTo          = '';

    protected $queryString = [
        'search', 'filterStatus', 'filterType', 'filterPriority',
        'showInternal' => ['except' => false, 'as' => 'internal'],
    ];

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingFilterStatus()   { $this->resetPage(); }
    public function updatingFilterType()     { $this->resetPage(); }
    public function updatingFilterPriority() { $this->resetPage(); }
    public function updatingShowInternal()   { $this->resetPage(); }

    public function toggleInternal(): void
    {
        $this->showInternal = !$this->showInternal;
        $this->resetPage();
    }

    public function selectTicket($ticketId)
    {
        $this->selectedTicket = SupportTicket::with(['user', 'comments.user', 'assignedTo'])
            ->findOrFail($ticketId);
        $this->newComment  = '';
        $this->isInternal  = false;
        $this->assignTo    = $this->selectedTicket->assigned_to ?? '';
    }

    public function closeTicketDetail()
    {
        $this->selectedTicket = null;
        $this->newComment     = '';
        $this->isInternal     = false;
        $this->assignTo       = '';
    }

    public function assignTicket()
    {
        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $previousAssignee = $this->selectedTicket->assigned_to;
        $this->selectedTicket->update(['assigned_to' => $this->assignTo ?: null]);
        $this->selectedTicket = $this->selectedTicket->fresh(['assignedTo']);

        SecurityLogger::logSecurityEvent('support_ticket_assigned', [
            'admin_id'      => Auth::id(),
            'ticket_id'     => $this->selectedTicket->id,
            'ticket_title'  => $this->selectedTicket->title,
            'from_user_id'  => $previousAssignee,
            'to_user_id'    => $this->assignTo ?: null,
        ]);

        $this->toastSuccess('Ticket asignado correctamente.');
    }

    public function changeStatus($status)
    {
        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $previousStatus = $this->selectedTicket->status;
        $this->selectedTicket->update(['status' => $status]);

        if ($status === 'resolved') {
            $this->selectedTicket->resolve();
        } elseif ($status === 'closed') {
            $this->selectedTicket->close();
        }

        $this->selectedTicket = $this->selectedTicket->fresh();

        SecurityLogger::logSecurityEvent('support_ticket_status_changed', [
            'admin_id'     => Auth::id(),
            'ticket_id'    => $this->selectedTicket->id,
            'ticket_title' => $this->selectedTicket->title,
            'from_status'  => $previousStatus,
            'to_status'    => $status,
        ]);

        $this->toastSuccess('Estado del ticket actualizado.');
    }

    public function deleteTicket($ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        $title  = $ticket->title;

        if ($this->selectedTicket && $this->selectedTicket->id === $ticketId) {
            $this->closeTicketDetail();
        }

        SecurityLogger::logSecurityEvent('support_ticket_deleted', [
            'admin_id'     => Auth::id(),
            'ticket_id'    => $ticketId,
            'ticket_title' => $title,
            'user_id'      => $ticket->user_id,
        ]);

        $ticket->delete();
        $this->toastSuccess("Ticket \"{$title}\" eliminado.");
    }

    public function selectCannedResponse(int $id): void
    {
        $response = CannedResponse::find($id);
        if ($response) {
            $this->newComment = $response->body;
        }
    }

    public function addComment()
    {
        $this->validate(['newComment' => 'required|string|min:3']);

        if (!$this->selectedTicket) {
            $this->toastError('No hay ticket seleccionado.');
            return;
        }

        $this->selectedTicket->comments()->create([
            'user_id'     => auth()->id(),
            'comment'     => $this->newComment,
            'is_internal' => $this->isInternal,
        ]);

        $wasInternal          = $this->isInternal;
        $this->newComment     = '';
        $this->isInternal     = false;
        $this->selectedTicket = $this->selectedTicket->fresh(['comments.user']);
        $this->toastSuccess($wasInternal ? 'Nota interna añadida.' : 'Comentario añadido.');
    }

    public function exportCsv()
    {
        $tickets  = SupportTicket::with('user')->latest()->get();
        $filename = 'tickets_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Título', 'Usuario', 'Email', 'Estado', 'Prioridad', 'Tipo', 'Creado']);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->id,
                    $ticket->title,
                    $ticket->user->name ?? '',
                    $ticket->user->email ?? '',
                    $ticket->status,
                    $ticket->priority,
                    $ticket->type,
                    $ticket->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        $query = SupportTicket::with(['user', 'assignedTo'])->latest();

        if (!$this->showInternal) {
            $query->whereHas('user', fn($q) => $q->excludeDemo());
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'open') {
                $query->open();
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        if ($this->filterType !== 'all') {
            $query->ofType($this->filterType);
        }

        if ($this->filterPriority !== 'all') {
            $query->where('priority', $this->filterPriority);
        }

        $tickets = $query->paginate(20);

        // Stats siempre sin usuarios internos
        $realBase = SupportTicket::whereHas('user', fn($q) => $q->excludeDemo());
        $stats = [
            'total'       => $realBase->count(),
            'open'        => (clone $realBase)->open()->count(),
            'in_progress' => (clone $realBase)->where('status', 'in_progress')->count(),
            'resolved'    => (clone $realBase)->where('status', 'resolved')->count(),
            'closed'      => (clone $realBase)->where('status', 'closed')->count(),
        ];

        $cannedResponses = CannedResponse::orderBy('sort_order')->orderBy('title')->get(['id', 'title', 'category']);

        return view('livewire.admin.support.index', [
            'tickets'         => $tickets,
            'stats'           => $stats,
            'cannedResponses' => $cannedResponses,
        ])->layout('layouts.app', [
            'title'       => 'Tickets de Soporte - Admin - Agro365',
            'description' => 'Gestiona todos los tickets de soporte del sistema',
        ]);
    }
}
