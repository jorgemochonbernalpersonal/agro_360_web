<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class PostHarvestIndex extends AbstractActivityIndex
{
    protected function activityType(): string
    {
        return 'post_harvest';
    }

    protected function pageTitle(): string
    {
        return __('Post-Vendimia');
    }

    protected function pageDescription(): string
    {
        return __('Registro de tratamientos y labores post-vendimia en tus parcelas');
    }

    protected function createRoute(): string
    {
        return roleRoute('digital-notebook.post-harvest.create');
    }

    protected function editRouteSuffix(): string
    {
        return 'digital-notebook.post-harvest.edit';
    }

    protected function typeIcon(): string
    {
        return 'archive-box';
    }

    protected function typeBadgeColor(): string
    {
        return 'indigo';
    }
}
