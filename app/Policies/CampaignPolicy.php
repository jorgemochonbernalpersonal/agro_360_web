<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier campaña.
     */
    public function viewAny(User $user): bool
    {
        // Solo viticultores pueden ver campañas
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede ver la campaña.
     */
    public function view(User $user, Campaign $campaign): bool
    {
        // Solo el viticultor dueño puede ver su campaña
        return $campaign->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede crear campañas.
     */
    public function create(User $user): bool
    {
        // Solo viticultores pueden crear campañas
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede actualizar la campaña.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        // Solo el viticultor dueño puede actualizar
        return $campaign->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede eliminar la campaña.
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        // Solo el viticultor dueño puede eliminar
        return $campaign->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede activar la campaña.
     */
    public function activate(User $user, Campaign $campaign): bool
    {
        // Solo el viticultor dueño puede activar
        return $campaign->viticulturist_id === $user->id;
    }
}
