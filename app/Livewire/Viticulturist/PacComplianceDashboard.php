<?php

namespace App\Livewire\Viticulturist;

use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryProduct;
use App\Models\Harvest;
use App\Services\Validators\PacComplianceValidator;
use App\Services\Validators\WithdrawalPeriodValidator;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PacComplianceDashboard extends Component
{
    public $timeRange = '30'; // Días a mostrar (30, 90, 180, 365, 'all')
    public $compliancePercentage = 0;
    public $totalActivities = 0;
    public $activitiesWithErrors = 0;
    public $activitiesWithWarnings = 0;
    public $criticalErrors = [];
    public $warnings = [];
    public $statsByType = [];
    
    // Nuevas métricas de productos
    public $totalProducts = 0;
    public $productsWithValidRegistration = 0;
    public $productsExpiringSoon = 0;
    public $productsExpiredOrRevoked = 0;
    public $productRegistrationPercentage = 0;
    public $expiringProducts = [];
    
    // Nuevas métricas de actividades
    public $totalLockedActivities = 0;
    public $lockedActivitiesPercentage = 0;
    public $recentlyLockedActivities = [];
    
    // Métricas de cosechas
    public $totalHarvests = 0;
    public $harvestsWithWithdrawalIssues = 0;
    public $harvestsWithWarnings = 0;
    
    public function mount()
    {
        $this->calculateCompliance();
    }
    
    public function updatedTimeRange()
    {
        $this->calculateCompliance();
    }
    
    public function calculateCompliance()
    {
        $user = Auth::user();
        $validator = new PacComplianceValidator();
        
        // Obtener actividades según el rango de tiempo
        $query = AgriculturalActivity::forUser($user->id)
            ->with(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product', 
                    'irrigation', 'fertilization', 'harvest', 'culturalWork', 'observation']);
        
        if ($this->timeRange !== 'all') {
            $query->where('activity_date', '>=', now()->subDays((int)$this->timeRange));
        }
        
        $activities = $query->get();
        
        // Validar actividades
        $validation = $validator->validateActivities($activities);
        
        // Calcular métricas globales
        $this->totalActivities = $activities->count();
        $this->compliancePercentage = $validator->getCompliancePercentage($validation);
        $this->activitiesWithErrors = count($validation['errors']);
        $this->activitiesWithWarnings = count($validation['warnings']);
        
        // Obtener errores críticos (máximo 10)
        $this->criticalErrors = array_slice($validation['errors'], 0, 10);
        
        // Obtener warnings (máximo 10)
        $this->warnings = array_slice($validation['warnings'], 0, 10);
        
        // Obtener estadísticas por tipo de actividad
        $this->statsByType = $validator->getComplianceStats($activities);
        
        // Calcular métricas de productos fitosanitarios
        $this->calculateProductMetrics();
        
        // Calcular métricas de actividades bloqueadas
        $this->calculateLockedActivitiesMetrics($activities);
        
        // Calcular métricas de cosechas
        $this->calculateHarvestMetrics($activities);
    }
    
    protected function calculateProductMetrics()
    {
        $products = PhytosanitaryProduct::all();
        $this->totalProducts = $products->count();
        
        // Productos con registro válido
        $validProducts = $products->filter(function($product) {
            return $product->isRegistrationValid();
        });
        $this->productsWithValidRegistration = $validProducts->count();
        
        // Productos próximos a caducar (30 días)
        $expiringDate = now()->addDays(30);
        $this->expiringProducts = $products->filter(function($product) use ($expiringDate) {
            return $product->registration_expiry_date 
                && $product->registration_expiry_date <= $expiringDate
                && $product->registration_expiry_date > now()
                && $product->registration_status === 'active';
        })->take(5)->values();
        
        $this->productsExpiringSoon = $this->expiringProducts->count();
        
        // Productos caducados o revocados
        $this->productsExpiredOrRevoked = $products->filter(function($product) {
            return !$product->isRegistrationValid();
        })->count();
        
        // Porcentaje de productos con registro válido
        $this->productRegistrationPercentage = $this->totalProducts > 0 
            ? round(($this->productsWithValidRegistration / $this->totalProducts) * 100, 1)
            : 0;
    }
    
    protected function calculateLockedActivitiesMetrics($activities)
    {
        // Total de actividades bloqueadas
        $this->totalLockedActivities = AgriculturalActivity::forUser(Auth::id())
            ->where('is_locked', true)
            ->count();
        
        // Porcentaje de actividades bloqueadas
        $totalAllActivities = AgriculturalActivity::forUser(Auth::id())->count();
        $this->lockedActivitiesPercentage = $totalAllActivities > 0
            ? round(($this->totalLockedActivities / $totalAllActivities) * 100, 1)
            : 0;
        
        // Actividades bloqueadas recientemente (últimos 7 días)
        $this->recentlyLockedActivities = AgriculturalActivity::forUser(Auth::id())
            ->where('is_locked', true)
            ->where('locked_at', '>=', now()->subDays(7))
            ->with(['plot'])
            ->orderBy('locked_at', 'desc')
            ->take(5)
            ->get();
    }
    
    protected function calculateHarvestMetrics($activities)
    {
        $harvests = $activities->filter(function($activity) {
            return $activity->activity_type === 'harvest' && $activity->harvest;
        });
        
        $this->totalHarvests = $harvests->count();
        
        if ($this->totalHarvests === 0) {
            return;
        }
        
        $withdrawalValidator = new WithdrawalPeriodValidator();
        $errorsCount = 0;
        $warningsCount = 0;
        
        foreach ($harvests as $activity) {
            $validation = $withdrawalValidator->validateHarvest($activity->harvest);
            
            if (!$validation['is_valid']) {
                $errorsCount++;
            }
            
            if (!empty($validation['warnings'])) {
                $warningsCount++;
            }
        }
        
        $this->harvestsWithWithdrawalIssues = $errorsCount;
        $this->harvestsWithWarnings = $warningsCount;
    }
    
    public function render()
    {
        return view('livewire.viticulturist.pac-compliance-dashboard')
            ->layout('layouts.app');
    }
}
