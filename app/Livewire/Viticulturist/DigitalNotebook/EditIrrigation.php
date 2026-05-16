<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Irrigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditIrrigation extends AbstractActivityForm
{
    // ─── Model instances ──────────────────────────────────────────────────────

    public AgriculturalActivity $activity;
    public Irrigation           $irrigation;

    // ─── Type-specific properties ─────────────────────────────────────────────

    public $water_volume           = '';
    public $water_volume_unit      = 'L';
    public $irrigation_method      = '';
    public $duration_minutes       = '';
    public $soil_moisture_before   = '';
    public $soil_moisture_after    = '';
    public $water_source           = '';
    public $water_concession       = '';
    public $flow_rate              = '';
    public $is_fertirrigation      = false;
    public $fertilizer_product     = '';
    public $fertilizer_dose_per_ha = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['irrigation', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if (!$this->mountEditGuards($activity, 'irrigation', 'digital-notebook.irrigation.index')) {
            return;
        }

        $this->irrigation = $this->activity->irrigation;
        $this->loadActivityFields($this->activity);
        $this->loadAvailablePlantings();

        $this->water_volume           = $this->irrigation->water_volume;
        $this->water_volume_unit      = $this->irrigation->water_volume_unit ?? 'L';
        $this->irrigation_method      = $this->irrigation->irrigation_method ?? '';
        $this->duration_minutes       = $this->irrigation->duration_minutes ?? '';
        $this->soil_moisture_before   = $this->irrigation->soil_moisture_before ?? '';
        $this->soil_moisture_after    = $this->irrigation->soil_moisture_after ?? '';
        $this->water_source           = $this->irrigation->water_source ?? '';
        $this->water_concession       = $this->irrigation->water_concession ?? '';
        $this->flow_rate              = $this->irrigation->flow_rate ?? '';
        $this->is_fertirrigation      = $this->irrigation->is_fertirrigation ?? false;
        $this->fertilizer_product     = $this->irrigation->fertilizer_product ?? '';
        $this->fertilizer_dose_per_ha = $this->irrigation->fertilizer_dose_per_ha ?? '';
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'water_volume'         => 'required|numeric|min:0.01|max:1000000',
            'water_volume_unit'    => 'required|in:L,m3',
            'irrigation_method'    => 'nullable|string|max:50',
            'duration_minutes'     => 'nullable|integer|min:0',
            'soil_moisture_before' => 'nullable|numeric|min:0|max:100',
            'soil_moisture_after'  => 'nullable|numeric|min:0|max:100',
            'water_source'         => 'required|string|max:100',
            'water_concession'     => 'required|string|max:100',
            'flow_rate'            => 'required|numeric|min:0|max:100000',
            'is_fertirrigation'       => 'boolean',
            'fertilizer_product'      => 'nullable|string|max:150',
            'fertilizer_dose_per_ha'  => 'nullable|numeric|min:0',
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $this->activity->update($this->activityData('irrigation'));

                $this->irrigation->update([
                    'water_volume'           => $this->water_volume ?: null,
                    'water_volume_unit'      => $this->water_volume_unit,
                    'irrigation_method'      => $this->irrigation_method,
                    'duration_minutes'       => $this->duration_minutes ?: null,
                    'soil_moisture_before'   => $this->soil_moisture_before ?: null,
                    'soil_moisture_after'    => $this->soil_moisture_after ?: null,
                    'water_source'           => $this->water_source ?: null,
                    'water_concession'       => $this->water_concession ?: null,
                    'flow_rate'              => $this->flow_rate ?: null,
                    'is_fertirrigation'      => $this->is_fertirrigation,
                    'fertilizer_product'     => $this->is_fertirrigation ? ($this->fertilizer_product ?: null) : null,
                    'fertilizer_dose_per_ha' => $this->is_fertirrigation ? ($this->fertilizer_dose_per_ha ?: null) : null,
                ]);
            });

            $this->toastSuccess('Riego actualizado correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.irrigation.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar riego', ['error' => $e->getMessage(), 'user_id' => Auth::id(), 'activity_id' => $this->activity->id]);
            $this->toastError('Error al actualizar el riego. Por favor, intenta de nuevo.');
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.edit-irrigation', $this->renderData())
            ->layout('layouts.app', ['title' => 'Editar Riego - Agro365']);
    }
}
