<?php

namespace App\Policies;

use App\Models\Crew;
use App\Models\User;

class CrewPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier cuadrilla.
     */
    public function viewAny(User $user): bool
    {
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede ver la cuadrilla.
     */
    public function view(User $user, Crew $crew): bool
    {
        // Solo el líder de la cuadrilla puede verla
        return $crew->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede crear cuadrillas.
     */
    public function create(User $user): bool
    {
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede actualizar la cuadrilla.
     */
    public function update(User $user, Crew $crew): bool
    {
        // Solo el líder puede actualizar
        return $crew->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede eliminar la cuadrilla.
     */
    public function delete(User $user, Crew $crew): bool
    {
        // Solo el líder puede eliminar, y solo si no tiene actividades
        if ($crew->viticulturist_id !== $user->id) {
            return false;
        }

        // No permitir eliminar si tiene actividades asociadas
        return !$crew->activities()->exists();
    }
}

