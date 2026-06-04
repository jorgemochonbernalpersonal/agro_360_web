<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PostHarvestTreatment;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class CreatePostHarvest extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $product_id             = '';
    public $application_type       = '';
    public $treated_area_ha        = '';
    public $dose_per_hectare       = '';
    public $dose_unit              = 'kg/ha';
    public $water_volume_liters    = '';
    public $reentry_interval_hours = '';

    // ─── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function products()
    {
        return PhytosanitaryProduct::forUser(Auth::id())->where('active', true)->orderBy('name')->get();
    }

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
        $this->phenological_stage = 'Caída de hoja';
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

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $user = Auth::user();

                $wineryViticulturistId = null;
                if ($user->isViticulturist()) {
                    $wineryViticulturistId = WineryViticulturist::where('viticulturist_id', $user->id)
                        ->whereNotNull('winery_id')
                        ->value('id');
                }

                $activity = AgriculturalActivity::create(
                    $this->activityData('post_harvest', array_filter(
                        ['winery_viticulturist_id' => $wineryViticulturistId],
                        fn ($v) => $v !== null
                    ))
                );

                PostHarvestTreatment::create([
                    'activity_id'            => $activity->id,
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

            $this->toastSuccess(__('Tratamiento post-vendimia registrado correctamente.'));
            return $this->viticulturistRoleRedirect('digital-notebook.post-harvest.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar tratamiento post-vendimia', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError(__('Error al registrar el tratamiento. Por favor, intenta de nuevo.'));
        }

        return null;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-post-harvest', $this->renderData([
            'products'         => $this->products,
            'applicationTypes' => PostHarvestTreatment::applicationTypeOptions(),
        ]))->layout('layouts.app', ['title' => __('Registrar Tratamiento Post-Vendimia - Agro365')]);
    }
}
