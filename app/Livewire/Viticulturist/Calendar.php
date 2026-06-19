<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithCalendarActivities;
use App\Livewire\Concerns\WithCalendarEvents;
use App\Livewire\Concerns\WithCalendarHelpers;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Calendar extends Component
{
    use WithCalendarActivities, WithCalendarEvents, WithCalendarHelpers, WithRoleAwareRedirect, WithToastNotifications;

    public $selectedCampaign = null;

    public $activityType = null;

    public $currentMonth;

    public $currentYear;

    public $selectedDate = null;

    public $selectedActivity = null;

    public $selectedEvents = null;

    public $showActivityModal = false;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'activityType' => ['except' => ''],
        'currentMonth' => ['except' => ''],
        'currentYear' => ['except' => ''],
    ];

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());

        if (! $campaign) {
            $this->toastError(__('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.'));
            $this->viticulturistRoleRedirect('campaign.index');

            return;
        }

        $this->selectedCampaign = $campaign->id;

        $now = Carbon::now();
        $this->currentMonth = $this->currentMonth ?? $now->month;
        $this->currentYear = $this->currentYear ?? $now->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday(): void
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function selectDate($date): void
    {
        $this->selectedDate = $date;
        $this->loadActivitiesForDate($date);
    }

    public function loadActivitiesForDate($date): void
    {
        $query = AgriculturalActivity::forViticulturist(Auth::id())
            ->whereDate('activity_date', $date)
            ->with(['plot', 'crew', 'phytosanitaryTreatment.product', 'fertilization',
                'irrigation', 'culturalWork', 'observation', 'machinery']);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }
        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        $this->selectedActivity = $query->get();
        $this->selectedEvents = $this->getEventsForDate(Auth::id(), $date);
        $this->selectedDate = $date;
        $this->showActivityModal = true;
    }

    public function getFormattedSelectedDate(): string
    {
        if (! $this->selectedDate) {
            return '';
        }

        return Carbon::parse($this->selectedDate)->format('d/m/Y');
    }

    public function closeModal(): void
    {
        $this->showActivityModal = false;
        $this->selectedActivity = null;
        $this->selectedEvents = null;
        $this->selectedDate = null;
    }

    #[Layout('layouts.app', [
        'title' => 'Calendario de Actividades - Agro365',
        'description' => 'Visualiza todas tus actividades agrícolas en un calendario interactivo. Planifica tratamientos, riegos y labores culturales por fecha.',
    ])]
    public function render()
    {
        $user = Auth::user();

        $plots = Plot::forUser($user)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        $currentCampaign = Campaign::find($this->selectedCampaign);
        $calendarDays = $this->getCalendarDays();

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
            'pruning' => (clone $baseQuery)->ofType('pruning')->count(),
            'harvest' => (clone $baseQuery)->ofType('harvest')->count(),
            'post_harvest' => (clone $baseQuery)->ofType('post_harvest')->count(),
        ];

        $monthName = ucfirst(Carbon::create($this->currentYear, $this->currentMonth, 1)->locale('es')->monthName);

        return view('livewire.viticulturist.calendar', [
            'plots' => $plots,
            'campaigns' => $campaigns,
            'currentCampaign' => $currentCampaign,
            'calendarDays' => $calendarDays,
            'stats' => $stats,
            'monthName' => $monthName,
            'upcomingActivities' => $this->getUpcomingActivities(),
            'recentActivities' => $this->getRecentActivities(),
            'routePrefix' => Auth::user()->role === 'producer' ? 'producer' : 'viticulturist',
        ]);
    }
}
