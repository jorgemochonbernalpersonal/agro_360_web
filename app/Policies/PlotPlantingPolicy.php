<?php

namespace App\Policies;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;

/**
 * Authorization for PlotPlanting resources.
 *
 * All access is derived from the parent Plot's permissions:
 * - If you can VIEW the plot, you can view its plantings.
 * - If you can UPDATE the plot, you can create, update, or delete its plantings.
 *
 * This means wineries cannot create/edit plantings on plots belonging to
 * supervisor-assigned viticulturists (source = 'supervisor'), because
 * PlotPolicy::canUpdatePlotAsWinery() restricts to SOURCE_OWN only.
 */
class PlotPlantingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist', 'producer']);
    }

    public function view(User $user, PlotPlanting $planting): bool
    {
        return $user->can('view', $planting->plot);
    }

    /**
     * Called as: can('create', [PlotPlanting::class, $plot])  — checks if user can update the specific plot.
     * Called as: can('create', PlotPlanting::class)           — general check (no specific plot), used in listing views.
     */
    public function create(User $user, ?Plot $plot = null): bool
    {
        if ($plot === null) {
            // General check (no specific plot): same roles that can view any planting.
            return in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist', 'producer']);
        }

        return $user->can('update', $plot);
    }

    public function update(User $user, PlotPlanting $planting): bool
    {
        return $user->can('update', $planting->plot);
    }

    public function delete(User $user, PlotPlanting $planting): bool
    {
        return $this->update($user, $planting);
    }
}
