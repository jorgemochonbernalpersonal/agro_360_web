<?php

namespace App\Policies;

use App\Models\Harvest;
use App\Models\User;

class HarvestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist', 'producer']);
    }

    public function view(User $user, Harvest $harvest): bool
    {
        return match ($user->role) {
            'admin' => true,
            'supervisor' => $this->isSupervisedWinery($user, $harvest),
            // Una cosecha es visible tanto para la bodega que la recibe (winery_id)
            // como para el viticultor que entregó la uva (activity.viticulturist_id).
            // producer cumple ambos roles, por eso comparte rama.
            'winery', 'viticulturist', 'producer' => $this->isOwnerWinery($user, $harvest)
                || $this->isOwnerViticulturist($user, $harvest),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'viticulturist', 'producer']);
    }

    public function update(User $user, Harvest $harvest): bool
    {
        return match ($user->role) {
            'admin' => true,
            // Solo quien posee la cosecha puede modificarla; el supervisor es de solo lectura.
            'winery', 'viticulturist', 'producer' => $this->isOwnerWinery($user, $harvest)
                || $this->isOwnerViticulturist($user, $harvest),
            default => false,
        };
    }

    public function delete(User $user, Harvest $harvest): bool
    {
        return $this->update($user, $harvest);
    }

    protected function isOwnerWinery(User $user, Harvest $harvest): bool
    {
        return $harvest->winery_id !== null && $harvest->winery_id === $user->id;
    }

    protected function isOwnerViticulturist(User $user, Harvest $harvest): bool
    {
        return (int) optional($harvest->activity)->viticulturist_id === $user->id;
    }

    protected function isSupervisedWinery(User $user, Harvest $harvest): bool
    {
        if ($harvest->winery_id === null) {
            return false;
        }

        return $user->supervisedWineries
            ->pluck('winery_id')
            ->contains($harvest->winery_id);
    }
}
