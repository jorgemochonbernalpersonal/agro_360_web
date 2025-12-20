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
            'supervisor' => $this->canViewPlotAsSupervisor($user, $plot),
            'winery' => $this->canViewPlotAsWinery($user, $plot),
            'viticulturist' => $this->canViewPlotAsViticulturist($user, $plot),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        if (!in_array($user->role, ['admin', 'supervisor', 'winery', 'viticulturist'])) {
            return false;
        }
        
        // Admin, supervisor y winery siempre pueden crear
        // Los viticultores también pueden crear parcelas para ellos mismos o para viticultores que hayan creado
        return true;
    }

    public function update(User $user, Plot $plot): bool
    {
        return match($user->role) {
            'admin' => true,
            'supervisor' => $this->canUpdatePlotAsSupervisor($user, $plot),
            'winery' => $this->canUpdatePlotAsWinery($user, $plot),
            'viticulturist' => $this->canUpdatePlotAsViticulturist($user, $plot),
            default => false,
        };
    }

    public function delete(User $user, Plot $plot): bool
    {
        return $this->update($user, $plot);
    }

    /**
     * Verificar si un supervisor puede ver una parcela
     */
    protected function canViewPlotAsSupervisor(User $user, Plot $plot): bool
    {
        if (!$plot->viticulturist_id) {
            return false;
        }
        
        // Verificar si el viticultor de la parcela pertenece a alguna winery supervisada
        $supervisedWineryIds = $user->supervisedWineries->pluck('winery_id');
        
        return WineryViticulturist::where('viticulturist_id', $plot->viticulturist_id)
            ->whereIn('winery_id', $supervisedWineryIds)
            ->exists();
    }

    /**
     * Verificar si una winery puede ver una parcela
     */
    protected function canViewPlotAsWinery(User $user, Plot $plot): bool
    {
        if (!$plot->viticulturist_id) {
            return false;
        }
        
        // Verificar si el viticultor de la parcela pertenece a esta winery
        return WineryViticulturist::where('viticulturist_id', $plot->viticulturist_id)
            ->where('winery_id', $user->id)
            ->exists();
    }

    /**
     * Verificar si un supervisor puede actualizar una parcela
     */
    protected function canUpdatePlotAsSupervisor(User $user, Plot $plot): bool
    {
        return $this->canViewPlotAsSupervisor($user, $plot);
    }

    /**
     * Verificar si una winery puede actualizar una parcela
     */
    protected function canUpdatePlotAsWinery(User $user, Plot $plot): bool
    {
        if (!$this->canViewPlotAsWinery($user, $plot)) {
            return false;
        }
        
        // Solo puede editar si el viticultor fue creado por la winery o si no hay viticultor asignado
        if ($plot->viticulturist_id === null) {
            return true;
        }
        
        return $this->canEditViticulturistForPlot($user, $plot->viticulturist_id);
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
