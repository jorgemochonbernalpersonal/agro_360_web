<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\Fertilization;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Livewire\Concerns\WithViticulturistValidation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateFertilization extends Component
{
    use WithViticulturistValidation;
    public $plot_id = '';
    public $activity_date = '';
    public $fertilizer_type = '';
    public $fertilizer_name = '';
    public $quantity = '';
    public $npk_ratio = '';
    public $application_method = '';
    public $area_applied = '';
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
            'fertilizer_type' => 'nullable|string|max:100',
            'fertilizer_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
            'npk_ratio' => 'nullable|string|max:50',
            'application_method' => 'nullable|string|max:50',
            'area_applied' => 'nullable|numeric|min:0',
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
                    'activity_type' => 'fertilization',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->crew_id ?: null,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Crear la fertilización
                Fertilization::create([
                    'activity_id' => $activity->id,
                    'fertilizer_type' => $this->fertilizer_type,
                    'fertilizer_name' => $this->fertilizer_name,
                    'quantity' => $this->quantity ?: null,
                    'npk_ratio' => $this->npk_ratio,
                    'application_method' => $this->application_method,
                    'area_applied' => $this->area_applied ?: null,
                ]);
            });

            session()->flash('message', 'Fertilización registrada correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar fertilización', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al registrar la fertilización. Por favor, intenta de nuevo.');
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

        return view('livewire.viticulturist.digital-notebook.create-fertilization', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
        ])->layout('layouts.app');
    }
}

