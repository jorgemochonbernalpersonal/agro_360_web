<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PostHarvestTreatment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class EditPostHarvest extends AbstractActivityForm
{
    // ─── Model instances ──────────────────────────────────────────────────────

    public AgriculturalActivity  $activity;
    public PostHarvestTreatment  $postHarvestTreatment;

    // ─── Type-specific properties ─────────────────────────────────────────────

    public $product_id             = '';
    public $application_type       = '';
    public $treated_area_ha        = '';
    public $dose_per_hectare       = '';
    public $dose_unit              = 'kg/ha';
    public $water_volume_liters    = '';
    public $reentry_interval_hours = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    // ─── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function products()
    {
        return PhytosanitaryProduct::forUser(Auth::id())->where('active', true)->orderBy('name')->get();
    }

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(AgriculturalActivity $activity): void
    {
        $this->activity = $activity->load(['postHarvestTreatment', 'plot', 'plotPlanting', 'crew', 'crewMember']);

        if (!$this->mountEditGuards($activity, 'post_harvest', 'digital-notebook.post-harvest.index')) {
            return;
        }

        $this->postHarvestTreatment = $this->activity->postHarvestTreatment;
        $this->loadActivityFields($this->activity);
        $this->loadAvailablePlantings();

        $this->product_id              = $this->postHarvestTreatment->product_id ?? '';
        $this->application_type        = $this->postHarvestTreatment->application_type;
        $this->treated_area_ha         = $this->postHarvestTreatment->treated_area_ha;
        $this->dose_per_hectare        = $this->postHarvestTreatment->dose_per_hectare ?? '';
        $this->dose_unit               = $this->postHarvestTreatment->dose_unit ?? 'kg/ha';
        $this->water_volume_liters     = $this->postHarvestTreatment->water_volume_liters ?? '';
        $this->reentry_interval_hours  = $this->postHarvestTreatment->reentry_interval_hours ?? '';
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'product_id'             => 'nullable|exists:phytosanitary_products,id',
            'application_type'       => 'required|string|in:' . implode(',', array_keys(PostHarvestTreatment::APPLICATION_TYPES)),
            'treated_area_ha'        => 'required|numeric|min:0.001',
            'dose_per_hectare'       => 'nullable|numeric|min:0',
            'dose_unit'              => 'nullable|string|max:20',
            'water_volume_liters'    => 'nullable|numeric|min:0',
            'reentry_interval_hours' => 'nullable|integer|min:0|max:168',
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $this->activity->update($this->activityData('post_harvest'));

                $this->postHarvestTreatment->update([
                    'product_id'             => $this->product_id ?: null,
                    'application_type'       => $this->application_type,
                    'treated_area_ha'        => $this->treated_area_ha,
                    'dose_per_hectare'       => $this->dose_per_hectare ?: null,
                    'dose_unit'              => $this->dose_per_hectare ? ($this->dose_unit ?: 'kg/ha') : null,
                    'water_volume_liters'    => $this->water_volume_liters ?: null,
                    'reentry_interval_hours' => $this->reentry_interval_hours !== '' ? (int) $this->reentry_interval_hours : null,
                    'notes'                  => $this->notes,
                ]);
            });

            $this->toastSuccess(__('Tratamiento post-vendimia actualizado correctamente.'));
            return $this->viticulturistRoleRedirect('digital-notebook.post-harvest.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar tratamiento post-vendimia', ['error' => $e->getMessage(), 'user_id' => Auth::id(), 'activity_id' => $this->activity->id]);
            $this->toastError(__('Error al actualizar el tratamiento. Por favor, intenta de nuevo.'));
        }

        return null;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.edit-post-harvest', $this->renderData([
            'products'         => $this->products,
            'applicationTypes' => PostHarvestTreatment::applicationTypeOptions(),
        ]))->layout('layouts.app', ['title' => __('Editar Tratamiento Post-Vendimia - Agro365')]);
    }
}
