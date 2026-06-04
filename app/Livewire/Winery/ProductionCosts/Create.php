<?php

namespace App\Livewire\Winery\ProductionCosts;

use App\Models\Wine;
use App\Models\WineCost;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    public ?int $wine_id = null;

    public string $category = '';

    public string $description = '';

    public string $amount = '';

    public string $cost_date = '';

    public string $supplier = '';

    public string $invoice_reference = '';

    public string $notes = '';

    public function mount(?int $wineId = null): void
    {
        $this->wine_id = $wineId;
        $this->cost_date = now()->toDateString();
    }

    public function save(): void
    {
        $this->validate();

        $userId = Auth::id();

        // Ensure wine belongs to this winery
        $wine = Wine::where('id', $this->wine_id)->where('user_id', $userId)->firstOrFail();

        WineCost::create([
            'wine_id' => $wine->id,
            'user_id' => $userId,
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'cost_date' => $this->cost_date,
            'supplier' => $this->supplier ?: null,
            'invoice_reference' => $this->invoice_reference ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => $userId,
        ]);

        session()->flash('success', __('Coste registrado correctamente.'));
        $this->redirect(roleRoute('production-costs.index'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $userId = Auth::id();

        $wines = Wine::where('user_id', $userId)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('vintage')
            ->orderBy('name')
            ->get(['id', 'name', 'vintage', 'wine_type']);

        return view('livewire.winery.production-costs.create', [
            'wines' => $wines,
            'categories' => WineCost::categoryOptions(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'wine_id' => 'required|exists:wines,id',
            'category' => 'required|in:'.implode(',', array_keys(WineCost::CATEGORIES)),
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'cost_date' => 'required|date',
            'supplier' => 'nullable|string|max:150',
            'invoice_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
