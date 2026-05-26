<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;
    public $name = '';
    public $year = '';
    public $start_date = '';
    public $end_date = '';
    public $description = '';
    public $active = false;

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('create', Campaign::class)) {
            abort(403, __('No tienes permiso para crear campañas.'));
        }

        // Valores por defecto
        $this->year = now()->year;
        $this->start_date = now()->startOfYear()->format('Y-m-d');
        $this->end_date = now()->endOfYear()->format('Y-m-d');
        $this->name = "Campaña {$this->year}";
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (now()->year + 5),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        // Validar que no exista otra campaña del mismo año
        $existingCampaign = Campaign::forViticulturist($user->id)
            ->forYear($this->year)
            ->first();

        if ($existingCampaign) {
            $this->addError('year', __('Ya existe una campaña para el año :year.', ['year' => $this->year]));
            return;
        }

        try {
            DB::transaction(function () use ($user) {
                // Obtener relación con bodega si es viticultor invitado
                $wineryViticulturistId = null;
                if ($user->isViticulturist()) {
                    $wineryRelation = WineryViticulturist::where('viticulturist_id', $user->id)
                        ->whereNotNull('winery_id')
                        ->first();
                    $wineryViticulturistId = $wineryRelation?->id;
                }

                $campaign = Campaign::create([
                    'name' => $this->name,
                    'year' => $this->year,
                    'viticulturist_id' => $user->id,
                    'winery_viticulturist_id' => $wineryViticulturistId,  // ← Vincular a bodega
                    'start_date' => $this->start_date ?: null,
                    'end_date' => $this->end_date ?: null,
                    'description' => $this->description,
                    'active' => false, // Se activará después si es necesario
                ]);

                // Si se marca como activa, activarla
                if ($this->active) {
                    $campaign->activate();
                }
            });

            if ($this->active) {
                session()->flash('campaign_activated', __('Campaña :year activada. Ya puedes registrar actividades.', ['year' => $this->year]));
                $route = $user->isProducer() ? route('producer.digital-notebook.estimated-yields.index') : route('viticulturist.digital-notebook');
                return $this->redirect($route, navigate: true);
            }

            $this->toastSuccess(__('Campaña creada correctamente. Actívala cuando quieras empezar a registrar actividades.'));
            $route = $user->isProducer() ? route('producer.campaign.index') : route('viticulturist.campaign.index');
            return $this->redirect($route, navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error al crear campaña', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'year' => $this->year,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al crear la campaña. Por favor, intenta de nuevo.'));
            return;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.campaign.create')
            ->layout('layouts.app');
    }
}
