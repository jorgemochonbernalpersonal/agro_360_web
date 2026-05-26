<?php

namespace App\Livewire\Viticulturist\PlotCosts;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotCost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

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

    protected function rules(): array
    {
        return [
            'plot_id'           => $this->plotOwnershipRule(false),
            'campaign_id'       => $this->campaignOwnershipRule(false),
            'category'          => 'required|in:' . implode(',', array_keys(PlotCost::CATEGORIES)),
            'description'       => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01',
            'cost_date'         => 'required|date',
            'supplier'          => 'nullable|string|max:255',
            'invoice_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ];
    }

    public function save(): mixed
    {
        $data = $this->validate();

        PlotCost::create([
            ...$data,
            'viticulturist_id' => Auth::id(),
            'plot_id'          => $this->plot_id ?: null,
            'campaign_id'      => $this->campaign_id ?: null,
        ]);

        $this->toastSuccess(__('Coste registrado correctamente.'));

        return $this->viticulturistRoleRedirect('plot-costs.index');
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.viticulturist.plot-costs.create', [
            'plots'      => Plot::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'campaigns'  => Campaign::where('viticulturist_id', $user->id)->orderByDesc('year')->get(),
            'categories' => PlotCost::CATEGORIES,
        ])->layout('layouts.app');
    }
}
