<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class CulturalWorkIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'cultural'; }
    protected function pageTitle(): string        { return 'Labores Culturales'; }
    protected function pageDescription(): string  { return 'Registro de labores culturales realizadas en tus parcelas'; }
    protected function createRoute(): string      { return route('viticulturist.digital-notebook.cultural.create'); }
    protected function editRouteName(): string    { return 'viticulturist.digital-notebook.cultural.edit'; }
    protected function typeIcon(): string         { return 'wrench-screwdriver'; }
    protected function typeBadgeColor(): string   { return 'yellow'; }
}
