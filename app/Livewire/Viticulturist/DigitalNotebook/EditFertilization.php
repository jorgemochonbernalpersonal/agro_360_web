<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Fertilization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditFertilization extends AbstractActivityForm
{
    // ─── Model instances ──────────────────────────────────────────────────────

    public AgriculturalActivity $activity;
    public Fertilization        $fertilization;

    // ─── Type-specific properties ─────────────────────────────────────────────

    public $fertilizer_type            = '';
    public $fertilizer_name            = '';
    public $quantity                   = '';
    public $npk_ratio                  = '';
    public $application_method         = '';
    public $area_applied               = '';
    public $nitrogen_uf                = '';
    public $phosphorus_uf              = '';
    public $potassium_uf               = '';
    public $manure_type                = '';
    public $burial_date                = '';
    public $emission_reduction_method  = '';
    public $is_organic                 = false;

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['fertilization', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if (!$this->mountEditGuards($activity, 'fertilization', 'digital-notebook.fertilization.index')) {
            return;
        }

        $this->fertilization = $this->activity->fertilization;
        $this->loadActivityFields($this->activity);
        $this->loadAvailablePlantings();

        $this->fertilizer_type           = $this->fertilization->fertilizer_type;
        $this->fertilizer_name           = $this->fertilization->fertilizer_name ?? '';
        $this->quantity                  = $this->fertilization->quantity;
        $this->npk_ratio                 = $this->fertilization->npk_ratio ?? '';
        $this->application_method        = $this->fertilization->application_method ?? '';
        $this->area_applied              = $this->fertilization->area_applied;
        $this->nitrogen_uf               = $this->fertilization->nitrogen_uf ?? '';
        $this->phosphorus_uf             = $this->fertilization->phosphorus_uf ?? '';
        $this->potassium_uf              = $this->fertilization->potassium_uf ?? '';
        $this->manure_type               = $this->fertilization->manure_type ?? '';
        $this->burial_date               = $this->fertilization->burial_date
            ? Carbon::parse($this->fertilization->burial_date)->format('Y-m-d') : '';
        $this->emission_reduction_method = $this->fertilization->emission_reduction_method ?? '';

        // Detect organic status from saved type
        $this->is_organic = $this->detectIsOrganic($this->fertilizer_type);
    }

    // ─── Reactive ─────────────────────────────────────────────────────────────

    public function updatedFertilizerType(): void
    {
        $this->is_organic = $this->detectIsOrganic($this->fertilizer_type);
    }

    private function detectIsOrganic(string $type): bool
    {
        $lower = strtolower($type);
        $hasOrganicKeyword = str_contains($lower, 'orgán')
            || str_contains($lower, 'organ')
            || str_contains($lower, 'estiér')
            || str_contains($lower, 'estier');
        $isInorganic = str_contains($lower, 'inorgán') || str_contains($lower, 'inorgan');
        return $hasOrganicKeyword && !$isInorganic;
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'phenological_stage'       => 'nullable|string|max:50',
            'fertilizer_type'          => 'required|string|max:100',
            'fertilizer_name'          => 'nullable|string|max:255',
            'quantity'                 => 'required|numeric|min:0.01',
            'npk_ratio'                => 'nullable|string|max:50',
            'application_method'       => 'nullable|in:aplicación al suelo,fertirrigación,aplicación foliar,otro',
            'area_applied'             => 'required|numeric|min:0.01',
            'nitrogen_uf'   => 'required_without_all:phosphorus_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'phosphorus_uf' => 'required_without_all:nitrogen_uf,potassium_uf|nullable|numeric|min:0|max:1000',
            'potassium_uf'  => 'required_without_all:nitrogen_uf,phosphorus_uf|nullable|numeric|min:0|max:1000',
            'manure_type'  => $this->is_organic ? 'required|string|max:100'             : 'nullable|string|max:100',
            'burial_date'  => $this->is_organic ? 'required|date|before_or_equal:today' : 'nullable|date|before_or_equal:today',
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

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $this->activity->update($this->activityData('fertilization'));

                $this->fertilization->update([
                    'fertilizer_type'           => $this->fertilizer_type,
                    'fertilizer_name'           => $this->fertilizer_name,
                    'quantity'                  => $this->quantity ?: null,
                    'npk_ratio'                 => $this->npk_ratio,
                    'application_method'        => $this->application_method,
                    'area_applied'              => $this->area_applied ?: null,
                    'nitrogen_uf'               => $this->nitrogen_uf ?: null,
                    'phosphorus_uf'             => $this->phosphorus_uf ?: null,
                    'potassium_uf'              => $this->potassium_uf ?: null,
                    'manure_type'               => $this->manure_type ?: null,
                    'burial_date'               => $this->burial_date ?: null,
                    'emission_reduction_method' => $this->emission_reduction_method ?: null,
                ]);
            });

            $this->toastSuccess(__('Fertilización actualizada correctamente.'));
            return $this->viticulturistRoleRedirect('digital-notebook.fertilization.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar fertilización', ['error' => $e->getMessage(), 'user_id' => Auth::id(), 'activity_id' => $this->activity->id]);
            $this->toastError(__('Error al actualizar la fertilización. Por favor, intenta de nuevo.'));
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.edit-fertilization', $this->renderData())
            ->layout('layouts.app', ['title' => __('Editar Fertilización - Agro365')]);
    }
}
