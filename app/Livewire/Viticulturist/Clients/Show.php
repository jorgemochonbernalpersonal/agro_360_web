<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Client $client;
    public $client_id;

    public function mount($client)
    {
        $this->client_id = $client;
        $this->loadClient();
    }

    public function loadClient()
    {
        $user = Auth::user();
        $this->client = Client::forUser($user->id)
            ->with(['addresses', 'invoices'])
            ->findOrFail($this->client_id);
    }

    public function render()
    {
        return view('livewire.viticulturist.clients.show')
            ->layout('layouts.app');
    }
}
