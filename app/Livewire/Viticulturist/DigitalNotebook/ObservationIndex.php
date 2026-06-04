<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class ObservationIndex extends AbstractActivityIndex
{
    protected function activityType(): string
    {
        return 'observation';
    }

    protected function pageTitle(): string
    {
        return __('Observaciones');
    }

    protected function pageDescription(): string
    {
        return __('Registro de observaciones realizadas en tus parcelas');
    }

    protected function createRoute(): string
    {
        return roleRoute('digital-notebook.observation.create');
    }

    protected function editRouteSuffix(): string
    {
        return 'digital-notebook.observation.edit';
    }

    protected function typeIcon(): string
    {
        return 'eye';
    }

    protected function typeBadgeColor(): string
    {
        return '';
    }
}
