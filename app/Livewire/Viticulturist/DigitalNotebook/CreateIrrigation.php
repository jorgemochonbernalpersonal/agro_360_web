<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Irrigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateIrrigation extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $water_volume          = '';
    public $water_volume_unit     = 'L';
    public $irrigation_method     = '';
    public $duration_minutes      = '';
    public $soil_moisture_before  = '';
    public $soil_moisture_after   = '';
    // PAC
    public $water_source          = '';
    public $water_concession      = '';
    public $flow_rate             = '';
    // Fertirrigación
    public $is_fertirrigation     = false;
    public $fertilizer_product    = '';
    public $fertilizer_dose_per_ha = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
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
            // PAC obligatorios (BCAM 5 - RD 1078/2014)
            'water_source'         => 'required|string|max:100',
            'water_concession'     => 'required|string|max:100',
            'flow_rate'            => 'required|numeric|min:0|max:100000',
            // Fertirrigación
            'is_fertirrigation'       => 'boolean',
            'fertilizer_product'      => 'nullable|string|max:150',
            'fertilizer_dose_per_ha'  => 'nullable|numeric|min:0',
        ]);
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $activity = AgriculturalActivity::create($this->activityData('irrigation'));

                Irrigation::create([
                    'activity_id'            => $activity->id,
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

            $this->toastSuccess('Riego registrado correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.irrigation.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar riego', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError('Error al registrar el riego. Por favor, intenta de nuevo.');
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-irrigation', $this->renderData())
            ->layout('layouts.app');
    }
}
