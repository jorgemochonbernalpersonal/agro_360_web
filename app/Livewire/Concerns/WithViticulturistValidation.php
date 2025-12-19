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
            ! $user->isViticulturist() &&
            ! $user->isWinery() &&
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
}

