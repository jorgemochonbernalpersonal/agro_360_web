<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\AgriculturalActivityAuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ActivityAuditHistory extends Component
{
    use WithPagination;

    public AgriculturalActivity $activity;
    public $filterUser = '';
    public $filterAction = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    protected $queryString = [
        'filterUser' => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function mount(AgriculturalActivity $activity = null)
    {
        if (!$activity) {
            abort(404, 'Actividad no encontrada.');
        }
        
        // Verificar autorización
        if (!Auth::user()->can('view', $activity)) {
            abort(403, 'No tienes permiso para ver el historial de esta actividad.');
        }

        $this->activity = $activity;
    }

    public function render()
    {
        $query = AgriculturalActivityAuditLog::where('activity_id', $this->activity->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        $logs = $query->paginate(10);

        // Obtener usuarios únicos que han modificado esta actividad
        $users = User::whereIn('id', 
            AgriculturalActivityAuditLog::where('activity_id', $this->activity->id)
                ->distinct()
                ->pluck('user_id')
        )->get();

        // Acciones disponibles
        $actions = AgriculturalActivityAuditLog::where('activity_id', $this->activity->id)
            ->distinct()
            ->pluck('action');

        return view('livewire.viticulturist.digital-notebook.activity-audit-history', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
        ]);
    }

    public function clearFilters()
    {
        $this->filterUser = '';
        $this->filterAction = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function getChangeDiff($log)
    {
        if (empty($log->changes)) {
            return [];
        }

        $changes = $log->changes;
        $diff = [];

        if (isset($changes['old']) && isset($changes['new'])) {
            foreach ($changes['new'] as $field => $newValue) {
                $oldValue = $changes['old'][$field] ?? null;
                
                if ($oldValue != $newValue) {
                    $diff[] = [
                        'field' => $this->getFieldLabel($field),
                        'old' => $this->formatValue($oldValue),
                        'new' => $this->formatValue($newValue),
                    ];
                }
            }
        }

        return $diff;
    }

    protected function getFieldLabel($field)
    {
        $labels = [
            'activity_date' => 'Fecha de actividad',
            'notes' => 'Notas',
            'weather_conditions' => 'Condiciones meteorológicas',
            'temperature' => 'Temperatura',
            'phenological_stage' => 'Estadio fenológico',
            'crew_id' => 'Cuadrilla',
            'machinery_id' => 'Maquinaria',
            'is_locked' => 'Estado de bloqueo',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    protected function formatValue($value)
    {
        if ($value === null) {
            return '<span class="text-gray-400 italic">vacío</span>';
        }

        if (is_bool($value)) {
            return $value ? '✅ Sí' : '❌ No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return $value;
    }
}
