<?php

namespace App\Livewire\Viticulturist;

use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\PlotRemoteSensing;
use App\Services\DashboardAlertsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    // ── Parcelas ──────────────────────────────────────────────────────────────

    #[Computed]
    public function plots()
    {
        return Plot::forUser(Auth::user())->get();
    }

    #[Computed]
    public function plotIds()
    {
        return $this->plots->pluck('id');
    }

    #[Computed]
    public function totalPlots(): int
    {
        return $this->plots->count();
    }

    #[Computed]
    public function totalArea(): float
    {
        return (float) ($this->plots->sum('area') ?? 0);
    }

    // ── Actividades ───────────────────────────────────────────────────────────

    #[Computed]
    public function activitiesThisMonth(): int
    {
        return AgriculturalActivity::where('viticulturist_id', Auth::id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    #[Computed]
    public function totalActivities(): int
    {
        return AgriculturalActivity::where('viticulturist_id', Auth::id())->count();
    }

    #[Computed]
    public function isNewUser(): bool
    {
        return $this->totalActivities === 0;
    }

    #[Computed]
    public function recentActivities()
    {
        return AgriculturalActivity::where('viticulturist_id', Auth::id())
            ->with(['plot'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function daysSinceLastActivity(): ?int
    {
        $last = $this->recentActivities->first();
        return $last ? (int) $last->activity_date->diffInDays(now()) : null;
    }

    // ── Cosecha ───────────────────────────────────────────────────────────────

    #[Computed]
    public function totalHarvested(): float
    {
        return (float) Harvest::whereHas('activity', function ($q) {
            $q->where('viticulturist_id', Auth::id())
              ->whereYear('activity_date', now()->year);
        })->sum('total_weight');
    }

    #[Computed]
    public function recentHarvests()
    {
        return Harvest::whereHas('activity', function ($q) {
            $q->where('viticulturist_id', Auth::id());
        })
        ->with(['activity.plot', 'plotPlanting.grapeVariety'])
        ->orderBy('harvest_start_date', 'desc')
        ->take(4)
        ->get();
    }

    // ── Gráficos ──────────────────────────────────────────────────────────────

    #[Computed]
    public function plotsByVariety()
    {
        return PlotPlanting::selectRaw('grape_variety_id, COUNT(DISTINCT plot_id) as count')
            ->whereIn('plot_id', $this->plotIds)
            ->whereNotNull('grape_variety_id')
            ->where('status', 'active')
            ->groupBy('grape_variety_id')
            ->with('grapeVariety')
            ->get();
    }

    // ── Teledetección ─────────────────────────────────────────────────────────

    #[Computed]
    public function ndviData(): array
    {
        if ($this->totalPlots === 0) {
            return ['ndvi' => null, 'plotName' => null, 'hasSigpac' => false];
        }

        $hasSigpac = $this->plots->first()?->sigpacCodes()->exists() ?? false;

        $sensing = PlotRemoteSensing::whereIn('plot_id', $this->plotIds)
            ->whereNotNull('ndvi_mean')
            ->orderByDesc('image_date')
            ->with('plot')
            ->first();

        return [
            'ndvi'      => $sensing?->ndvi_mean,
            'plotName'  => $sensing?->plot->name ?? '',
            'hasSigpac' => $hasSigpac,
        ];
    }

    // ── Alertas ───────────────────────────────────────────────────────────────

    #[Computed]
    public function alerts()
    {
        return app(DashboardAlertsService::class)->getAlerts(Auth::user());
    }

    // ── Consejo estacional ────────────────────────────────────────────────────

    #[Computed]
    public function currentTip(): array
    {
        $user = Auth::user();
        $month = (int) now()->month;

        $tips = [
            1  => ['icon' => '❄️', 'title' => 'Enero — Reposo invernal',       'tip' => 'Buen momento para planificar la poda de invierno y revisar el estado de espalderas y tutores.', 'action' => 'Registrar poda',          'route' => 'viticulturist.digital-notebook.pruning.create'],
            2  => ['icon' => '✂️', 'title' => 'Febrero — Poda en seco',         'tip' => 'Época de poda principal. Registra los tipos de poda y carga de yemas por parcela.',              'action' => 'Registrar poda',          'route' => 'viticulturist.digital-notebook.pruning.create'],
            3  => ['icon' => '🌱', 'title' => 'Marzo — Lloro y brotación',      'tip' => 'Vigila la brotación y prepara tratamientos preventivos contra mildiu y oídio.',                  'action' => 'Registrar observación',  'route' => 'viticulturist.digital-notebook.observation.create'],
            4  => ['icon' => '🌿', 'title' => 'Abril — Crecimiento vegetativo', 'tip' => 'Controla el vigor y aplica tratamientos preventivos. Revisa las necesidades de riego.',          'action' => 'Registrar tratamiento',  'route' => 'viticulturist.digital-notebook.treatment.create'],
            5  => ['icon' => '🌸', 'title' => 'Mayo — Pre-floración',           'tip' => 'Momento crítico para mildiu. Mantén la vigilancia y registra tratamientos preventivos.',         'action' => 'Registrar tratamiento',  'route' => 'viticulturist.digital-notebook.treatment.create'],
            6  => ['icon' => '🌼', 'title' => 'Junio — Floración y cuajado',    'tip' => 'Registra observaciones de floración. Controla el cuajado y ajusta el riego si es necesario.',    'action' => 'Registrar observación',  'route' => 'viticulturist.digital-notebook.observation.create'],
            7  => ['icon' => '☀️', 'title' => 'Julio — Envero',                 'tip' => 'Controla el estrés hídrico y registra el inicio del envero. Prepara estimaciones de rendimiento.','action' => 'Estimar rendimiento',    'route' => 'viticulturist.digital-notebook.estimated-yields.create'],
            8  => ['icon' => '🍇', 'title' => 'Agosto — Maduración',            'tip' => 'Muestrea la madurez de la uva y planifica la vendimia. Registra análisis de azúcar y acidez.',   'action' => 'Registrar observación',  'route' => 'viticulturist.digital-notebook.observation.create'],
            9  => ['icon' => '🍷', 'title' => 'Septiembre — Vendimia',          'tip' => 'Registra cada entrega de vendimia con peso, variedad y parcela para la trazabilidad completa.',   'action' => 'Registrar vendimia',     'route' => $user->hasWinery() ? 'viticulturist.harvests.delivery.create' : 'viticulturist.digital-notebook.harvest.create'],
            10 => ['icon' => '🍂', 'title' => 'Octubre — Post-vendimia',        'tip' => 'Aplica tratamientos post-vendimia y registra labores de mantenimiento del suelo.',                'action' => 'Registrar post-vendimia','route' => 'viticulturist.digital-notebook.post-harvest.create'],
            11 => ['icon' => '🍁', 'title' => 'Noviembre — Caída de hoja',      'tip' => 'Buen momento para análisis de suelo y planificación de la fertilización invernal.',               'action' => 'Registrar fertilización','route' => 'viticulturist.digital-notebook.fertilization.create'],
            12 => ['icon' => '🌧️', 'title' => 'Diciembre — Reposo',             'tip' => 'Revisa el balance de la campaña y prepara la documentación para el próximo ciclo.',               'action' => 'Ver campañas',           'route' => 'viticulturist.campaign.index'],
        ];

        return $tips[$month];
    }

    // ── Plan de acceso ────────────────────────────────────────────────────────

    #[Computed]
    public function hasActiveAccess(): bool
    {
        return Auth::user()->hasActiveAccess();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.viticulturist.dashboard')
            ->layout('layouts.app');
    }
}
