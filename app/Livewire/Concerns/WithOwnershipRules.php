<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Oenologist;
use App\Models\Plot;
use App\Models\PlotPlanting;
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
                    $fail('La campaña seleccionada no es válida.');
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
                    $fail('La parcela seleccionada no es válida.');
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
                    $fail('La plantación seleccionada no es válida.');
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
                    $fail('El equipo seleccionado no es válido.');
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
                    $fail('La maquinaria seleccionada no es válida.');
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
                    $fail('El vino seleccionado no es válido.');
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
                    $fail('El contenedor seleccionado no es válido.');
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
                    $fail('El enólogo seleccionado no es válido.');
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
                    $fail('El insumo seleccionado no es válido.');
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
                    $fail('La vendimia seleccionada no es válida.');
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
                    $fail('El detalle de proceso seleccionado no es válido.');
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
                    $fail('El viticultor seleccionado no está vinculado a tu bodega.');
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
                if (! $value) return;
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! Plot::where('id', $value)->whereIn('viticulturist_id', $viticulturistIds)->exists()) {
                    $fail('La parcela seleccionada no pertenece a un viticultor vinculado.');
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
                if (! $value) return;
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! PlotPlanting::where('id', $value)
                    ->whereHas('plot', fn ($q) => $q->whereIn('viticulturist_id', $viticulturistIds))
                    ->exists()) {
                    $fail('La plantación seleccionada no pertenece a un viticultor vinculado.');
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
                if (! $value) return;
                $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
                    ->pluck('viticulturist_id');
                if (! Campaign::where('id', $value)->whereIn('viticulturist_id', $viticulturistIds)->exists()) {
                    $fail('La campaña seleccionada no pertenece a un viticultor vinculado.');
                }
            },
        ];
    }
}
