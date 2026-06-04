<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class PruningIndex extends AbstractActivityIndex
{
    protected function activityType(): string
    {
        return 'pruning';
    }

    protected function pageTitle(): string
    {
        return __('Podas');
    }

    protected function pageDescription(): string
    {
        return __('Registro de podas realizadas en tus parcelas');
    }

    protected function createRoute(): string
    {
        return roleRoute('digital-notebook.pruning.create');
    }

    protected function editRouteSuffix(): string
    {
        return 'digital-notebook.pruning.edit';
    }

    protected function typeIcon(): string
    {
        return 'scissors';
    }

    protected function typeBadgeColor(): string
    {
        return 'lime';
    }
}
