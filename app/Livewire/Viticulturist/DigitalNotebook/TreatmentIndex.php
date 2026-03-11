<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class TreatmentIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'phytosanitary'; }
    protected function pageTitle(): string        { return 'Tratamientos Fitosanitarios'; }
    protected function pageDescription(): string  { return 'Registro de tratamientos fitosanitarios aplicados en tus parcelas'; }
    protected function createRoute(): string      { return route('viticulturist.digital-notebook.treatment.create'); }
    protected function editRouteName(): string    { return 'viticulturist.digital-notebook.treatment.edit'; }
    protected function typeIcon(): string         { return 'shield-exclamation'; }
    protected function typeBadgeColor(): string   { return 'red'; }
}
