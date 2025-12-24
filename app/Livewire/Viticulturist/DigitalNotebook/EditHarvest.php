<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use App\Models\Container;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\CrewMember;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditHarvest extends Component
{
    use WithViticulturistValidation, WithToastNotifications;

    public $harvest;
    public $harvest_id;

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
    
    // Para detectar cambios de contenedor
    public $original_container_id = null;
    
    // Alertas de plazos de seguridad
    public $hasActiveWithdrawal = false;
    public $activeWithdrawalTreatments = [];
    public $withdrawalAcknowledged = false;
    public $withdrawalReason = '';

    // Notas de edición
    public $edit_notes = '';

    // Panel de control de límite y rendimiento
    public $selectedPlanting = null;
    public $estimatedYield = null;
    public $totalHarvestedInCampaign = 0;
    public $harvestLimitInfo = null;
    public $yieldVarianceInfo = null;

    public function mount($harvest)
    {
        $this->harvest_id = $harvest;
        $this->loadHarvest();
    }

    public function loadHarvest()
    {
        $user = Auth::user();

        $this->harvest = Harvest::whereHas('activity', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['activity.plot', 'activity.campaign', 'plotPlanting.grapeVariety'])
        ->findOrFail($this->harvest_id);

        $activity = $this->harvest->activity;

        // Cargar datos de la actividad
        $this->plot_id = $activity->plot_id;
        $this->plot_planting_id = $this->harvest->plot_planting_id;
        $this->activity_date = $activity->activity_date->format('Y-m-d');
        $this->campaign_id = $activity->campaign_id;
        $this->crew_id = $activity->crew_id ?? '';
        $this->crew_member_id = $activity->crew_member_id ?? '';
        $this->machinery_id = $activity->machinery_id ?? '';
        $this->weather_conditions = $activity->weather_conditions ?? '';
        $this->temperature = $activity->temperature ?? '';
        $this->notes = $activity->notes ?? '';

        // Determinar workType
        if ($activity->crew_id) {
            $this->workType = 'crew';
        } elseif ($activity->crew_member_id) {
            $this->workType = 'individual';
        }

        // Cargar datos de la cosecha
        $this->container_id = $this->harvest->container_id ?? '';
        $this->original_container_id = $this->harvest->container_id;
        $this->harvest_start_date = $this->harvest->harvest_start_date->format('Y-m-d');
        $this->harvest_end_date = $this->harvest->harvest_end_date ? $this->harvest->harvest_end_date->format('Y-m-d') : '';
        $this->total_weight = $this->harvest->total_weight;
        $this->yield_per_hectare = $this->harvest->yield_per_hectare;
        
        // Cargar contenedores disponibles
        $this->loadAvailableContainers();
        $this->baume_degree = $this->harvest->baume_degree ?? '';
        $this->brix_degree = $this->harvest->brix_degree ?? '';
        $this->acidity_level = $this->harvest->acidity_level ?? '';
        $this->ph_level = $this->harvest->ph_level ?? '';
        $this->color_rating = $this->harvest->color_rating ?? '';
        $this->aroma_rating = $this->harvest->aroma_rating ?? '';
        $this->health_status = $this->harvest->health_status ?? '';
        $this->destination_type = $this->harvest->destination_type ?? '';
        $this->destination = $this->harvest->destination ?? '';
        $this->buyer_name = $this->harvest->buyer_name ?? '';
        $this->price_per_kg = $this->harvest->price_per_kg ?? '';
        $this->total_value = $this->harvest->total_value ?? '';

        // Cargar plantaciones disponibles
        $this->updatedPlotId($this->plot_id);
        
        // Cargar datos del panel de control
        $this->loadControlPanelData();
    }
    
    /**
     * Cargar contenedores disponibles (sin cosecha asignada + el actual si existe)
     */
    protected function loadAvailableContainers()
    {
        $query = Container::available()->whereDoesntHave('harvests');
        
        // Incluir el contenedor actual si existe
        if ($this->original_container_id) {
            $query->orWhere('id', $this->original_container_id);
        }
        
        $this->availableContainers = $query->orderBy('created_at', 'desc')->get();
    }
    
    /**
     * Cuando se cambia de contenedor, recalcular peso y valores
     */
    public function updatedContainerId($value)
    {
        if ($value && $value != $this->original_container_id) {
            $container = Container::find($value);
            if ($container && $container->hasAvailableCapacity($this->total_weight ?? 0)) {
                // No actualizamos el peso automáticamente, el usuario lo define
                // Solo validamos que el contenedor tenga capacidad disponible
                $this->calculateYieldPerHectare();
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

    /**
     * Calcular rendimiento cuando cambia el peso o plantación
     */
    public function updatedTotalWeight()
    {
        $this->calculateYield();
        $this->calculateTotalValue();
        $this->updateControlPanelData();
    }

    public function updatedPlotPlantingId()
    {
        $this->calculateYield();
        $this->loadControlPanelData();
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

        // Cargar cosechas registradas en la campaña (excluyendo la actual si estamos editando)
        $this->totalHarvestedInCampaign = $this->selectedPlanting->getTotalActualYieldForCampaign($this->campaign_id);
        if ($this->harvest && $this->harvest->id) {
            // Restar el peso de la cosecha actual si estamos editando
            $this->totalHarvestedInCampaign = max(0, $this->totalHarvestedInCampaign - $this->harvest->total_weight);
        }

        // Cargar información del límite
        if ($this->selectedPlanting->hasHarvestLimit()) {
            // Calcular remaining y percentage usando totalHarvestedInCampaign (que ya excluye la cosecha actual)
            $remaining = max(0, round($this->selectedPlanting->harvest_limit_kg - $this->totalHarvestedInCampaign, 3));
            $percentage = $this->selectedPlanting->harvest_limit_kg > 0
                ? round(($this->totalHarvestedInCampaign / $this->selectedPlanting->harvest_limit_kg) * 100, 3)
                : null;

            $this->harvestLimitInfo = [
                'limit' => $this->selectedPlanting->harvest_limit_kg,
                'harvested' => $this->totalHarvestedInCampaign,
                'remaining' => $remaining,
                'percentage' => $percentage,
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
            'edit_notes' => 'nullable|string|min:10',
        ];

        // Si hay plazo de seguridad activo, validar confirmación
        if ($this->hasActiveWithdrawal) {
            $rules['withdrawalAcknowledged'] = 'required|accepted';
            $rules['withdrawalReason'] = 'required|string|min:20';
        }

        return $rules;
    }

    public function update()
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
        
        // Validar que el contenedor existe y está disponible (o es el actual)
        $container = Container::find($this->container_id);
        if (!$container) {
            $this->addError('container_id', 'El contenedor seleccionado no existe.');
            return;
        }
        
        // Si se cambió de contenedor, verificar que el nuevo esté disponible
        if ($this->container_id != $this->original_container_id && !$container->isAvailable()) {
            $this->addError('container_id', 'El contenedor seleccionado ya está asignado a otra cosecha.');
            return;
        }

        try {
            DB::transaction(function () use ($user, $container) {
                $crewMemberId = $this->harvest->activity->crew_member_id;
                
                if ($this->workType === 'individual' && $this->crew_member_id) {
                    $crewMember = CrewMember::firstOrCreate(
                        [
                            'viticulturist_id' => $this->crew_member_id,
                            'assigned_by' => $user->id,
                        ],
                        ['crew_id' => null]
                    );
                    
                    $crewMemberId = $crewMember->id;
                } elseif ($this->workType === 'crew') {
                    $crewMemberId = null;
                }
                
                // Actualizar actividad base
                $this->harvest->activity->update([
                    'plot_id' => $this->plot_id,
                    'campaign_id' => $this->campaign_id,
                    'activity_date' => $this->activity_date,
                    'crew_id' => $this->workType === 'crew' ? $this->crew_id : null,
                    'crew_member_id' => $crewMemberId,
                    'machinery_id' => $this->machinery_id ?: null,
                    'weather_conditions' => $this->weather_conditions,
                    'temperature' => $this->temperature ?: null,
                    'notes' => $this->notes,
                ]);

                // Manejar cambio de contenedor
                if ($this->container_id != $this->original_container_id) {
                    // Desvincular el contenedor anterior
                    if ($this->original_container_id) {
                        Container::where('id', $this->original_container_id)
                            ->update(['harvest_id' => null]);
                    }
                    
                    // Asignar el nuevo contenedor a esta cosecha
                    $container->update(['harvest_id' => $this->harvest->id]);
                }
                
                // Actualizar cosecha
                $this->harvest->update([
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
                    'edited_at' => now(),
                    'edited_by' => $user->id,
                    'edit_notes' => $this->edit_notes ?: null,
                ]);
            });

            $this->toastSuccess('Cosecha actualizada correctamente.');
            return redirect()->route('viticulturist.digital-notebook.harvest.show', $this->harvest->id);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar cosecha', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'harvest_id' => $this->harvest->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar la cosecha. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        // Recargar contenedores disponibles
        $this->loadAvailableContainers();
        
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

        return view('livewire.viticulturist.digital-notebook.edit-harvest', [
            'plots' => $plots,
            'crews' => $crews,
            'machinery' => $machinery,
            'campaign' => $campaign,
            'allViticulturists' => $allViticulturists,
        ])->layout('layouts.app');
    }
}

