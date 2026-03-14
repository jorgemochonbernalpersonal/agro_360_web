<?php

namespace App\Livewire\DenominacionOrigen\Settings;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Sistema',
            'icon'        => 'cog-6-tooth',
            'description' => 'Usuarios y permisos DO, documentos internos, comunicaciones masivas a bodegas y viticultores, y configuración general.',
            'items'       => ['Usuarios y permisos DO', 'Documentos DO', 'Comunicaciones masivas', 'Configuración'],
        ])->layout('layouts.app');
    }
}
