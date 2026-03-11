<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class FertilizationIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'fertilization'; }
    protected function pageTitle(): string        { return 'Fertilizaciones'; }
    protected function pageDescription(): string  { return 'Registro de fertilizaciones aplicadas en tus parcelas'; }
    protected function createRoute(): string      { return route('viticulturist.digital-notebook.fertilization.create'); }
    protected function editRouteName(): string    { return 'viticulturist.digital-notebook.fertilization.edit'; }
    protected function typeIcon(): string         { return 'funnel'; }
    protected function typeBadgeColor(): string   { return 'blue'; }
}
