<?php

namespace App\Livewire\DenominacionOrigen\Statistics;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Estadísticas',
            'icon'        => 'chart-bar',
            'description' => 'Dashboard general, producción por campaña, superficie y plantaciones, comercialización interior, exportación, rendimientos medios, evolución histórica e informes para CCAA.',
            'items'       => ['Dashboard general', 'Producción por campaña', 'Superficie y plantaciones', 'Comercialización interior', 'Exportación por mercado', 'Rendimientos medios', 'Evolución histórica', 'Informes para CCAA', 'Memoria anual'],
        ])->layout('layouts.app');
    }
}
