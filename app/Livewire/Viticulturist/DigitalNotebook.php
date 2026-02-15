<?php

namespace App\Livewire\Viticulturist;

use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;

/**
 * Componente principal del Cuaderno Digital
 * Orquesta los subcomponentes: Filtros, Estadísticas y Lista de Actividades
 */
class DigitalNotebook extends Component
{
    use WithToastNotifications;

    public $selectedCampaign = null;

    public function mount()
    {
        // Validar autorización
        if (!Auth::user()->can('viewAny', \App\Models\AgriculturalActivity::class)) {
            abort(403, 'No tienes permiso para ver actividades agrícolas.');
        }

        // Obtener o crear campaña activa del año actual
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());

        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
            return redirect()->route('viticulturist.campaign.index');
        }

        $this->selectedCampaign = $campaign->id;
    }

    public function render()
    {
        $user = Auth::user();

        // Obtener campañas del viticultor
        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        // Campaña seleccionada
        $currentCampaign = Campaign::find($this->selectedCampaign);

        return view('livewire.viticulturist.digital-notebook', [
            'campaigns' => $campaigns,
            'currentCampaign' => $currentCampaign,
        ]);
    }

    public function updatedSelectedCampaign()
    {
        // Notificar a los subcomponentes que la campaña cambió
        $this->dispatch('campaign-changed', $this->selectedCampaign);
    }
}
