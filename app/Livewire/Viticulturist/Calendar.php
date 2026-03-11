<?php

namespace App\Livewire\Viticulturist;

use App\Models\Plot;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\FieldApplicator;
use App\Models\FieldEquipment;
use App\Models\CommercialAuthorization;
use App\Models\ResidueAnalysis;
use App\Models\ResidueManagement;
use App\Models\EnergyUsage;
use App\Models\PacDeclaration;
use App\Models\PacPayment;
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
    public $selectedEvents = null;
    public $showActivityModal = false;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'activityType'     => ['except' => ''],
        'currentMonth'     => ['except' => ''],
        'currentYear'      => ['except' => ''],
    ];

    public function mount()
    {
        $user = Auth::user();

        $campaign = Campaign::getOrCreateActiveForYear($user->id);

        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.index');
        }

        $this->selectedCampaign = $campaign->id;

        $now = Carbon::now();
        $this->currentMonth = $this->currentMonth ?? $now->month;
        $this->currentYear  = $this->currentYear  ?? $now->year;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear  = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear  = $date->year;
    }

    public function goToToday()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear  = $now->year;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadActivitiesForDate($date);
    }

    public function loadActivitiesForDate($date)
    {
        $user  = Auth::user();
        $query = AgriculturalActivity::forViticulturist($user->id)
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
        $this->selectedEvents   = $this->getEventsForDate($user->id, $date);
        $this->selectedDate     = $date;
        $this->showActivityModal = true;
    }

    public function getFormattedSelectedDate()
    {
        if (!$this->selectedDate) return '';
        return Carbon::parse($this->selectedDate)->format('d/m/Y');
    }

    public function closeModal()
    {
        $this->showActivityModal = false;
        $this->selectedActivity  = null;
        $this->selectedEvents    = null;
        $this->selectedDate      = null;
    }

    // ─────────────────────────────────────────────
    // Actividades del cuaderno de campo (existente)
    // ─────────────────────────────────────────────

    public function getActivitiesForMonth()
    {
        $user      = Auth::user();
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $query = AgriculturalActivity::forViticulturist($user->id)
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->with(['plot', 'phytosanitaryTreatment.product', 'fertilization',
                    'irrigation', 'culturalWork', 'observation']);

        if ($this->selectedCampaign) {
            $query->forCampaign($this->selectedCampaign);
        }
        if ($this->activityType) {
            $query->ofType($this->activityType);
        }

        return $query->get()->groupBy(fn($a) => Carbon::parse($a->activity_date)->format('Y-m-d'));
    }

    // ─────────────────────────────────────────────
    // Eventos/alertas de otros módulos
    // ─────────────────────────────────────────────

    /**
     * Eventos no-actividad para el mes visible, agrupados por fecha Y-m-d.
     * Cada evento: ['type', 'label', 'description', 'urgency' => normal|warning|danger]
     */
    public function getEventsForPeriod(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $user   = Auth::user();
        $userId = $user->id;
        $events = collect();
        $today  = Carbon::today();

        // ── Vencimiento ROPO (aplicadores) ──────────────────────────
        try {
            FieldApplicator::forViticulturist($userId)
                ->whereNotNull('ropo_expiry_date')
                ->whereBetween('ropo_expiry_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $expires = Carbon::parse($item->ropo_expiry_date);
                    $events->push([
                        'date'        => $expires->format('Y-m-d'),
                        'type'        => 'alert_ropo',
                        'label'       => 'ROPO vence',
                        'description' => $item->name ?? '',
                        'urgency'     => $expires->diffInDays($today, false) >= 0 ? 'danger' : ($expires->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Próxima inspección ITB/ITEA ─────────────────────────────
        try {
            FieldEquipment::forViticulturist($userId)
                ->whereNotNull('next_inspection_date')
                ->whereBetween('next_inspection_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $due = Carbon::parse($item->next_inspection_date);
                    $events->push([
                        'date'        => $due->format('Y-m-d'),
                        'type'        => 'alert_itb',
                        'label'       => 'Inspección ITB',
                        'description' => $item->name ?? '',
                        'urgency'     => $due->diffInDays($today, false) >= 0 ? 'danger' : ($due->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Vencimiento autorizaciones comerciales ───────────────────
        try {
            CommercialAuthorization::forViticulturist($userId)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $expires = Carbon::parse($item->expiry_date);
                    $events->push([
                        'date'        => $expires->format('Y-m-d'),
                        'type'        => 'alert_authorization',
                        'label'       => 'Autorización vence',
                        'description' => $item->name ?? $item->authorization_code ?? '',
                        'urgency'     => $expires->diffInDays($today, false) >= 0 ? 'danger' : ($expires->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Análisis de residuos ────────────────────────────────────
        try {
            ResidueAnalysis::forViticulturist($userId)
                ->whereBetween('analysis_date', [$start, $end])
                ->with('plot')
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date'        => Carbon::parse($item->analysis_date)->format('Y-m-d'),
                        'type'        => 'residue_analysis',
                        'label'       => 'Análisis residuos',
                        'description' => $item->plot->name ?? '',
                        'urgency'     => 'normal',
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Gestión de residuos ─────────────────────────────────────
        try {
            ResidueManagement::forViticulturist($userId)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date'        => Carbon::parse($item->date)->format('Y-m-d'),
                        'type'        => 'residue_management',
                        'label'       => 'Gestión residuos',
                        'description' => $item->residue_type ?? '',
                        'urgency'     => 'normal',
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Consumo energético ──────────────────────────────────────
        try {
            EnergyUsage::forViticulturist($userId)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date'        => Carbon::parse($item->date)->format('Y-m-d'),
                        'type'        => 'energy',
                        'label'       => 'Consumo energético',
                        'description' => $item->energy_type ?? '',
                        'urgency'     => 'normal',
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Declaraciones PAC ───────────────────────────────────────
        try {
            PacDeclaration::forViticulturist($userId)
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date'        => Carbon::parse($item->submitted_at)->format('Y-m-d'),
                        'type'        => 'pac_declaration',
                        'label'       => 'Declaración PAC',
                        'description' => $item->campaign_year ?? '',
                        'urgency'     => 'normal',
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Pagos PAC ───────────────────────────────────────────────
        try {
            PacPayment::forViticulturist($userId)
                ->whereNotNull('payment_date')
                ->whereBetween('payment_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date'        => Carbon::parse($item->payment_date)->format('Y-m-d'),
                        'type'        => 'pac_payment',
                        'label'       => 'Pago PAC',
                        'description' => $item->concept ?? '',
                        'urgency'     => 'normal',
                    ]);
                });
        } catch (\Exception $e) {}

        // ── Hitos de campaña ────────────────────────────────────────
        if ($this->selectedCampaign) {
            try {
                $campaign = Campaign::find($this->selectedCampaign);
                if ($campaign) {
                    foreach ([
                        'mid_validation_date'   => 'Validación intermedia',
                        'final_validation_date' => 'Validación final',
                    ] as $field => $label) {
                        if ($campaign->$field) {
                            $date = Carbon::parse($campaign->$field);
                            if ($date->between($start, $end)) {
                                $events->push([
                                    'date'        => $date->format('Y-m-d'),
                                    'type'        => 'campaign_milestone',
                                    'label'       => $label,
                                    'description' => $campaign->name,
                                    'urgency'     => 'normal',
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {}
        }

        return $events->groupBy('date');
    }

    /**
     * Eventos para un día concreto (modal).
     */
    public function getEventsForDate(int $userId, string $date): \Illuminate\Support\Collection
    {
        $day = Carbon::parse($date);
        return $this->getEventsForPeriod($day->copy()->startOfDay(), $day->copy()->endOfDay())
                    ->get($date, collect());
    }

    // ─────────────────────────────────────────────
    // Construcción del grid
    // ─────────────────────────────────────────────

    public function getCalendarDays()
    {
        $startDate       = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate         = $startDate->copy()->endOfMonth();
        $startOfCalendar = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar   = $endDate->copy()->endOfWeek(Carbon::SUNDAY);

        $activities = $this->getActivitiesForMonth();
        $events     = $this->getEventsForPeriod($startOfCalendar, $endOfCalendar);

        $days       = [];
        $currentDay = $startOfCalendar->copy();

        while ($currentDay <= $endOfCalendar) {
            $dateKey        = $currentDay->format('Y-m-d');
            $dayActivities  = $activities->get($dateKey, collect());
            $dayEvents      = $events->get($dateKey, collect());

            $days[] = [
                'date'           => $currentDay->copy(),
                'dateKey'        => $dateKey,
                'day'            => $currentDay->day,
                'isCurrentMonth' => $currentDay->month == $this->currentMonth,
                'isToday'        => $currentDay->isToday(),
                'activities'     => $dayActivities,
                'activityCount'  => $dayActivities->count(),
                'events'         => $dayEvents,
                'eventCount'     => $dayEvents->count(),
                'hasAlerts'      => $dayEvents->whereIn('type', ['alert_ropo', 'alert_itb', 'alert_authorization'])->isNotEmpty(),
            ];

            $currentDay->addDay();
        }

        return $days;
    }

    // ─────────────────────────────────────────────
    // Helpers de color y etiqueta
    // ─────────────────────────────────────────────

    public function getActivityTypeColor($type)
    {
        return match($type) {
            'phytosanitary' => 'bg-red-100 text-red-700 border-red-300',
            'fertilization' => 'bg-blue-100 text-blue-700 border-blue-300',
            'irrigation'    => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'cultural'      => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            'observation'   => 'bg-zinc-100 text-zinc-700 border-zinc-300',
            default         => 'bg-zinc-100 text-zinc-700 border-zinc-300',
        };
    }

    public function getActivityTypeLabel($type)
    {
        return match($type) {
            'phytosanitary' => 'Tratamiento',
            'fertilization' => 'Fertilización',
            'irrigation'    => 'Riego',
            'cultural'      => 'Labor',
            'observation'   => 'Observación',
            default         => 'Actividad',
        };
    }

    public function getEventColor($type, $urgency = 'normal')
    {
        if (in_array($type, ['alert_ropo', 'alert_itb', 'alert_authorization'])) {
            return match($urgency) {
                'danger'  => 'bg-red-100 text-red-800 border-red-400',
                'warning' => 'bg-orange-100 text-orange-800 border-orange-400',
                default   => 'bg-orange-50 text-orange-700 border-orange-300',
            };
        }

        return match($type) {
            'residue_analysis'    => 'bg-purple-100 text-purple-700 border-purple-300',
            'residue_management'  => 'bg-slate-100 text-slate-700 border-slate-300',
            'energy'              => 'bg-amber-100 text-amber-700 border-amber-300',
            'pac_declaration'     => 'bg-amber-100 text-amber-800 border-amber-400',
            'pac_payment'         => 'bg-teal-100 text-teal-700 border-teal-300',
            'campaign_milestone'  => 'bg-green-100 text-green-800 border-green-400',
            default               => 'bg-zinc-100 text-zinc-700 border-zinc-300',
        };
    }

    public function getEventLabel($type)
    {
        return match($type) {
            'alert_ropo'          => 'ROPO vence',
            'alert_itb'           => 'Inspección ITB',
            'alert_authorization' => 'Autorización vence',
            'residue_analysis'    => 'Análisis residuos',
            'residue_management'  => 'Gestión residuos',
            'energy'              => 'Consumo energético',
            'pac_declaration'     => 'Declaración PAC',
            'pac_payment'         => 'Pago PAC',
            'campaign_milestone'  => 'Hito campaña',
            default               => 'Evento',
        };
    }

    #[Layout('layouts.app', [
        'title'       => 'Calendario de Actividades - Agro365',
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
        $calendarDays    = $this->getCalendarDays();

        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $baseQuery = AgriculturalActivity::forViticulturist($user->id)
            ->whereBetween('activity_date', [$monthStart, $monthEnd]);

        if ($this->selectedCampaign) {
            $baseQuery->forCampaign($this->selectedCampaign);
        }

        $stats = [
            'total'         => (clone $baseQuery)->count(),
            'phytosanitary' => (clone $baseQuery)->ofType('phytosanitary')->count(),
            'fertilization' => (clone $baseQuery)->ofType('fertilization')->count(),
            'irrigation'    => (clone $baseQuery)->ofType('irrigation')->count(),
            'cultural'      => (clone $baseQuery)->ofType('cultural')->count(),
            'observation'   => (clone $baseQuery)->ofType('observation')->count(),
        ];

        $monthName = ucfirst(Carbon::create($this->currentYear, $this->currentMonth, 1)->locale('es')->monthName);

        return view('livewire.viticulturist.calendar', [
            'plots'           => $plots,
            'campaigns'       => $campaigns,
            'currentCampaign' => $currentCampaign,
            'calendarDays'    => $calendarDays,
            'stats'           => $stats,
            'monthName'       => $monthName,
        ]);
    }
}
