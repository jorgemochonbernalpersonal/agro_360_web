<?php

namespace App\Livewire\Viticulturist\PlotCosts;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotCost;
use Illuminate\Support\Facades\Auth;

class Create extends AbstractCreate
{
    public string $plot_id = '';

    public string $campaign_id = '';

    public string $category = 'other';

    public string $description = '';

    public string $amount = '';

    public string $cost_date = '';

    public string $supplier = '';

    public string $invoice_reference = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->cost_date = now()->format('Y-m-d');

        $campaign = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->first();
        if ($campaign) {
            $this->campaign_id = (string) $campaign->id;
        }
    }

    protected function performCreate(): void
    {
        PlotCost::create([
            'viticulturist_id' => $this->ownerId(),
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
        return __('Coste registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'plot-costs.index';
    }

    protected function viewData(): array
    {
        $userId = $this->ownerId();

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
