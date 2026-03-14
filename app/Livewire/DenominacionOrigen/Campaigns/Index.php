<?php

namespace App\Livewire\DenominacionOrigen\Campaigns;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Campañas',
            'icon'        => 'flag',
            'description' => 'Gestión de campañas de vendimia, declaraciones de cosecha, aforos por bodega y viticultor, cupos y límites de producción.',
            'items'       => ['Campañas de vendimia', 'Declaraciones de cosecha', 'Aforos por bodega', 'Aforos por viticultor', 'Rendimientos por parcela', 'Cupos y límites de producción', 'Previsiones agregadas'],
        ])->layout('layouts.app');
    }
}
