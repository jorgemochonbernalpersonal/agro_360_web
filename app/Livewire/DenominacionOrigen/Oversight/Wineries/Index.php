<?php

namespace App\Livewire\DenominacionOrigen\Oversight\Wineries;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Supervisión — Bodegas',
            'icon'        => 'building-office-2',
            'description' => 'Panel de supervisión de todas las bodegas adscritas: vendimias, elaboración, trazabilidad, embotellado, recepciones de uva y análisis de calidad.',
            'items'       => ['Panel de bodegas', 'Vendimias por bodega', 'Elaboración y vinos', 'Trazabilidad por bodega', 'Embotellado y expediciones', 'Recepciones de uva', 'Análisis de calidad', 'Viticultores por bodega'],
        ])->layout('layouts.app');
    }
}
