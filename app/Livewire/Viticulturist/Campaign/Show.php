<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use App\Models\AgriculturalActivity;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithToastNotifications;
    public Campaign $campaign;

    public function mount(Campaign $campaign)
    {
        // Validar autorización
        if (!Auth::user()->can('view', $campaign)) {
            abort(403, 'No tienes permiso para ver esta campaña.');
        }

        $this->campaign = $campaign->loadCount([
            'activities',
            'activities as phytosanitary_count' => function($query) {
                $query->ofType('phytosanitary');
            },
            'activities as fertilization_count' => function($query) {
                $query->ofType('fertilization');
            },
            'activities as irrigation_count' => function($query) {
                $query->ofType('irrigation');
            },
            'activities as cultural_count' => function($query) {
                $query->ofType('cultural');
            },
            'activities as observation_count' => function($query) {
                $query->ofType('observation');
            },
        ]);
    }

    public function activate()
    {
        if (!Auth::user()->can('activate', $this->campaign)) {
            $this->toastError('No tienes permiso para activar esta campaña.');
            return;
        }

        try {
            $this->campaign->activate();
            $this->campaign->refresh();
            $this->toastSuccess('Campaña activada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al activar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $this->campaign->id,
                'user_id' => Auth::id(),
            ]);

            $this->toastError('Error al activar la campaña. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        // Obtener últimas actividades
        $recentActivities = AgriculturalActivity::forCampaign($this->campaign->id)
            ->with(['plot', 'phytosanitaryTreatment.product', 'fertilization', 'irrigation', 'culturalWork', 'observation'])
            ->orderBy('activity_date', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.viticulturist.campaign.show', [
            'recentActivities' => $recentActivities,
        ])->layout('layouts.app');
    }
}
