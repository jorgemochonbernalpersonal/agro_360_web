<?php

namespace App\Livewire\Concerns;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated Use WithOwnershipRules directly.
 *
 * Kept as a compatibility shim. Components that extend Shared\AbstractCreate or
 * Shared\AbstractEdit already inherit WithOwnershipRules automatically.
 * Direct Component subclasses (Phenology, CampaignDocuments, etc.) still use this
 * trait explicitly — they get WithOwnershipRules via composition below.
 */
trait WithViticulturistValidation
{
    use WithOwnershipRules;

    /**
     * Validates that a plot belongs to the authenticated user.
     * Legacy name kept for backward compatibility with existing components.
     * @deprecated Use plotOwnershipRule() from WithOwnershipRules instead.
     */
    protected function validatePlotOwnership(int $plotId): Plot
    {
        return Plot::where('id', $plotId)
            ->where('viticulturist_id', Auth::id())
            ->firstOrFail();
    }

    /**
     * Validates that the user can create agricultural activities.
     * @deprecated Move authorization logic to policies.
     */
    protected function authorizeCreateActivity(): void
    {
        $user = Auth::user();

        if (
            ! $user?->hasViticulturistAccess() &&
            ! $user?->hasWineryAccess() &&
            ! $user?->isSupervisor() &&
            ! $user?->isAdmin()
        ) {
            abort(403, 'No tienes permiso para crear actividades agrícolas.');
        }
    }

    /**
     * @deprecated Use plotOwnershipRule() from WithOwnershipRules instead.
     */
    protected function authorizeCreateActivityForPlot(int $plotId): Plot
    {
        $this->authorizeCreateActivity();

        $plot = Plot::findOrFail($plotId);

        if (! Auth::user()->can('update', $plot)) {
            abort(403, 'No tienes permiso para crear actividades en esta parcela.');
        }

        return $plot;
    }

    /**
     * Validates PAC declared areas against real plot areas.
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
            if (! $plot) continue;

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

        return ! $hasErrors;
    }
}
