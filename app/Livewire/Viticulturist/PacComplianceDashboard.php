<?php

namespace App\Livewire\Viticulturist;

use App\Models\AgriculturalActivity;
use App\Services\Validators\PacComplianceValidator;
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
    }
    
    public function render()
    {
        return view('livewire.viticulturist.pac-compliance-dashboard')
            ->layout('layouts.app');
    }
}
