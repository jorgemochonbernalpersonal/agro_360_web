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
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($client instanceof Client) {
            $this->client = $client;
            $this->client_id = $client->id;
        } else {
            $this->client_id = $client;
        }
        
        $this->loadClient();
    }

    public function loadClient()
    {
        $user = Auth::user();
        
        // Si ya tenemos el cliente cargado, solo cargar relaciones
        if (!isset($this->client) || $this->client->id != $this->client_id) {
            $this->client = Client::forUser($user->id)
                ->with(['addresses', 'invoices'])
                ->findOrFail($this->client_id);
        } else {
            // Asegurar que el cliente pertenece al usuario actual
            if ($this->client->user_id !== $user->id) {
                abort(403, 'No tienes permiso para ver este cliente.');
            }
            // Cargar relaciones si no están cargadas
            if (!$this->client->relationLoaded('addresses')) {
                $this->client->load(['addresses', 'invoices']);
            }
        }
    }

    public function render()
    {
        $clientName = $this->client->full_name;
        return view('livewire.viticulturist.clients.show')
            ->layout('layouts.app', [
                'title' => $clientName . ' - Cliente - Agro365',
                'description' => 'Detalles del cliente ' . $clientName . '. Información de contacto, direcciones, facturas y estadísticas de facturación.',
            ]);
    }
}
