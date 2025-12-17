<?php

namespace App\Livewire\Concerns;

use App\Models\User;

trait WithWineryFilter
{
    /**
     * Obtener bodegas accesibles según el rol del usuario
     */
    public function getWineriesProperty()
    {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => User::where('role', User::ROLE_WINERY)->get(),
            'supervisor' => User::whereIn('id', 
                $user->supervisedWineries->pluck('winery_id')
            )->get(),
            'winery' => collect([$user]),
            default => collect(),
        };
    }
    
    /**
     * Obtener viticultores accesibles según el rol del usuario
     * Solo muestra viticultores que el usuario puede editar (crear parcelas para ellos)
     */
    public function getViticulturistsProperty()
    {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => User::where('role', User::ROLE_VITICULTURIST)->get(),
            'supervisor' => User::whereIn('id', 
                $user->supervisedViticulturists->pluck('viticulturist_id')
            )->get(),
            'winery' => $this->getEditableViticulturistsForWinery($user),
            'viticulturist' => $this->getEditableViticulturistsForViticulturist($user),
            default => collect(),
        };
    }

    /**
     * Obtener viticultores editables para una winery
     * Solo viticultores creados por esta winery
     */
    protected function getEditableViticulturistsForWinery(User $user)
    {
        return \App\Models\WineryViticulturist::where('winery_id', $user->id)
            ->where('source', \App\Models\WineryViticulturist::SOURCE_OWN)
            ->where('assigned_by', $user->id)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Obtener viticultores editables para un viticultor
     * Solo viticultores creados por este viticultor
     */
    protected function getEditableViticulturistsForViticulturist(User $user)
    {
        return \App\Models\WineryViticulturist::editableBy($user)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->filter()
            ->unique('id')
            ->values();
    }
}

