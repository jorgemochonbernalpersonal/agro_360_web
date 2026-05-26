<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class TreatmentIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'phytosanitary'; }
    protected function pageTitle(): string        { return __('Tratamientos Fitosanitarios'); }
    protected function pageDescription(): string  { return __('Registro de tratamientos fitosanitarios aplicados en tus parcelas'); }
    protected function createRoute(): string      { return roleRoute('digital-notebook.treatment.create'); }
    protected function editRouteSuffix(): string  { return 'digital-notebook.treatment.edit'; }
    protected function typeIcon(): string         { return 'shield-exclamation'; }
    protected function typeBadgeColor(): string   { return 'red'; }
}
