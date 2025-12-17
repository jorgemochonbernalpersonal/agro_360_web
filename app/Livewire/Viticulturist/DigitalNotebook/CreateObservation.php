<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\Observation;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Livewire\Concerns\WithViticulturistValidation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateObservation extends Component
{
    use WithViticulturistValidation;
    public $plot_id = '';
    public $activity_date = '';
    public $observation_type = '';
    public $description = '';
    public $severity = '';
    public $action_taken = '';
    public $crew_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';

    public function mount()
    {
        // Validar autorización
        $this->authorizeCreateActivity();
        
        $this->activity_date = now()->format('Y-m-d');
        
        // Obtener o crear campaña activa del año actual
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        
        if (!$campaign) {
            // Si no se pudo obtener/crear campaña, redirigir
            session()->flash('error', 'No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.create');
        }
        
        $this->campaign_id = $campaign->id;
    }

    protected function rules(): array
    {
        return [
            'plot_id' => 'required|exists:plots,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'activity_date' => 'required|date',
            'observation_type' => 'nullable|string|max:50',
            'description' => 'required|string',
            'severity' => 'nullable|string|in:leve,moderada,grave',
            'action_taken' => 'nullable|string',
            'crew_id' => 'nullable|exists:crews,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        // Validar que la parcela pertenece al viticultor usando el trait
        $plot = $this->authorizeCreateActivityForPlot($this->plot_id);

        try {
            DB::transaction(function () use ($user) {
                // Crear la actividad base
                $activity = AgriculturalActivity::create([
                    'plot_id' => $this->plot_id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $this->campaign_id,
                    'activity_type' => 'observation',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->crew_id ?: null,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Crear la observación
                Observation::create([
                    'activity_id' => $activity->id,
                    'observation_type' => $this->observation_type,
                    'description' => $this->description,
                    'severity' => $this->severity,
                    'action_taken' => $this->action_taken,
                    'photos' => null, // Para futuro: subida de fotos
                ]);
            });

            session()->flash('message', 'Observación registrada correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar observación', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al registrar la observación. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $crews = Crew::where('viticulturist_id', $user->id)
            ->orderBy('name')
            ->get();

        $machinery = Machinery::forViticulturist($user->id)
            ->active()
            ->orderBy('name')
            ->get();

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.create-observation', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
        ])->layout('layouts.app');
    }
}

