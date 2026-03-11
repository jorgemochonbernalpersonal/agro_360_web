<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class PruningIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'pruning'; }
    protected function pageTitle(): string        { return 'Podas'; }
    protected function pageDescription(): string  { return 'Registro de podas realizadas en tus parcelas'; }
    protected function createRoute(): string      { return route('viticulturist.digital-notebook.pruning.create'); }
    protected function editRouteName(): string    { return 'viticulturist.digital-notebook.pruning.edit'; }
    protected function typeIcon(): string         { return 'scissors'; }
    protected function typeBadgeColor(): string   { return 'lime'; }
}
