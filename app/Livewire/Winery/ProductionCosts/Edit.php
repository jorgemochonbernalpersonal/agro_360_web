<?php

namespace App\Livewire\Winery\ProductionCosts;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Wine;
use App\Models\WineCost;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public WineCost $cost;

    public int $wine_id;

    public string $category = '';

    public string $description = '';

    public string $amount = '';

    public string $cost_date = '';

    public string $supplier = '';

    public string $invoice_reference = '';

    public string $notes = '';

    public function mount(WineCost $cost): void
    {
        $this->authorize('update', $cost);

        $this->cost = $cost;
        $this->wine_id = $cost->wine_id;
        $this->category = $cost->category;
        $this->description = $cost->description;
        $this->amount = (string) $cost->amount;
        $this->cost_date = $cost->cost_date->toDateString();
        $this->supplier = $cost->supplier ?? '';
        $this->invoice_reference = $cost->invoice_reference ?? '';
        $this->notes = $cost->notes ?? '';
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->cost);
        $this->cost->delete();
        session()->flash('success', __('Coste eliminado.'));
        $this->redirect(roleRoute('production-costs.index'), navigate: true);
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

    protected function performUpdate(): void
    {
        Wine::where('id', $this->wine_id)->where('user_id', Auth::id())->firstOrFail();

        $this->cost->update([
            'wine_id' => $this->wine_id,
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
        return 'winery.production-costs.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => Wine::where('user_id', Auth::id())
                ->whereNotIn('status', ['cancelled'])
                ->orderByDesc('vintage')
                ->orderBy('name')
                ->get(['id', 'name', 'vintage', 'wine_type']),
            'categories' => WineCost::categoryOptions(),
        ];
    }
}
