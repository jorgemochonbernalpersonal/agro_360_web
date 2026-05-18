<?php

namespace App\Livewire\Concerns;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait WithViticulturistValidation
{
    /**
     * Validar que una parcela pertenece al viticultor autenticado
     */
    protected function validatePlotOwnership(int $plotId): Plot
    {
        $user = Auth::user();

        return Plot::where('id', $plotId)
            ->where('viticulturist_id', $user->id)
            ->firstOrFail();
    }

    /**
     * Validar que el usuario puede crear actividades agrícolas
     */
    protected function authorizeCreateActivity(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No tienes permiso para crear actividades agrícolas.');
        }

        // Permitimos viticultores, bodegas, supervisores y admins
        if (
            ! $user->hasViticulturistAccess() &&
            ! $user->hasWineryAccess() &&
            ! $user->isSupervisor() &&
            ! $user->isAdmin()
        ) {
            abort(403, 'No tienes permiso para crear actividades agrícolas.');
        }
    }

    /**
     * Validar que el usuario puede crear actividades en una parcela específica
     */
    protected function authorizeCreateActivityForPlot(int $plotId): Plot
    {
        $this->authorizeCreateActivity();

        $plot = Plot::findOrFail($plotId);

        // Usar la misma lógica que para editar la parcela:
        // si el usuario puede actualizar la parcela, puede crear actividades sobre ella.
        if (!Auth::user()->can('update', $plot)) {
            abort(403, 'No tienes permiso para crear actividades en esta parcela.');
        }

        return $plot;
    }

    // -------------------------------------------------------------------------
    // Reglas de validación con comprobación de pertenencia (ownership)
    // Evitan que un viticultor use IDs de campañas, cuadrillas o maquinaria
    // que no le pertenecen, sin necesitar import adicional en cada componente.
    // -------------------------------------------------------------------------

    public function plotOwnershipRule(bool $required = true): array
    {
        $userId = Auth::id();
        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && !\App\Models\Plot::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
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
                if ($value && !\App\Models\PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $userId))
                    ->where('id', $value)->exists()) {
                    $fail('La plantación seleccionada no es válida.');
                }
            },
        ];
    }

    public function campaignOwnershipRule(bool $required = true): array
    {
        $userId = Auth::id();
        return [
            $required ? 'required' : 'nullable',
            function ($attribute, $value, $fail) use ($userId) {
                if ($value && !\App\Models\Campaign::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail('La campaña seleccionada no es válida.');
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
                if ($value && !\App\Models\Crew::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
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
                if ($value && !\App\Models\Machinery::where('id', $value)->where('viticulturist_id', $userId)->exists()) {
                    $fail('La maquinaria seleccionada no es válida.');
                }
            },
        ];
    }

    /**
     * Validates that declared PAC areas don't exceed the real plot areas.
     * Re-queries DB to prevent tampering with Livewire state.
     * Returns true if all areas are valid, false if errors were added.
     */
    protected function validatePacDeclaredAreas(array $selectedIds, array $items): bool
    {
        $plots = Plot::where('viticulturist_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->get()
            ->keyBy('id');

        $hasErrors = false;

        foreach ($selectedIds as $plotId) {
            $plot = $plots->get($plotId);
            if (!$plot) {
                continue;
            }

            $plotArea     = (float) ($plot->area ?? 0);
            $declaredArea = (float) ($items[$plotId]['declared_area'] ?? 0);
            $eligibleArea = (float) ($items[$plotId]['eligible_area'] ?? 0);

            if ($plotArea > 0 && $declaredArea > $plotArea) {
                $this->addError(
                    "items.{$plotId}.declared_area",
                    "La superficie declarada ({$declaredArea} ha) supera la superficie total de la parcela \"{$plot->name}\" ({$plotArea} ha)."
                );
                $hasErrors = true;
            }

            if ($eligibleArea > $declaredArea) {
                $this->addError(
                    "items.{$plotId}.eligible_area",
                    "La superficie admisible ({$eligibleArea} ha) no puede superar la superficie declarada ({$declaredArea} ha) en la parcela \"{$plot->name}\"."
                );
                $hasErrors = true;
            }
        }

        return !$hasErrors;
    }
}

