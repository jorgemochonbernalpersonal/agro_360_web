<?php

namespace App\Livewire\Viticulturist;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Calendar extends Component
{
    use WithToastNotifications;
    public $selectedCampaign = null;
    public $activityType = null;
    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $selectedActivity = null;
    public $showActivityModal = false;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'activityType' => ['except' => ''],
        'currentMonth' => ['except' => ''],
        'currentYear' => ['except' => ''],
    ];

    public function mount()
    {
        $user = Auth::user();
        
        // Obtener o crear campaña activa del año actual
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        
        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.index');
        }
        
        $this->selectedCampaign = $campaign->id;
        
        // Inicializar mes y año actual
        $now = Carbon::now();
        $this->currentMonth = $this->currentMonth ?? $now->month;
        $this->currentYear = $this->currentYear ?? $now->year;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadActivitiesForDate($date);
    }

    public function loadActivitiesForDate($date)
    {
        $user = Auth::user();
        $query = AgriculturalActivity::forViticulturist($user->id)
            ->whereDate('activity_date', $date)
            ->with(['plot', 'crew', 'phytosanitaryTreatment.product', 'fertilization', 'irrigation', 'culturalWork', 'observation', 'machinery']);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }

        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        $this->selectedActivity = $query->get();
        $this->selectedDate = $date;
        $this->showActivityModal = true;
    }

    public function getFormattedSelectedDate()
    {
        if (!$this->selectedDate) {
            return '';
        }
        return Carbon::parse($this->selectedDate)->format('d/m/Y');
    }

    public function closeModal()
    {
        $this->showActivityModal = false;
        $this->selectedActivity = null;
        $this->selectedDate = null;
    }

    public function getActivitiesForMonth()
    {
        $user = Auth::user();
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = AgriculturalActivity::forViticulturist($user->id)
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->with(['plot', 'phytosanitaryTreatment.product', 'fertilization', 'irrigation', 'culturalWork', 'observation']);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }

        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        return $query->get()->groupBy(function ($activity) {
            return Carbon::parse($activity->activity_date)->format('Y-m-d');
        });
    }

    public function getCalendarDays()
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $startOfCalendar = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endDate->copy()->endOfWeek(Carbon::SUNDAY);

        $activities = $this->getActivitiesForMonth();
        $days = [];
        $currentDay = $startOfCalendar->copy();

        while ($currentDay <= $endOfCalendar) {
            $dateKey = $currentDay->format('Y-m-d');
            $isCurrentMonth = $currentDay->month == $this->currentMonth;
            $isToday = $currentDay->isToday();
            
            $dayActivities = $activities->get($dateKey, collect());
            
            $days[] = [
                'date' => $currentDay->copy(),
                'dateKey' => $dateKey,
                'day' => $currentDay->day,
                'isCurrentMonth' => $isCurrentMonth,
                'isToday' => $isToday,
                'activities' => $dayActivities,
                'activityCount' => $dayActivities->count(),
            ];

            $currentDay->addDay();
        }

        return $days;
    }

    public function getActivityTypeColor($type)
    {
        return match($type) {
            'phytosanitary' => 'bg-red-100 text-red-700 border-red-300',
            'fertilization' => 'bg-blue-100 text-blue-700 border-blue-300',
            'irrigation' => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'cultural' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            'observation' => 'bg-gray-100 text-gray-700 border-gray-300',
            default => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }

    public function getActivityTypeLabel($type)
    {
        return match($type) {
            'phytosanitary' => 'Tratamiento',
            'fertilization' => 'Fertilización',
            'irrigation' => 'Riego',
            'cultural' => 'Labor',
            'observation' => 'Observación',
            default => 'Actividad',
        };
    }

    #[Layout('layouts.app', [
        'title' => 'Calendario de Actividades - Agro365',
        'description' => 'Visualiza todas tus actividades agrícolas en un calendario interactivo. Planifica tratamientos, riegos y labores culturales por fecha.',
    ])]
    public function render()
    {
        $user = Auth::user();
        
        // Obtener parcelas del viticultor
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener todas las campañas del viticultor
        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Campaña seleccionada
        $currentCampaign = Campaign::find($this->selectedCampaign);

        // Obtener días del calendario
        $calendarDays = $this->getCalendarDays();
        
        // Obtener estadísticas del mes
        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        $baseQuery = AgriculturalActivity::forViticulturist($user->id)
            ->whereBetween('activity_date', [$monthStart, $monthEnd]);
        
        if ($this->selectedCampaign) {
            $baseQuery->forCampaign($this->selectedCampaign);
        }
        
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'phytosanitary' => (clone $baseQuery)->ofType('phytosanitary')->count(),
            'fertilization' => (clone $baseQuery)->ofType('fertilization')->count(),
            'irrigation' => (clone $baseQuery)->ofType('irrigation')->count(),
            'cultural' => (clone $baseQuery)->ofType('cultural')->count(),
            'observation' => (clone $baseQuery)->ofType('observation')->count(),
        ];

        $monthName = Carbon::create($this->currentYear, $this->currentMonth, 1)->locale('es')->monthName;
        $monthName = ucfirst($monthName);

        return view('livewire.viticulturist.calendar', [
            'plots' => $plots,
            'campaigns' => $campaigns,
            'currentCampaign' => $currentCampaign,
            'calendarDays' => $calendarDays,
            'stats' => $stats,
            'monthName' => $monthName,
        ]);
    }
}

