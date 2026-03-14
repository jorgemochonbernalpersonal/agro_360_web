<?php

namespace App\Livewire\DenominacionOrigen\Qualification;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Calificación',
            'icon'        => 'star',
            'description' => 'Panel de cata, análisis de laboratorio, calificación y descalificación de vinos, añadas, fichas técnicas DO e histórico de calificaciones.',
            'items'       => ['Panel de cata', 'Análisis de laboratorio', 'Calificación de vinos', 'Descalificación / retirada', 'Añadas', 'Fichas técnicas DO', 'Histórico de calificaciones'],
        ])->layout('layouts.app');
    }
}
