<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\Irrigation;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Livewire\Concerns\WithViticulturistValidation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateIrrigation extends Component
{
    use WithViticulturistValidation;
    public $plot_id = '';
    public $activity_date = '';
    public $water_volume = '';
    public $irrigation_method = '';
    public $duration_minutes = '';
    public $soil_moisture_before = '';
    public $soil_moisture_after = '';
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
            'water_volume' => 'nullable|numeric|min:0',
            'irrigation_method' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:0',
            'soil_moisture_before' => 'nullable|numeric|min:0|max:100',
            'soil_moisture_after' => 'nullable|numeric|min:0|max:100',
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
                    'activity_type' => 'irrigation',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->crew_id ?: null,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Crear el riego
                Irrigation::create([
                    'activity_id' => $activity->id,
                    'water_volume' => $this->water_volume ?: null,
                    'irrigation_method' => $this->irrigation_method,
                    'duration_minutes' => $this->duration_minutes ?: null,
                    'soil_moisture_before' => $this->soil_moisture_before ?: null,
                    'soil_moisture_after' => $this->soil_moisture_after ?: null,
                ]);
            });

            session()->flash('message', 'Riego registrado correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar riego', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al registrar el riego. Por favor, intenta de nuevo.');
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

        return view('livewire.viticulturist.digital-notebook.create-irrigation', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
        ])->layout('layouts.app');
    }
}

