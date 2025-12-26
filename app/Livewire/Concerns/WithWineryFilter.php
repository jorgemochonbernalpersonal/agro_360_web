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
            // Winery users see viticultores creados por la winery
            'winery' => $this->getEditableViticulturistsForWinery($user),
            // Viticulturist users must ALWAYS see only the viticultores that THEY created,
            // independientemente de si están asociados a alguna winery.
            'viticulturist' => $this->getCreatedViticulturists($user),
            default => collect(),
        };
    }

    /**
     * Obtener viticultores creados por este viticultor (source = 'viticulturist').
     * Esto garantiza que aunque el viticultor esté asociado a una winery, el listado
     * de opciones mostrará solo los viticultores que él creó personalmente.
     * SIEMPRE incluye al usuario mismo + los viticultores que ha creado.
     */
    protected function getCreatedViticulturists(User $user)
    {
        $createdViticulturists = \App\Models\WineryViticulturist::where('parent_viticulturist_id', $user->id)
            ->where('source', \App\Models\WineryViticulturist::SOURCE_VITICULTURIST)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->filter()
            ->unique('id');
        
        // SIEMPRE incluir al usuario mismo al principio de la lista
        return collect([$user])->merge($createdViticulturists)->unique('id')->values();
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

