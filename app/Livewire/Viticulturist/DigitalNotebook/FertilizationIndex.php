<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class FertilizationIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'fertilization'; }
    protected function pageTitle(): string        { return __('Fertilizaciones'); }
    protected function pageDescription(): string  { return __('Registro de fertilizaciones aplicadas en tus parcelas'); }
    protected function createRoute(): string      { return roleRoute('digital-notebook.fertilization.create'); }
    protected function editRouteSuffix(): string  { return 'digital-notebook.fertilization.edit'; }
    protected function typeIcon(): string         { return 'funnel'; }
    protected function typeBadgeColor(): string   { return 'blue'; }
}
