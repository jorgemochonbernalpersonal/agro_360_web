<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Oenologist;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use App\Models\WinerySupply;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;

/**
 * Unified ownership validation rules for all roles.
 *
 * ── Viticulturist / Producer ─────────────────────────────────────────────────
 *   Resources owned via viticulturist_id = Auth::id()
 *   Producer shares this column since they ARE both roles in one user.
 *
 * ── Winery / Producer ────────────────────────────────────────────────────────
 *   Resources owned via user_id = Auth::id()
 *   Producer shares this column too.
 *
 * ── Winery cross-boundary ────────────────────────────────────────────────────
 *   Viticulturist resources accessed by winery via WineryViticulturist link.
 */
trait WithOwnershipRules
{
    // ── VITICULTURIST / PRODUCER ──────────────────────────────────────────────

    public function campaignOwnershipRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Campaign::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail(__('La campaña seleccionada no es válida.'));
                }
            },
        ];
    }

    public function plotOwnershipRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Plot::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail(__('La parcela seleccionada no es válida.'));
                }
            },
        ];
    }

    public function plotPlantingOwnershipRule(bool $required = false): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! PlotPlanting::whereHas('plot', fn ($q) => $q->where('viticulturist_id', $userId))
                    ->where('id', $value)->exists()) {
                    $fail(__('La plantación seleccionada no es válida.'));
                }
            },
        ];
    }

    public function crewOwnershipRule(): array
    {
        $userId = Auth::id();

        return [
            'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! \App\Models\Crew::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail(__('El equipo seleccionado no es válido.'));
                }
            },
        ];
    }

    public function machineryOwnershipRule(): array
    {
        $userId = Auth::id();

        return [
            'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! \App\Models\Machinery::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail(__('La maquinaria seleccionada no es válida.'));
                }
            },
        ];
    }

    // ── WINERY / PRODUCER ─────────────────────────────────────────────────────

    public function ownedWineRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Wine::where('id', $value)->where('user_id', $userId)->exists()) {
                    $fail(__('El vino seleccionado no es válido.'));
                }
            },
        ];
    }

    public function ownedContainerRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Container::where('id', $value)->where('user_id', $userId)->exists()) {
                    $fail(__('El contenedor seleccionado no es válido.'));
                }
            },
        ];
    }

    public function ownedOenologistRule(): array
    {
        $userId = Auth::id();

        return [
            'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Oenologist::where('id', $value)->where('user_id', $userId)->exists()) {
                    $fail(__('El enólogo seleccionado no es válido.'));
                }
            },
        ];
    }

    public function ownedWinerySupplyRule(bool $required = false): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! WinerySupply::where('id', $value)->where('user_id', $userId)->exists()) {
                    $fail(__('El insumo seleccionado no es válido.'));
                }
            },
        ];
    }

    public function ownedHarvestRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! Harvest::where('id', $value)->where('winery_id', $userId)->exists()) {
                    $fail(__('La vendimia seleccionada no es válida.'));
                }
            },
        ];
    }

    public function ownedWineProcessDetailRule(bool $required = true): array
    {
        $userId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && ! WineProcessDetail::where('id', $value)
                    ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
                    ->exists()) {
                    $fail(__('El detalle de proceso seleccionado no es válido.'));
                }
            },
        ];
    }

    // ── WINERY → VITICULTORES VINCULADOS ─────────────────────────────────────

    /**
     * Validates that the selected viticulturist is linked to the authenticated winery.
     * Also accepts producers linked via parent_viticulturist_id.
     */
    public function linkedViticulturistRule(): array
    {
        $wineryId = Auth::id();

        return [
            'required',
            function ($attribute, $value, $fail) use ($wineryId) {
                if ($value && ! WineryViticulturist::where('winery_id', $wineryId)
                    ->where('viticulturist_id', $value)
                    ->exists()) {
                    $fail(__('El viticultor seleccionado no está vinculado a tu bodega.'));
                }
            },
        ];
    }

    /**
     * Validates that the selected plot belongs to a viticulturist linked to this winery.
     */
    public function linkedPlotRule(bool $required = true): array
    {
        $wineryId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($wineryId) {
                if (! $value) {
                    return;
                }
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! Plot::where('id', $value)->whereIn('viticulturist_id', $viticulturistIds)->exists()) {
                    $fail(__('La parcela seleccionada no pertenece a un viticultor vinculado.'));
                }
            },
        ];
    }

    /**
     * Validates that the selected plot_planting belongs to a viticulturist linked to this winery.
     */
    public function linkedPlotPlantingRule(bool $required = false): array
    {
        $wineryId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($wineryId) {
                if (! $value) {
                    return;
                }
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! PlotPlanting::where('id', $value)
                    ->whereHas('plot', fn ($q) => $q->whereIn('viticulturist_id', $viticulturistIds))
                    ->exists()) {
                    $fail(__('La plantación seleccionada no pertenece a un viticultor vinculado.'));
                }
            },
        ];
    }

    /**
     * Validates that the selected campaign belongs to a viticulturist linked to this winery.
     */
    public function linkedCampaignRule(bool $required = true): array
    {
        $wineryId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($wineryId) {
                if (! $value) {
                    return;
                }
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! Campaign::where('id', $value)->whereIn('viticulturist_id', $viticulturistIds)->exists()) {
                    $fail(__('La campaña seleccionada no pertenece a un viticultor vinculado.'));
                }
            },
        ];
    }

    // ── SUPERVISOR ────────────────────────────────────────────────────────────

    /**
     * Validates that the selected winery is linked to the authenticated supervisor (DO).
     */
    public function supervisorLinkedWineryRule(bool $required = true): array
    {
        $supervisorId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($supervisorId) {
                if ($value && ! SupervisorWinery::where('supervisor_id', $supervisorId)
                    ->where('winery_id', $value)->exists()) {
                    $fail(__('La bodega seleccionada no pertenece a esta denominación.'));
                }
            },
        ];
    }

    /**
     * Validates that the selected viticulturist is in the supervisor's (DO) pool.
     */
    public function supervisorLinkedViticulturistRule(bool $required = true): array
    {
        $supervisorId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($supervisorId) {
                if ($value && ! SupervisorViticulturist::where('supervisor_id', $supervisorId)
                    ->where('viticulturist_id', $value)->exists()) {
                    $fail(__('El viticultor seleccionado no pertenece a esta denominación.'));
                }
            },
        ];
    }

    /**
     * Validates a polymorphic subject (winery or viticulturist) against the supervisor's pool.
     * Pass the subject_type ('winery'|'viticulturist') at call time.
     */
    public function supervisorLinkedSubjectRule(string $subjectType, bool $required = true): array
    {
        $supervisorId = Auth::id();

        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($supervisorId, $subjectType) {
                if (! $value) {
                    return;
                }
                $exists = $subjectType === 'winery'
                    ? SupervisorWinery::where('supervisor_id', $supervisorId)->where('winery_id', $value)->exists()
                    : SupervisorViticulturist::where('supervisor_id', $supervisorId)->where('viticulturist_id', $value)->exists();
                if (! $exists) {
                    $fail(__('El sujeto seleccionado no pertenece a esta denominación.'));
                }
            },
        ];
    }
}
