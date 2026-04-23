<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class IrrigationIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'irrigation'; }
    protected function pageTitle(): string        { return 'Riegos'; }
    protected function pageDescription(): string  { return 'Registro de riegos aplicados en tus parcelas'; }
    protected function createRoute(): string      { return roleRoute('digital-notebook.irrigation.create'); }
    protected function editRouteSuffix(): string  { return 'digital-notebook.irrigation.edit'; }
    protected function typeIcon(): string         { return 'cloud'; }
    protected function typeBadgeColor(): string   { return 'cyan'; }
}
