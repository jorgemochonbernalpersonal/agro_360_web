<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\CulturalWork;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateCulturalWork extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $work_type                   = '';
    public $hours_worked                = '';
    public $workers_count               = '';
    public $description                 = '';
    public $pruning_type                = '';
    public $productive_buds_per_hectare = '';
    public $residue_management          = '';
    public $defoliation_face            = '';
    public $topping_height_cm           = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'work_type'                   => 'required|in:poda,deshojado,despuntado,laboreo,desbroce,otro',
            'hours_worked'                => 'nullable|numeric|min:0',
            'workers_count'               => 'nullable|integer|min:1',
            'description'                 => 'required|string|min:10',
            'pruning_type'                => 'nullable|string|max:50',
            'productive_buds_per_hectare' => 'nullable|integer|min:0',
            'residue_management'          => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            'defoliation_face'            => 'nullable|in:norte,sur,ambas',
            'topping_height_cm'           => 'nullable|integer|min:1|max:300',
        ]);
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                $activity = AgriculturalActivity::create($this->activityData('cultural'));

                CulturalWork::create([
                    'activity_id'                 => $activity->id,
                    'work_type'                   => $this->work_type,
                    'hours_worked'                => $this->hours_worked ?: null,
                    'workers_count'               => $this->workers_count ?: null,
                    'description'                 => $this->description,
                    'pruning_type'                => $this->work_type === 'poda' ? ($this->pruning_type ?: null) : null,
                    'productive_buds_per_hectare' => $this->work_type === 'poda' ? ($this->productive_buds_per_hectare ?: null) : null,
                    'residue_management'          => $this->residue_management ?: null,
                    'defoliation_face'            => $this->work_type === 'deshojado' ? ($this->defoliation_face ?: null) : null,
                    'topping_height_cm'           => $this->work_type === 'despuntado' ? ($this->topping_height_cm ?: null) : null,
                ]);
            });

            $this->toastSuccess(__('Labor cultural registrada correctamente.'));
            return $this->viticulturistRoleRedirect('digital-notebook.cultural.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar labor cultural', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError(__('Error al registrar la labor cultural. Por favor, intenta de nuevo.'));
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-cultural-work', $this->renderData())
            ->layout('layouts.app');
    }
}
