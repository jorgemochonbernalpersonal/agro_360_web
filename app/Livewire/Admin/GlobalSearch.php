<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Plot;
use App\Models\SupportTicket;
use App\Models\User;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query  = '';
    public bool   $open   = false;
    public array  $results = [];

    protected $listeners = ['openGlobalSearch' => 'openModal'];

    public function openModal(): void
    {
        $this->open  = true;
        $this->query = '';
        $this->results = [];
    }

    public function closeModal(): void
    {
        $this->open = false;
    }

    public function updatedQuery(): void
    {
        if (strlen(trim($this->query)) < 2) {
            $this->results = [];
            return;
        }

        $term = '%' . mb_strtolower(trim($this->query)) . '%';
        $results = [];

        // Users
        $users = User::whereRaw('LOWER(name) LIKE ?', [$term])
            ->orWhereRaw('LOWER(email) LIKE ?', [$term])
            ->limit(5)
            ->get(['id', 'name', 'email', 'role']);

        foreach ($users as $u) {
            $results[] = [
                'type'     => 'usuario',
                'icon'     => 'user',
                'color'    => 'purple',
                'title'    => $u->name,
                'subtitle' => $u->email . ' · ' . $u->role,
                'url'      => route('admin.users.show', $u->id),
            ];
        }

        // Support tickets
        $tickets = SupportTicket::whereRaw('LOWER(subject) LIKE ?', [$term])
            ->orWhereRaw('LOWER(description) LIKE ?', [$term])
            ->limit(4)
            ->get(['id', 'subject', 'status']);

        foreach ($tickets as $t) {
            $results[] = [
                'type'     => 'ticket',
                'icon'     => 'chat-bubble-left-ellipsis',
                'color'    => 'red',
                'title'    => $t->subject,
                'subtitle' => __('Ticket #') . $t->id . ' · ' . $t->status,
                'url'      => route('admin.support.index'),
            ];
        }

        // Plots
        $plots = Plot::whereRaw('LOWER(name) LIKE ?', [$term])
            ->orWhereRaw('LOWER(municipality) LIKE ?', [$term])
            ->limit(4)
            ->get(['id', 'name', 'municipality', 'province']);

        foreach ($plots as $p) {
            $results[] = [
                'type'     => 'parcela',
                'icon'     => 'map',
                'color'    => 'agro',
                'title'    => $p->name,
                'subtitle' => trim(($p->municipality ?? '') . ', ' . ($p->province ?? ''), ', '),
                'url'      => route('admin.plots.index'),
            ];
        }

        $this->results = $results;
    }

    public function render()
    {
        return view('livewire.admin.global-search');
    }
}
