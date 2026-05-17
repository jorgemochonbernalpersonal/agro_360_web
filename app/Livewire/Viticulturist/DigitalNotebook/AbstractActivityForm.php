<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Machinery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Base class for all digital-notebook Create and Edit forms.
 *
 * Provides:
 *  - Common Livewire properties shared by every activity form
 *  - mountCreate() / mountEditGuards() / loadActivityFields() helpers
 *  - updatedPlotId() reactive handler
 *  - commonRules() – validation rules that every activity type shares
 *  - resolveCrewMemberId() – find-or-create the CrewMember row
 *  - activityData() – build the AgriculturalActivity create/update array
 *  - renderData() – assemble the common view data bag
 */
abstract class AbstractActivityForm extends Component
{
    use WithViticulturistValidation, WithToastNotifications, WithUserFilters, WithRoleAwareRedirect;

    // ─── Properties shared by every activity form ─────────────────────────────

    public $plot_id            = '';
    public $plot_planting_id   = '';
    public $availablePlantings = [];
    public $activity_date      = '';
    public $phenological_stage = '';
    public $workType           = '';   // 'crew' | 'individual'
    public $crew_id            = '';
    public $crew_member_id     = '';
    public $machinery_id       = '';
    public $weather_conditions = '';
    public $temperature        = '';
    public $notes              = '';
    public $campaign_id        = '';

    // ─── Create helpers ───────────────────────────────────────────────────────

