<?php

namespace App\Livewire\Viticulturist\Pac\Declarations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\PacDeclaration;
use App\Models\PacDeclarationItem;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public PacDeclaration $declaration;

    public string $reference_number = '';
    public string $notes            = '';

    // items[plot_id] = ['selected' => bool, 'declared_area' => '', 'eligible_area' => '', 'eco_schemes' => []]
    public array $items = [];

    public function mount(PacDeclaration $declaration): void
    {
        if ($declaration->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        if (!$declaration->isDraft()) {
            $this->redirectRoute('viticulturist.pac.declarations.show', $declaration, navigate: true);
            return;
        }

        $this->declaration      = $declaration;
        $this->reference_number = $declaration->reference_number ?? '';
        $this->notes            = $declaration->notes ?? '';

        $this->loadItems();
    }

    private function loadItems(): void
    {
        $plots = Plot::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $existingItems = $this->declaration->items->keyBy('plot_id');

        $this->items = [];
        foreach ($plots as $plot) {
            $existing = $existingItems->get($plot->id);
            $this->items[$plot->id] = [
                'selected'      => $existing !== null,
                'declared_area' => $existing?->declared_area ?? $plot->pac_eligible_area ?? $plot->area ?? '',
                'eligible_area' => $existing?->eligible_area ?? $plot->pac_eligible_area ?? '',
                'eco_schemes'   => $existing?->eco_schemes ?? ($plot->is_organic ? ['organic'] : []),
                'name'          => $plot->name,
                'area'          => $plot->area,
            ];
        }
    }

    protected function rules(): array
    {
        $rules = [
            'reference_number' => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
        ];

        foreach ($this->items as $plotId => $item) {
            if ($item['selected'] ?? false) {
                $rules["items.{$plotId}.declared_area"] = 'required|numeric|min:0.001';
                $rules["items.{$plotId}.eligible_area"] = 'required|numeric|min:0';
                $rules["items.{$plotId}.eco_schemes"]   = 'nullable|array';
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'items.*.declared_area.required' => 'La superficie declarada es obligatoria.',
            'items.*.declared_area.min'      => 'La superficie declarada debe ser mayor que 0.',
            'items.*.eligible_area.required' => 'La superficie admisible es obligatoria.',
        ];
    }

    public function save(string $status = 'draft'): void
    {
        $selectedIds = array_keys(array_filter($this->items, fn($i) => $i['selected'] ?? false));

        if (empty($selectedIds)) {
            $this->addError('items', 'Selecciona al menos una parcela.');
            return;
        }

        $this->validate();

        DB::transaction(function () use ($selectedIds, $status) {
            $this->declaration->update([
                'reference_number' => $this->reference_number ?: null,
                'status'           => $status,
                'submitted_at'     => $status === 'submitted' ? now() : null,
                'notes'            => $this->notes ?: null,
            ]);

            // Sincronizar items: eliminar los que no están seleccionados
            $this->declaration->items()->whereNotIn('plot_id', $selectedIds)->delete();

            foreach ($selectedIds as $plotId) {
                $item = $this->items[$plotId];
                PacDeclarationItem::updateOrCreate(
                    ['declaration_id' => $this->declaration->id, 'plot_id' => $plotId],
                    [
                        'declared_area' => $item['declared_area'],
                        'eligible_area' => $item['eligible_area'],
                        'eco_schemes'   => !empty($item['eco_schemes']) ? $item['eco_schemes'] : null,
                    ]
                );
            }

            $this->declaration->recalculateTotals();
        });

        $label = $status === 'submitted' ? 'presentada' : 'guardada';
        $this->toastSuccess("Declaración PAC {$this->declaration->year} {$label} correctamente.");
        $this->redirectRoute('viticulturist.pac.declarations.show', $this->declaration, navigate: true);
    }

    public function getSelectedCountProperty(): int
    {
        return count(array_filter($this->items, fn($i) => $i['selected'] ?? false));
    }

    public function getTotalDeclaredProperty(): float
    {
        return collect($this->items)
            ->filter(fn($i) => $i['selected'] ?? false)
            ->sum(fn($i) => (float) ($i['declared_area'] ?? 0));
    }

    public function getTotalEligibleProperty(): float
    {
        return collect($this->items)
            ->filter(fn($i) => $i['selected'] ?? false)
            ->sum(fn($i) => (float) ($i['eligible_area'] ?? 0));
    }

    public function render()
    {
        return view('livewire.viticulturist.pac.declarations.edit', [
            'ecoSchemes' => PacDeclaration::ECO_SCHEMES,
        ])->layout('layouts.app');
    }
}
