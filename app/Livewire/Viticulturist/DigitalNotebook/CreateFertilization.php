<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Fertilization;
use App\Services\NotebookActivityService;
use Illuminate\Support\Facades\Auth;

class CreateFertilization extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $fertilizer_type = '';

    public $fertilizer_name = '';

    public $quantity = '';

    public $npk_ratio = '';

    public $application_method = '';

    public $area_applied = '';

    public $nitrogen_uf = '';

    public $phosphorus_uf = '';

    public $potassium_uf = '';

    public $manure_type = '';

    public $burial_date = '';

    public $emission_reduction_method = '';

    public $is_organic = false;

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
    }

    // ─── Reactive ─────────────────────────────────────────────────────────────

    public function updatedFertilizerType(): void
    {
        $this->is_organic = $this->detectIsOrganic($this->fertilizer_type);
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            app(NotebookActivityService::class)->createActivity(
                $this->activityData('fertilization'),
                fn (int $activityId) => Fertilization::create([
                    'activity_id' => $activityId,
                    'fertilizer_type' => $this->fertilizer_type,
                    'fertilizer_name' => $this->fertilizer_name,
                    'quantity' => $this->quantity ?: null,
                    'npk_ratio' => $this->npk_ratio,
                    'application_method' => $this->application_method,
                    'area_applied' => $this->area_applied ?: null,
                    'nitrogen_uf' => $this->nitrogen_uf ?: null,
                    'phosphorus_uf' => $this->phosphorus_uf ?: null,
                    'potassium_uf' => $this->potassium_uf ?: null,
                    'manure_type' => $this->manure_type ?: null,
                    'burial_date' => $this->burial_date ?: null,
                    'emission_reduction_method' => $this->emission_reduction_method ?: null,
                ]),
            );

            $this->toastSuccess(__('Fertilización registrada correctamente.'));

            return $this->viticulturistRoleRedirect('digital-notebook.fertilization.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar fertilización', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError(__('Error al registrar la fertilización. Por favor, intenta de nuevo.'));
        }

        return null;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-fertilization', $this->renderData())
            ->layout('layouts.app');
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'phenological_stage' => 'nullable|string|max:50',
            'fertilizer_type' => 'required|string|max:100',
            'fertilizer_name' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'npk_ratio' => 'nullable|string|max:50',
            'application_method' => 'nullable|in:aplicación al suelo,fertirrigación,aplicación foliar,otro',
            'area_applied' => 'required|numeric|min:0.01',
            // PAC Nutrición (BCAM 6) — al menos un valor NPK
            'nitrogen_uf' => 'required_without_all:phosphorus_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'phosphorus_uf' => 'required_without_all:nitrogen_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'potassium_uf' => 'required_without_all:nitrogen_uf,phosphorus_uf|nullable|numeric|min:0|max:1000',
            // Campos orgánicos — obligatorios solo si is_organic
            'manure_type' => $this->is_organic ? 'required|string|max:100' : 'nullable|string|max:100',
            'burial_date' => $this->is_organic ? 'required|date|before_or_equal:today' : 'nullable|date|before_or_equal:today',
            'emission_reduction_method' => 'nullable|in:inyección,platos,tubos,enterrado_inmediato,otro',
        ]);
    }

    protected function messages(): array
    {
        return [
            'manure_type.required' => __('El tipo de estiércol es obligatorio para fertilizantes orgánicos (BCAM 6).'),
            'burial_date.required' => __('La fecha de enterrado es obligatoria para fertilizantes orgánicos (BCAM 6).'),
        ];
    }

    private function detectIsOrganic(string $type): bool
    {
        $lower = strtolower($type);
        $hasOrganicKeyword = str_contains($lower, 'orgán')
            || str_contains($lower, 'organ')
            || str_contains($lower, 'estiér')
            || str_contains($lower, 'estier');
        $isInorganic = str_contains($lower, 'inorgán') || str_contains($lower, 'inorgan');

        return $hasOrganicKeyword && ! $isInorganic;
    }
}
