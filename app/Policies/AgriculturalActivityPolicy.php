<?php

namespace App\Policies;

use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\User;

class AgriculturalActivityPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier actividad.
     */
    public function viewAny(User $user): bool
    {
        // Solo viticultores pueden ver actividades
        return $user->isViticulturist();
    }

    /**
     * Determinar si el usuario puede ver la actividad.
     */
    public function view(User $user, AgriculturalActivity $activity): bool
    {
        // Solo el viticultor dueño puede ver su actividad
        return $activity->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede crear actividades.
     * 
     * @param Plot|null $plot Parcela opcional para validar propiedad
     */
    public function create(User $user, ?Plot $plot = null): bool
    {
        // Solo viticultores pueden crear actividades
        if (!$user->isViticulturist()) {
            return false;
        }

        // Si se proporciona una parcela, verificar que pertenece al viticultor
        if ($plot) {
            return $plot->viticulturist_id === $user->id;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede actualizar la actividad.
     */
    public function update(User $user, AgriculturalActivity $activity): bool
    {
        // Solo el viticultor dueño puede actualizar
        return $activity->viticulturist_id === $user->id;
    }

    /**
     * Determinar si el usuario puede eliminar la actividad.
     */
    public function delete(User $user, AgriculturalActivity $activity): bool
    {
        // Solo el viticultor dueño puede eliminar
        return $activity->viticulturist_id === $user->id;
    }
}
