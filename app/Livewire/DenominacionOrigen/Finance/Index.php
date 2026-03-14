<?php

namespace App\Livewire\DenominacionOrigen\Finance;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Negocio DO',
            'icon'        => 'banknotes',
            'description' => 'Cuotas de inscripción, tasas y liquidaciones, facturas DO, VeriFactu, presupuesto anual, estadísticas financieras y promoción.',
            'items'       => ['Cuotas de inscripción', 'Tasas y liquidaciones', 'Facturas DO', 'VeriFactu', 'Presupuesto anual', 'Estadísticas financieras', 'Promoción y marketing'],
        ])->layout('layouts.app');
    }
}
