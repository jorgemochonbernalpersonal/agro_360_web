<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\CulturalWork;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePruning extends AbstractActivityForm
{
    // ─── Type-specific properties ─────────────────────────────────────────────

    public $pruning_type                = '';
    public $productive_buds_per_hectare = '';
    public $residue_management          = '';
    public $hours_worked                = '';
    public $workers_count               = '';
    public $description                 = '';

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->mountCreate();
        $this->phenological_stage = 'Reposo invernal';
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return array_merge($this->commonRules(), [
            'pruning_type'                => 'required|string|max:50',
            'productive_buds_per_hectare' => 'nullable|integer|min:0',
            'residue_management'          => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            'hours_worked'                => 'nullable|numeric|min:0',
            'workers_count'               => 'nullable|integer|min:1',
            'description'                 => 'required|string|min:10',
        ]);
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $this->validate();
        $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () {
                // Link to winery if viticulturist is invited
                $wineryViticulturistId = null;
                $user = Auth::user();
                if ($user->isViticulturist()) {
                    $wineryViticulturistId = WineryViticulturist::where('viticulturist_id', $user->id)
                        ->whereNotNull('winery_id')
                        ->value('id');
                }

                $activity = AgriculturalActivity::create(
                    $this->activityData('pruning', array_filter(
                        ['winery_viticulturist_id' => $wineryViticulturistId],
                        fn ($v) => $v !== null
                    ))
                );

                CulturalWork::create([
                    'activity_id'                 => $activity->id,
                    'work_type'                   => 'poda',
                    'pruning_type'                => $this->pruning_type,
                    'productive_buds_per_hectare' => $this->productive_buds_per_hectare ?: null,
                    'residue_management'          => $this->residue_management ?: null,
                    'hours_worked'                => $this->hours_worked ?: null,
                    'workers_count'               => $this->workers_count ?: null,
                    'description'                 => $this->description,
                ]);
            });

            $this->toastSuccess('Poda registrada correctamente.');
            return $this->viticulturistRoleRedirect('digital-notebook.pruning.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar poda', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError('Error al registrar la poda. Por favor, intenta de nuevo.');
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.digital-notebook.create-pruning', $this->renderData())
            ->layout('layouts.app', ['title' => 'Registrar Poda - Agro365']);
    }
}
