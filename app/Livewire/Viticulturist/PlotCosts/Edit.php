<?php

namespace App\Livewire\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotCost;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public PlotCost $cost;

    public string $plot_id = '';

    public string $campaign_id = '';

    public string $category = 'other';

    public string $description = '';

    public string $amount = '';

    public string $cost_date = '';

    public string $supplier = '';

    public string $invoice_reference = '';

    public string $notes = '';

    public function mount(PlotCost $cost): void
    {
        $this->authorizeOwnership($cost);

        $this->cost = $cost;
        $this->plot_id = (string) ($cost->plot_id ?? '');
        $this->campaign_id = (string) ($cost->campaign_id ?? '');
        $this->category = $cost->category;
        $this->description = $cost->description;
        $this->amount = (string) $cost->amount;
        $this->cost_date = $cost->cost_date->format('Y-m-d');
        $this->supplier = $cost->supplier ?? '';
        $this->invoice_reference = $cost->invoice_reference ?? '';
        $this->notes = $cost->notes ?? '';
    }

    protected function performUpdate(): void
    {
        $this->cost->update([
            'plot_id' => $this->plot_id ?: null,
            'campaign_id' => $this->campaign_id ?: null,
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'cost_date' => $this->cost_date,
            'supplier' => $this->supplier ?: null,
            'invoice_reference' => $this->invoice_reference ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Coste actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'plot-costs.index';
    }

    protected function viewData(): array
    {
        $userId = Auth::id();

        return [
            'plots' => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'campaigns' => Campaign::where('viticulturist_id', $userId)->orderByDesc('year')->get(),
            'categories' => PlotCost::categoryOptions(),
        ];
    }

    protected function rules(): array
    {
        return [
            'plot_id' => $this->plotOwnershipRule(false),
            'campaign_id' => $this->campaignOwnershipRule(false),
            'category' => 'required|in:'.implode(',', array_keys(PlotCost::CATEGORIES)),
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'cost_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'invoice_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }
}
