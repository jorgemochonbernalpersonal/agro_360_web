<?php

namespace App\Livewire\Concerns;

use App\Models\User;

trait WithUserFilters
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
            // Viticulturist users see all visible viticulturists:
            // - Themselves (always included)
            // - Viticultores they created
            // - Viticultores from their wineries (if associated with any)
            // - Viticultores from their supervisor pool (if they have a supervisor)
            'viticulturist' => $this->getAllVisibleViticulturists($user),
            default => collect(),
        };
    }

    /**
     * Obtener todos los viticultores visibles para un viticultor
     * Incluye: el usuario mismo, los que creó, los de sus bodegas, y los del pool de su supervisor
     */
    protected function getAllVisibleViticulturists(User $user)
    {
        // Siempre incluir al usuario mismo al principio
        $viticulturists = collect([$user]);
        
        // Agregar viticultores creados por este viticultor
        $created = \App\Models\WineryViticulturist::where('parent_viticulturist_id', $user->id)
            ->where('source', \App\Models\WineryViticulturist::SOURCE_VITICULTURIST)
            ->with('viticulturist')
            ->get()
            ->pluck('viticulturist')
            ->filter();
        
        $viticulturists = $viticulturists->merge($created);
        
        // Si tiene bodegas asociadas, agregar viticultores de esas bodegas
        // Obtener IDs de wineries directamente desde WineryViticulturist
        $wineryIds = \App\Models\WineryViticulturist::where('viticulturist_id', $user->id)
            ->whereNotNull('winery_id')
            ->pluck('winery_id');
        
        if ($wineryIds->isNotEmpty()) {
            $fromWineries = \App\Models\WineryViticulturist::whereIn('winery_id', $wineryIds)
                ->whereIn('source', [
                    \App\Models\WineryViticulturist::SOURCE_OWN,
                    \App\Models\WineryViticulturist::SOURCE_VITICULTURIST
                ])
                ->where('viticulturist_id', '!=', $user->id)
                ->with('viticulturist')
                ->get()
                ->pluck('viticulturist')
                ->filter();
            
            $viticulturists = $viticulturists->merge($fromWineries);
        }
        
        // Si tiene supervisor, agregar viticultores del pool del supervisor
        // Obtener ID del supervisor directamente desde WineryViticulturist
        $supervisorId = \App\Models\WineryViticulturist::where('viticulturist_id', $user->id)
            ->where('source', \App\Models\WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereNotNull('supervisor_id')
            ->value('supervisor_id');
        
        if ($supervisorId) {
            $fromSupervisor = \App\Models\WineryViticulturist::where('source', \App\Models\WineryViticulturist::SOURCE_SUPERVISOR)
                ->where('supervisor_id', $supervisorId)
                ->where('viticulturist_id', '!=', $user->id)
                ->with('viticulturist')
                ->get()
                ->pluck('viticulturist')
                ->filter();
            
            $viticulturists = $viticulturists->merge($fromSupervisor);
        }
        
        return $viticulturists->unique('id')->values();
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

