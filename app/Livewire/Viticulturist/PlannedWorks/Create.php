<?php

namespace App\Livewire\Viticulturist\PlannedWorks;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\PlannedWork;
use App\Models\Plot;

class Create extends AbstractCreate
{
    public string $campaign_id     = '';
    public string $plot_id         = '';
    public string $category        = 'otro';
    public string $title           = '';
    public string $description     = '';
    public string $planned_date    = '';
    public string $planned_end_date = '';
    public string $priority        = 'media';
    public string $notes           = '';

    public function mount(): void
    {
        $this->planned_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'      => 'nullable|exists:campaigns,id',
            'plot_id'          => 'nullable|exists:plots,id',
            'category'         => 'required|in:' . implode(',', array_keys(PlannedWork::CATEGORIES)),
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'planned_date'     => 'required|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_date',
            'priority'         => 'required|in:' . implode(',', array_keys(PlannedWork::PRIORITIES)),
            'notes'            => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        PlannedWork::create([
            'viticulturist_id' => $this->viticulturistId(),
            'campaign_id'      => $this->campaign_id ?: null,
            'plot_id'          => $this->plot_id ?: null,
            'category'         => $this->category,
            'title'            => $this->title,
            'description'      => $this->description ?: null,
            'planned_date'     => $this->planned_date,
            'planned_end_date' => $this->planned_end_date ?: null,
            'priority'         => $this->priority,
            'notes'            => $this->notes ?: null,
            'status'           => 'pendiente',
        ]);
    }

    protected function successMessage(): string { return 'Trabajo planificado registrado correctamente.'; }
    protected function indexRoute(): string      { return $this->rolePrefix() . '.planned-works.index'; }

    protected function viewData(): array
    {
        return [
            'categories' => PlannedWork::CATEGORIES,
            'priorities' => PlannedWork::PRIORITIES,
            'campaigns'  => Campaign::where('viticulturist_id', $this->viticulturistId())
                                ->orderByDesc('year')->get(['id', 'name', 'year']),
            'plots'      => Plot::where('viticulturist_id', $this->viticulturistId())
                                ->orderBy('name')->get(['id', 'name', 'municipality_id']),
        ];
    }
}
