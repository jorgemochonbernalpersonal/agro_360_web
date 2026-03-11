<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

class PostHarvestIndex extends AbstractActivityIndex
{
    protected function activityType(): string     { return 'post_harvest'; }
    protected function pageTitle(): string        { return 'Post-Vendimia'; }
    protected function pageDescription(): string  { return 'Registro de tratamientos y labores post-vendimia en tus parcelas'; }
    protected function createRoute(): string      { return route('viticulturist.digital-notebook.post-harvest.create'); }
    protected function editRouteName(): string    { return 'viticulturist.digital-notebook.post-harvest.edit'; }
    protected function typeIcon(): string         { return 'archive-box'; }
    protected function typeBadgeColor(): string   { return 'indigo'; }
}
