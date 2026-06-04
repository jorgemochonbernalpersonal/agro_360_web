<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class CulturalWorkIndex extends AbstractActivityIndex
{
    protected function activityType(): string
    {
        return 'cultural';
    }

    protected function pageTitle(): string
    {
        return __('Labores Culturales');
    }

    protected function pageDescription(): string
    {
        return __('Registro de labores culturales realizadas en tus parcelas');
    }

    protected function createRoute(): string
    {
        return roleRoute('digital-notebook.cultural.create');
    }

    protected function editRouteSuffix(): string
    {
        return 'digital-notebook.cultural.edit';
    }

    protected function typeIcon(): string
    {
        return 'wrench-screwdriver';
    }

    protected function typeBadgeColor(): string
    {
        return 'yellow';
    }
}
