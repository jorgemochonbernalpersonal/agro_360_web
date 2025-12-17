<?php

namespace App\Policies;

use App\Models\Plot;
use App\Models\User;
use App\Models\WineryViticulturist;

class PlotPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist']);
    }

    public function view(User $user, Plot $plot): bool
    {
        return match($user->role) {
            'admin' => true,
            'supervisor' => $user->supervisedWineries->contains('winery_id', $plot->winery_id),
            'winery' => $plot->winery_id === $user->id,
            'viticulturist' => $this->canViewPlotAsViticulturist($user, $plot),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        if (!in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist'])) {
            return false;
        }
        
        // Para viticultores: solo pueden crear si tienen viticultores propios
        if ($user->isViticulturist()) {
            return WineryViticulturist::editableBy($user)->exists();
        }
        
        // Admin, supervisor y winery siempre pueden crear
        return true;
    }

    public function update(User $user, Plot $plot): bool
    {
        return match($user->role) {
            'admin' => true,
            'supervisor' => $user->supervisedWineries->contains('winery_id', $plot->winery_id),
            'winery' => $plot->winery_id === $user->id && 
                       ($plot->viticulturist_id === null || 
                        $this->canEditViticulturistForPlot($user, $plot->viticulturist_id)),
            'viticulturist' => $this->canUpdatePlotAsViticulturist($user, $plot),
            default => false,
        };
    }

    public function delete(User $user, Plot $plot): bool
    {
        return $this->update($user, $plot);
    }

    /**
     * Verificar si un viticultor puede ver una parcela
     * Optimizado: usa el scope del modelo en lugar de query individual
     */
    protected function canViewPlotAsViticulturist(User $user, Plot $plot): bool
    {
        // Puede ver si es su propia parcela
        if ($plot->viticulturist_id === $user->id) {
            return true;
        }
        
        // Verificar si la parcela está en el conjunto de parcelas visibles para el viticultor
        return Plot::forViticulturist($user)
            ->where('id', $plot->id)
            ->exists();
    }

    /**
     * Verificar si un viticultor puede actualizar una parcela
     */
    protected function canUpdatePlotAsViticulturist(User $user, Plot $plot): bool
    {
        // Puede editar si es su propia parcela
        if ($plot->viticulturist_id === $user->id) {
            return true;
        }
        
        // Solo puede editar si el viticultor de la parcela fue creado por él
        if ($plot->viticulturist_id) {
            return $user->canEditViticulturist($plot->viticulturist_id);
        }
        
        return false;
    }

    /**
     * Verificar si un usuario puede editar un viticultor para asignarlo a una parcela
     */
    protected function canEditViticulturistForPlot(User $user, ?int $viticulturistId): bool
    {
        if (!$viticulturistId) {
            return false;
        }
        
        // Solo puede editar si el viticultor fue creado por el usuario
        if ($user->isViticulturist()) {
            return $user->canEditViticulturist($viticulturistId);
        }
        
        // Para winery: verificar si el viticultor fue creado por la winery
        if ($user->isWinery()) {
            return WineryViticulturist::where('viticulturist_id', $viticulturistId)
                ->where('winery_id', $user->id)
                ->where('source', WineryViticulturist::SOURCE_OWN)
                ->where('assigned_by', $user->id)
                ->exists();
        }
        
        return false;
    }
}
