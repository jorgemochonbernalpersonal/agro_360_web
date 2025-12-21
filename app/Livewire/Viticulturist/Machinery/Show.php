<?php

namespace App\Livewire\Viticulturist\Machinery;

use App\Models\Machinery;
use App\Models\AgriculturalActivity;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Machinery $machinery;

    public function mount(Machinery $machinery)
    {
        // Validar autorización
        if (!Auth::user()->can('view', $machinery)) {
            abort(403, 'No tienes permiso para ver esta maquinaria.');
        }

        $this->machinery = $machinery->loadCount('activities');
    }

    public function render()
    {
        // Obtener últimas actividades donde se usó esta maquinaria
        $recentActivities = AgriculturalActivity::where('machinery_id', $this->machinery->id)
            ->with(['plot', 'campaign', 'viticulturist'])
            ->orderBy('activity_date', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.viticulturist.machinery.show', [
            'recentActivities' => $recentActivities,
        ])->layout('layouts.app', [
            'title' => $this->machinery->name . ' - Maquinaria - Agro365',
            'description' => 'Detalles de la maquinaria ' . $this->machinery->name . '. Especificaciones técnicas, registro ROMA y historial de uso en actividades.',
        ]);
    }
}
