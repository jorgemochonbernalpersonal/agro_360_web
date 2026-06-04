<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public Campaign $campaign;

    public function mount(Campaign $campaign)
    {
        // Validar autorización
        if (! Auth::user()->can('view', $campaign)) {
            abort(403, __('No tienes permiso para ver esta campaña.'));
        }

        $this->campaign = $campaign->loadCount([
            'activities',
            'activities as phytosanitary_count' => function ($query) {
                $query->ofType('phytosanitary');
            },
            'activities as fertilization_count' => function ($query) {
                $query->ofType('fertilization');
            },
            'activities as irrigation_count' => function ($query) {
                $query->ofType('irrigation');
            },
            'activities as cultural_count' => function ($query) {
                $query->ofType('cultural');
            },
            'activities as observation_count' => function ($query) {
                $query->ofType('observation');
            },
            'activities as harvest_count' => function ($query) {
                $query->ofType('harvest');
            },
        ]);
    }

    public function activate()
    {
        if (! Auth::user()->can('activate', $this->campaign)) {
            $this->toastError(__('No tienes permiso para activar esta campaña.'));

            return;
        }

        try {
            $this->campaign->activate();
            $this->campaign->refresh();
            $this->toastSuccess(__('Campaña activada correctamente.'));
        } catch (\Exception $e) {
            \Log::error('Error al activar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $this->campaign->id,
                'user_id' => Auth::id(),
            ]);

            $this->toastError(__('Error al activar la campaña. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        // Obtener últimas actividades
        $recentActivities = AgriculturalActivity::forCampaign($this->campaign->id)
            ->with(['plot', 'plotPlanting.grapeVariety', 'phytosanitaryTreatment.product', 'fertilization', 'irrigation', 'culturalWork', 'observation'])
            ->orderBy('activity_date', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.viticulturist.campaign.show', [
            'recentActivities' => $recentActivities,
        ])->layout('layouts.app', [
            'title' => $this->campaign->name.' ('.$this->campaign->year.') - Campaña - Agro365',
            'description' => __('Detalles de la campaña ').$this->campaign->name.' del año '.$this->campaign->year.'. Actividades, estadísticas y rendimientos.',
        ]);
    }
}
