<?php

namespace App\Livewire\DenominacionOrigen\Inspection;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Control e Inspección',
            'icon'        => 'shield-check',
            'description' => 'Plan de control anual, inspecciones programadas, actas, incumplimientos, expedientes sancionadores, resoluciones y alertas.',
            'items'       => ['Plan de control anual', 'Inspecciones programadas', 'Actas de inspección', 'Incumplimientos detectados', 'Expedientes sancionadores', 'Resoluciones', 'Alertas de incumplimiento'],
        ])->layout('layouts.app');
    }
}
