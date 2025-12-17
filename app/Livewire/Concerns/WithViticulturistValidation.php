<?php

namespace App\Livewire\Concerns;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;

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
        if (!Auth::user()->can('create', \App\Models\AgriculturalActivity::class)) {
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
        
        if (!Auth::user()->can('create', [\App\Models\AgriculturalActivity::class, $plot])) {
            abort(403, 'No tienes permiso para crear actividades en esta parcela.');
        }
        
        return $plot;
    }
}

