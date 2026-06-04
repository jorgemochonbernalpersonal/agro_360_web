<?php

namespace App\Livewire\Viticulturist\Pac\Declarations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\PacDeclaration;
use App\Models\PacDeclarationItem;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithViticulturistValidation;

    public int $year;

    public string $reference_number = '';

    public string $notes = '';

    // items[plot_id] = ['selected' => bool, 'declared_area' => '', 'eligible_area' => '', 'eco_schemes' => []]
    public array $items = [];

    public function mount(): void
    {
        $this->year = now()->year;

        // Verificar que no exista ya declaración para este año
        if (PacDeclaration::forViticulturist(Auth::id())->where('year', $this->year)->exists()) {
            $this->redirectRoute('viticulturist.pac.declarations.index', navigate: true);

            return;
        }

        $this->loadPlots();
    }

    public function updatedYear(): void
    {
        if (PacDeclaration::forViticulturist(Auth::id())->where('year', $this->year)->exists()) {
            $this->redirectRoute('viticulturist.pac.declarations.index', navigate: true);

            return;
        }

        $this->loadPlots();
    }

    public function save(string $status = 'draft'): void
    {
        $selectedIds = array_keys(array_filter($this->items, fn ($i) => $i['selected'] ?? false));

        if (empty($selectedIds)) {
            $this->addError('items', __('Selecciona al menos una parcela para la declaración.'));

            return;
        }

        $this->validate();

        if (! $this->validatePacDeclaredAreas($selectedIds, $this->items)) {
            $this->toastError(__('Revisa las superficies declaradas antes de continuar.'));

            return;
        }

        DB::transaction(function () use ($selectedIds, $status) {
            $declaration = PacDeclaration::create([
                'viticulturist_id' => Auth::id(),
                'year' => $this->year,
                'reference_number' => $this->reference_number ?: null,
                'status' => $status,
                'submitted_at' => $status === 'submitted' ? now() : null,
                'notes' => $this->notes ?: null,
                'total_declared_area' => 0,
                'total_eligible_area' => 0,
            ]);

            foreach ($selectedIds as $plotId) {
                $item = $this->items[$plotId];
                PacDeclarationItem::create([
                    'declaration_id' => $declaration->id,
                    'plot_id' => $plotId,
                    'declared_area' => $item['declared_area'],
                    'eligible_area' => $item['eligible_area'],
                    'eco_schemes' => ! empty($item['eco_schemes']) ? $item['eco_schemes'] : null,
                ]);
            }

            $declaration->recalculateTotals();
        });

        $label = $status === 'submitted' ? 'presentada' : 'guardada como borrador';
        $this->toastSuccess("Declaración PAC {$this->year} {$label} correctamente.");
        $this->redirectRoute('viticulturist.pac.declarations.index', navigate: true);
    }

    public function getSelectedCountProperty(): int
    {
        return count(array_filter($this->items, fn ($i) => $i['selected'] ?? false));
    }

    public function getTotalDeclaredProperty(): float
    {
        return collect($this->items)
            ->filter(fn ($i) => $i['selected'] ?? false)
            ->sum(fn ($i) => (float) ($i['declared_area'] ?? 0));
    }

    public function getTotalEligibleProperty(): float
    {
        return collect($this->items)
            ->filter(fn ($i) => $i['selected'] ?? false)
            ->sum(fn ($i) => (float) ($i['eligible_area'] ?? 0));
    }

    public function render()
    {
        $existingYears = PacDeclaration::forViticulturist(Auth::id())
            ->pluck('year')
            ->toArray();

        return view('livewire.viticulturist.pac.declarations.create', [
            'ecoSchemes' => PacDeclaration::ECO_SCHEMES,
            'existingYears' => $existingYears,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        $rules = [
            'year' => [
                'required', 'integer', 'min:2000', 'max:'.(now()->year + 1),
                \Illuminate\Validation\Rule::unique('pac_declarations', 'year')
                    ->where('viticulturist_id', Auth::id()),
            ],
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];

        foreach ($this->items as $plotId => $item) {
            if ($item['selected'] ?? false) {
                $rules["items.{$plotId}.declared_area"] = 'required|numeric|min:0.001';
                $rules["items.{$plotId}.eligible_area"] = 'required|numeric|min:0';
                $rules["items.{$plotId}.eco_schemes"] = 'nullable|array';
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'year.required' => __('El año es obligatorio.'),
            'year.unique' => __('Ya existe una declaración para el año :input.'),
            'items.*.declared_area.required' => __('La superficie declarada es obligatoria.'),
            'items.*.declared_area.min' => __('La superficie declarada debe ser mayor que 0.'),
            'items.*.eligible_area.required' => __('La superficie admisible es obligatoria.'),
        ];
    }

    private function loadPlots(): void
    {
        $plots = Plot::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $this->items = [];
        foreach ($plots as $plot) {
            $this->items[$plot->id] = [
                'selected' => false,
                'declared_area' => $plot->pac_eligible_area ?? $plot->area ?? '',
                'eligible_area' => $plot->pac_eligible_area ?? '',
                'eco_schemes' => $plot->is_organic ? ['organic'] : [],
                'name' => $plot->name,
                'area' => $plot->area,
            ];
        }
    }
}
