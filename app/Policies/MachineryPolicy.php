<?php

namespace App\Policies;

use App\Models\Machinery;
use App\Models\User;

class MachineryPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier maquinaria.
     */
    public function viewAny(User $user): bool
    {
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede ver la maquinaria.
     */
    public function view(User $user, Machinery $machinery): bool
    {
        // Solo el viticultor propietario puede verla
        return $machinery->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede crear maquinaria.
     */
    public function create(User $user): bool
    {
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede actualizar la maquinaria.
     */
    public function update(User $user, Machinery $machinery): bool
    {
        // Solo el viticultor propietario puede actualizarla
        return $machinery->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede eliminar la maquinaria.
     */
    public function delete(User $user, Machinery $machinery): bool
    {
        // Solo el viticultor propietario puede eliminarla, y solo si no tiene actividades
        if ($machinery->viticulturist_id !== $user->id) {
            return false;
        }

        // No permitir eliminar si tiene actividades asociadas
        return !$machinery->activities()->exists();
    }
}