    protected function mountCreate(): void
    {
        $this->authorizeCreateActivity();
        $this->activity_date = now()->format('Y-m-d');

        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            $this->viticulturistRoleRedirect('campaign.create');
            return;
        }
        $this->campaign_id = $campaign->id;
    }

    // ─── Edit helpers ─────────────────────────────────────────────────────────

    /**
     * Run the three standard edit guards (type check, policy, lock).
     * Returns true when the caller may proceed, false when it should abort.
     */
    protected function mountEditGuards(
        AgriculturalActivity $activity,
        string $expectedType,
        string $indexRoute
    ): bool {
        if ($activity->activity_type !== $expectedType) {
            $this->toastError('Esta actividad no es del tipo esperado.');
            $this->viticulturistRoleRedirect($indexRoute);
            return false;
        }
        if (!Auth::user()->can('update', $activity)) {
            abort(403);
        }
        if ($activity->isLocked()) {
            $this->toastError(
                'No se puede editar una actividad bloqueada (PAC, >'
                . config('activities.lock_days', 7) . ' días).'
            );
            $this->viticulturistRoleRedirect($indexRoute);
            return false;
        }
        return true;
    }

    /**
     * Populate all common properties from an existing AgriculturalActivity.
     * Call after mountEditGuards() returns true.
     */
    protected function loadActivityFields(AgriculturalActivity $activity): void
    {
        $this->plot_id            = $activity->plot_id;
        $this->plot_planting_id   = $activity->plot_planting_id ?? '';
        $this->activity_date      = Carbon::parse($activity->activity_date)->format('Y-m-d');
        $this->phenological_stage = $activity->phenological_stage ?? '';
        $this->campaign_id        = $activity->campaign_id;
        $this->weather_conditions = $activity->weather_conditions ?? '';
        $this->temperature        = $activity->temperature ?? '';
        $this->notes              = $activity->notes ?? '';
        $this->machinery_id       = $activity->machinery_id ?? '';

        if ($activity->crew_id) {
            $this->workType = 'crew';
            $this->crew_id  = $activity->crew_id;
        } elseif ($activity->crew_member_id) {
            $this->workType       = 'individual';
            $this->crew_member_id = $activity->crewMember?->viticulturist_id ?? '';
        }
    }

    /**
     * Populate availablePlantings for the currently set plot_id.
     */
    protected function loadAvailablePlantings(): void
    {
        if ($this->plot_id) {
            $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get();
        }
    }

    // ─── Reactive handlers ────────────────────────────────────────────────────

    public function updatedPlotId($value): void
    {
        $this->plot_planting_id = '';
        $this->availablePlantings = $value
            ? PlotPlanting::where('plot_id', $value)
                ->where('status', 'active')
                ->with('grapeVariety')
                ->orderBy('name')
                ->get()
            : [];
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    /**
     * Validation rules shared by every activity form.
     * Subclasses merge their type-specific rules on top.
     */
    protected function commonRules(): array
    {
        return [
            'plot_id' => $this->plotOwnershipRule(),
            'plot_planting_id' => [
                'nullable',
                'exists:plot_plantings,id',
                function ($attribute, $value, $fail) {
                    if ($this->plot_id) {
                        $plot = Plot::find($this->plot_id);
                        if ($plot && $plot->plantings()->where('status', 'active')->exists()) {
                            if (!$value) {
                                $fail('Debes seleccionar una plantación para esta parcela.');
                            } elseif (!PlotPlanting::where('id', $value)->where('plot_id', $this->plot_id)->exists()) {
                                $fail('La plantación seleccionada no pertenece a esta parcela.');
                            }
                        }
                    }
                },
            ],
            'campaign_id'        => $this->campaignOwnershipRule(),
            'activity_date'      => 'required|date',
            'phenological_stage' => 'required|string|max:50',
            'workType'           => 'required|in:crew,individual',
            'crew_id'            => 'required_if:workType,crew|nullable|exists:crews,id',
            'crew_member_id'     => 'required_if:workType,individual|nullable|exists:users,id',
            'machinery_id'       => $this->machineryOwnershipRule(),
            'weather_conditions' => 'nullable|string|max:255',
            'temperature'        => 'nullable|numeric',
            'notes'              => 'nullable|string',
        ];
    }

    // ─── Save helpers ─────────────────────────────────────────────────────────

    /**
     * Find-or-create the CrewMember row for individual work.
     */
    protected function resolveCrewMemberId(): ?int
    {
        if ($this->workType !== 'individual' || !$this->crew_member_id) {
            return null;
        }
        return CrewMember::firstOrCreate(
            ['viticulturist_id' => $this->crew_member_id, 'assigned_by' => Auth::id()],
            ['crew_id' => null]
        )->id;
    }

    /**
     * Build the array for AgriculturalActivity::create() / $activity->update().
     * Pass $extra to merge any activity-type–specific columns (e.g. winery_viticulturist_id).
     */
    protected function activityData(string $type, array $extra = []): array
    {
        return array_merge([
            'plot_id'            => $this->plot_id,
            'plot_planting_id'   => $this->plot_planting_id ?: null,
            'viticulturist_id'   => Auth::id(),
            'campaign_id'        => $this->campaign_id,
            'activity_type'      => $type,
            'phenological_stage' => $this->phenological_stage,
            'activity_date'      => $this->activity_date,
            'crew_id'            => $this->workType === 'crew' ? $this->crew_id : null,
            'crew_member_id'     => $this->resolveCrewMemberId(),
            'machinery_id'       => $this->machinery_id ?: null,
            'weather_conditions' => $this->weather_conditions,
            'temperature'        => $this->temperature ?: null,
            'notes'              => $this->notes,
        ], $extra);
    }

    // ─── Computed properties (memoized per request) ───────────────────────────

    #[Computed]
    public function plots()
    {
        return Plot::forUser(Auth::user())->where('active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function crews()
    {
        return Crew::where('viticulturist_id', Auth::id())->orderBy('name')->get();
    }

    #[Computed]
    public function machinery()
    {
        return Machinery::forViticulturist(Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function individualWorkers()
    {
        return CrewMember::whereNull('crew_id')
            ->where('assigned_by', Auth::id())
            ->join('users', 'users.id', '=', 'crew_members.viticulturist_id')
            ->orderBy('users.name')
            ->select('crew_members.*')
            ->with('viticulturist')
            ->get();
    }

    #[Computed]
    public function campaign()
    {
        return Campaign::find($this->campaign_id);
    }

    // ─── Render helpers ───────────────────────────────────────────────────────

    /**
     * Assemble the common view-data bag.
     * Pass $extra to merge component-specific variables (products, applicators, etc.).
     */
    protected function renderData(array $extra = []): array
    {
        return array_merge([
            'plots'             => $this->plots,
            'crews'             => $this->crews,
            'machinery'         => $this->machinery,
            'campaign'          => $this->campaign,
            'allViticulturists' => $this->viticulturists,
            'individualWorkers' => $this->individualWorkers,
        ], $extra);
    }
}
