<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use App\Models\HarvestContainer;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateHarvest extends Component
{
    use WithViticulturistValidation, WithToastNotifications;

    // Básico
    public $plot_id = '';
    public $plot_planting_id = '';
    public $container_id = ''; // Contenedor obligatorio
    public $activity_date = '';
    public $harvest_start_date = '';
    public $harvest_end_date = '';
    
    // Cantidad
    public $total_weight = '';
    public $yield_per_hectare = '';
    
    // Calidad (opcional)
    public $baume_degree = '';
    public $brix_degree = '';
    public $acidity_level = '';
    public $ph_level = '';
    
    // Evaluación organoléptica (opcional)
    public $color_rating = '';
    public $aroma_rating = '';
    public $health_status = '';
    
    // Destino
    public $destination_type = '';
    public $destination = '';
    public $buyer_name = '';
    
    // Económico (opcional)
    public $price_per_kg = '';
    public $total_value = '';
    
    // Común a todas las actividades
    public $workType = '';
    public $crew_id = '';
    public $crew_member_id = '';
    public $machinery_id = '';
    public $weather_conditions = '';
    public $temperature = '';
    public $notes = '';
    public $campaign_id = '';

    // Plantaciones disponibles (dinámico)
    public $availablePlantings = [];
    
    // Contenedores disponibles
    public $availableContainers = [];
    
    // Alertas de plazos de seguridad
    public $hasActiveWithdrawal = false;
    public $activeWithdrawalTreatments = [];
    public $withdrawalAcknowledged = false;
    public $withdrawalReason = '';

    // Panel de control de límite y rendimiento
    public $selectedPlanting = null;
    public $estimatedYield = null;
    public $totalHarvestedInCampaign = 0;
    public $harvestLimitInfo = null;
    public $yieldVarianceInfo = null;

    public function mount()
    {
        $this->authorizeCreateActivity();
        
        $this->activity_date = now()->format('Y-m-d');
        $this->harvest_start_date = now()->format('Y-m-d');
        
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        
        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa.');
            return redirect()->route('viticulturist.campaign.create');
        }
        
        $this->campaign_id = $campaign->id;
        
        // Cargar contenedores disponibles
        $this->loadAvailableContainers();
    }
    
    /**
     * Cargar contenedores disponibles (sin cosecha asignada)
     */
    protected function loadAvailableContainers()
    {
        $this->availableContainers = HarvestContainer::whereNull('harvest_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Cuando se selecciona un contenedor, actualizar el peso automáticamente
     */
    public function updatedContainerId($value)
    {
        if ($value) {
            $container = HarvestContainer::find($value);
            if ($container && $container->isAvailable()) {
                // Actualizar el peso con el peso del contenedor
                $this->total_weight = $container->weight;
                $this->calculateYield();
                $this->calculateTotalValue();
                $this->updateControlPanelData();
            }
        }
    }

    /**
     * Cuando cambia la parcela, actualizar plantaciones disponibles
     */
    public function updatedPlotId($value)
    {
        if (!$value) {
            $this->availablePlantings = [];
            $this->plot_planting_id = '';
            $this->resetWithdrawalWarning();
            return;
        }

        $this->availablePlantings = PlotPlanting::where('plot_id', $value)
            ->where('status', 'active')
            ->with('grapeVariety')
            ->get();
        
        // Si solo hay una plantación, auto-seleccionarla
        if ($this->availablePlantings->count() === 1) {
            $this->plot_planting_id = $this->availablePlantings->first()->id;
            $this->loadControlPanelData();
        } else {
            $this->plot_planting_id = '';
            $this->selectedPlanting = null;
            $this->harvestLimitInfo = null;
            $this->yieldVarianceInfo = null;
        }
        
        $this->checkWithdrawalPeriods();
        $this->calculateYield();
    }

    /**
     * Verificar plazos de seguridad activos
     */
    protected function checkWithdrawalPeriods()
    {
        if (!$this->plot_id) {
            $this->resetWithdrawalWarning();
            return;
        }

        $plot = Plot::find($this->plot_id);
        if (!$plot) {
            $this->resetWithdrawalWarning();
            return;
        }

        $activeWithdrawals = $plot->activeWithdrawalPeriods();
        
        if ($activeWithdrawals->count() > 0) {
            $this->hasActiveWithdrawal = true;
            $this->activeWithdrawalTreatments = $activeWithdrawals->map(function($activity) {
                $treatment = $activity->phytosanitaryTreatment;
                $product = $treatment->product;
                $withdrawalDays = $product->withdrawal_period_days;
                $safeDate = $activity->activity_date->copy()->addDays($withdrawalDays);
                
                return [
                    'product_name' => $product->name,
                    'application_date' => $activity->activity_date->format('d/m/Y'),
                    'withdrawal_days' => $withdrawalDays,
                    'safe_date' => $safeDate->format('d/m/Y'),
                    'days_remaining' => now()->diffInDays($safeDate, false),
                ];
            })->toArray();
        } else {
            $this->resetWithdrawalWarning();
        }
    }

    protected function resetWithdrawalWarning()
    {
        $this->hasActiveWithdrawal = false;
        $this->activeWithdrawalTreatments = [];
        $this->withdrawalAcknowledged = false;
        $this->withdrawalReason = '';
    }


    public function updatedPlotPlantingId()
    {
        $this->calculateYield();
        $this->loadControlPanelData();
    }

    public function updatedTotalWeight()
    {
        $this->calculateYield();
        $this->calculateTotalValue();
        $this->updateControlPanelData();
    }

    public function updatedPricePerKg()
    {
        $this->calculateTotalValue();
    }

    protected function calculateYield()
    {
        if (!$this->total_weight || !$this->plot_planting_id) {
            $this->yield_per_hectare = '';
            return;
        }

        $planting = PlotPlanting::find($this->plot_planting_id);
        if ($planting && $planting->area_planted > 0) {
            $this->yield_per_hectare = round($this->total_weight / $planting->area_planted, 3);
        }
    }

    protected function calculateTotalValue()
    {
        if (!$this->total_weight || !$this->price_per_kg) {
            $this->total_value = '';
            return;
        }

        $this->total_value = round($this->total_weight * $this->price_per_kg, 3);
    }

    /**
     * Cargar datos del panel de control cuando se selecciona una plantación
     */
    protected function loadControlPanelData()
    {
        if (!$this->plot_planting_id || !$this->campaign_id) {
            $this->selectedPlanting = null;
            $this->estimatedYield = null;
            $this->harvestLimitInfo = null;
            $this->yieldVarianceInfo = null;
            $this->totalHarvestedInCampaign = 0;
            return;
        }

        $this->selectedPlanting = PlotPlanting::with(['grapeVariety', 'plot'])->find($this->plot_planting_id);
        if (!$this->selectedPlanting) {
            return;
        }

        // Cargar rendimiento estimado
        $this->estimatedYield = $this->selectedPlanting->getEstimatedYieldForCampaign($this->campaign_id);

        // Cargar cosechas registradas en la campaña
        $this->totalHarvestedInCampaign = $this->selectedPlanting->getTotalActualYieldForCampaign($this->campaign_id);

        // Cargar información del límite
        if ($this->selectedPlanting->hasHarvestLimit()) {
            $this->harvestLimitInfo = [
                'limit' => $this->selectedPlanting->harvest_limit_kg,
                'harvested' => $this->totalHarvestedInCampaign,
                'remaining' => $this->selectedPlanting->getRemainingHarvestLimitForCampaign($this->campaign_id),
                'percentage' => $this->selectedPlanting->getHarvestLimitUsagePercentageForCampaign($this->campaign_id),
            ];
        } else {
            $this->harvestLimitInfo = null;
        }

        // Cargar varianza de rendimiento
        $this->updateControlPanelData();
    }

    /**
     * Actualizar datos del panel de control cuando cambia el peso
     */
    protected function updateControlPanelData()
    {
        if (!$this->selectedPlanting || !$this->campaign_id) {
            return;
        }

        $newWeight = (float) ($this->total_weight ?: 0);
        
        // Actualizar información del límite con el nuevo peso
        if ($this->harvestLimitInfo) {
            $newTotal = $this->totalHarvestedInCampaign + $newWeight;
            $this->harvestLimitInfo['new_total'] = $newTotal;
            $this->harvestLimitInfo['new_remaining'] = max(0, round($this->harvestLimitInfo['limit'] - $newTotal, 3));
            $this->harvestLimitInfo['new_percentage'] = $this->harvestLimitInfo['limit'] > 0 
                ? round(($newTotal / $this->harvestLimitInfo['limit']) * 100, 3) 
                : null;
            $this->harvestLimitInfo['exceeds'] = $newTotal > $this->harvestLimitInfo['limit'];
        }

        // Actualizar varianza de rendimiento
        if ($this->estimatedYield) {
            $this->yieldVarianceInfo = $this->selectedPlanting->getYieldVariance($this->campaign_id, $newWeight);
        } else {
            $this->yieldVarianceInfo = null;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => 'required|exists:plots,id',
            'plot_planting_id' => 'required|exists:plot_plantings,id',
            'container_id' => 'required|exists:harvest_containers,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'activity_date' => 'required|date',
            'harvest_start_date' => 'required|date',
            'harvest_end_date' => 'nullable|date|after_or_equal:harvest_start_date',
            
            'total_weight' => 'required|numeric|min:0.01',
            'yield_per_hectare' => 'nullable|numeric|min:0',
            
            'baume_degree' => 'nullable|numeric|min:0|max:20',
            'brix_degree' => 'nullable|numeric|min:0|max:40',
            'acidity_level' => 'nullable|numeric|min:0|max:20',
            'ph_level' => 'nullable|numeric|min:0|max:14',
            
            'color_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'aroma_rating' => 'nullable|in:excelente,bueno,aceptable,deficiente',
            'health_status' => 'nullable|in:sano,daño_leve,daño_moderado,daño_grave',
            
            'destination_type' => 'nullable|in:winery,direct_sale,cooperative,self_consumption,other',
            'destination' => 'nullable|string|max:255',
            'buyer_name' => 'nullable|string|max:255',
            
            'price_per_kg' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            
            'crew_id' => 'nullable|exists:crews,id',
            'crew_member_id' => 'nullable|exists:crew_members,id',
            'machinery_id' => 'nullable|exists:machinery,id',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];

        // Si hay plazo de seguridad activo, validar confirmación
        if ($this->hasActiveWithdrawal) {
            $rules['withdrawalAcknowledged'] = 'required|accepted';
            $rules['withdrawalReason'] = 'required|string|min:20';
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        // Validar workType
        if (!$this->workType) {
            $this->addError('workType', 'Debes seleccionar quién realizó el trabajo.');
            return;
        }

        if ($this->workType === 'crew' && !$this->crew_id) {
            $this->addError('crew_id', 'Debes seleccionar un equipo.');
            return;
        }

        if ($this->workType === 'individual' && !$this->crew_member_id) {
            $this->addError('crew_member_id', 'Debes seleccionar un viticultor.');
            return;
        }

        // Validar que la parcela pertenece al viticultor
        $plot = $this->authorizeCreateActivityForPlot($this->plot_id);
        
        // Validar que el contenedor existe y está disponible
        $container = HarvestContainer::find($this->container_id);
        if (!$container) {
            $this->addError('container_id', 'El contenedor seleccionado no existe.');
            return;
        }
        
        if (!$container->isAvailable()) {
            $this->addError('container_id', 'El contenedor seleccionado ya está asignado a otra cosecha.');
            return;
        }

        try {
            DB::transaction(function () use ($user, $container) {
                $crewMemberId = null;
                
                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        [
                            'viticulturist_id' => $this->crew_member_id,
                            'assigned_by' => $user->id,
                        ],
                        ['crew_id' => null]
                    );
                    
                    $crewMemberId = $crewMember->id;
                }
                
                // Preparar notas con advertencia de plazo de seguridad si aplica
                $notes = $this->notes;
                if ($this->hasActiveWithdrawal && $this->withdrawalAcknowledged) {
                    $warningNote = "\n\n⚠️ COSECHA CON PLAZO DE SEGURIDAD ACTIVO\n";
                    $warningNote .= "Motivo: " . $this->withdrawalReason . "\n";
                    $warningNote .= "Tratamientos activos:\n";
                    foreach ($this->activeWithdrawalTreatments as $treatment) {
                        $warningNote .= "- {$treatment['product_name']} (aplicado el {$treatment['application_date']}, seguro desde {$treatment['safe_date']})\n";
                    }
                    $notes = $warningNote . ($notes ? "\n" . $notes : '');
                }
                
                // Crear actividad base
                $activity = AgriculturalActivity::create([
                    'plot_id' => $this->plot_id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $this->campaign_id,
                    'activity_type' => 'harvest',
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $notes,
                ]);

                // Crear cosecha
                $harvest = Harvest::create([
                    'activity_id' => $activity->id,
                    'plot_planting_id' => $this->plot_planting_id,
                    'container_id' => $this->container_id,
                    'harvest_start_date' => $this->harvest_start_date,
                    'harvest_end_date' => $this->harvest_end_date ?: null,
                    'total_weight' => $this->total_weight,
                    'yield_per_hectare' => $this->yield_per_hectare,
                    'baume_degree' => $this->baume_degree ?: null,
                    'brix_degree' => $this->brix_degree ?: null,
                    'acidity_level' => $this->acidity_level ?: null,
                    'ph_level' => $this->ph_level ?: null,
                    'color_rating' => $this->color_rating ?: null,
                    'aroma_rating' => $this->aroma_rating ?: null,
                    'health_status' => $this->health_status ?: null,
                    'destination_type' => $this->destination_type ?: null,
                    'destination' => $this->destination,
                    'buyer_name' => $this->buyer_name,
                    'price_per_kg' => $this->price_per_kg ?: null,
                    'total_value' => $this->total_value ?: null,
                    'status' => 'active',
                    'notes' => $this->notes,
                ]);
                
                // Asignar el contenedor a la cosecha
                $container->update(['harvest_id' => $harvest->id]);
            });

            $this->toastSuccess('Cosecha registrada correctamente.');
            return redirect()->route('viticulturist.digital-notebook');
        } catch (\Exception $e) {
            \Log::error('Error al registrar cosecha', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plot_id' => $this->plot_id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al registrar la cosecha. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        // Solo parcelas con plantaciones activas
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->whereHas('plantings', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('name')
            ->get();

        $crews = Crew::where('viticulturist_id', $user->id)
            ->orderBy('name')
            ->get();

        $machinery = Machinery::forViticulturist($user->id)
            ->active()
            ->orderBy('name')
            ->get();

        $allViticulturists = \App\Models\WineryViticulturist::editableBy($user)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->unique('id')
            ->sortBy('name')
            ->values();

        $campaign = Campaign::find($this->campaign_id);

        return view('livewire.viticulturist.digital-notebook.create-harvest', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app');
    }
}
