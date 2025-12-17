<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Livewire\Concerns\WithViticulturistValidation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePhytosanitaryTreatment extends Component
{
    use WithViticulturistValidation;
    public $plot_id = '';
    public $activity_date = '';
    public $product_id = '';
    public $dose_per_hectare = '';
    public $total_dose = '';
    public $area_treated = '';
    public $application_method = '';
    public $target_pest = '';
    public $crew_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $wind_speed = '';
    public $humidity = '';
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
            'product_id' => 'required|exists:phytosanitary_products,id',
            'dose_per_hectare' => 'nullable|numeric|min:0',
            'total_dose' => 'nullable|numeric|min:0',
            'area_treated' => 'nullable|numeric|min:0',
            'application_method' => 'nullable|string|max:50',
            'target_pest' => 'nullable|string|max:255',
            'crew_id' => 'nullable|exists:crews,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'wind_speed' => 'nullable|numeric|min:0',
            'humidity' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ];
    }

    public function updatedAreaTreated()
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = $this->area_treated * $this->dose_per_hectare;
        }
    }

    public function updatedDosePerHectare()
    {
        if ($this->area_treated && $this->dose_per_hectare) {
            $this->total_dose = $this->area_treated * $this->dose_per_hectare;
        }
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
                    'activity_type' => 'phytosanitary',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->crew_id ?: null,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Crear el tratamiento fitosanitario
                PhytosanitaryTreatment::create([
                    'activity_id' => $activity->id,
                    'product_id' => $this->product_id,
                    'dose_per_hectare' => $this->dose_per_hectare ?: null,
                    'total_dose' => $this->total_dose ?: null,
                    'area_treated' => $this->area_treated ?: null,
                    'application_method' => $this->application_method,
                    'target_pest' => $this->target_pest,
                    'wind_speed' => $this->wind_speed ?: null,
                    'humidity' => $this->humidity ?: null,
                ]);
            });

            session()->flash('message', 'Tratamiento fitosanitario registrado correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar tratamiento fitosanitario', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al registrar el tratamiento fitosanitario. Por favor, intenta de nuevo.');
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

        $products = PhytosanitaryProduct::orderBy('name')->get();

        $crews = Crew::where('viticulturist_id', $user->id)
            ->orderBy('name')
            ->get();

        $machinery = Machinery::forViticulturist($user->id)
            ->active()
            ->orderBy('name')
            ->get();

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.create-phytosanitary-treatment', [
            'plots' => $plots,
            'products' => $products,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
        ])->layout('layouts.app');
    }
}

