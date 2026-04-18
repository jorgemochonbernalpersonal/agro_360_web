<?php

namespace App\Livewire\Viticulturist;

use App\Models\Campaign;
use App\Repositories\AgriculturalActivityRepository;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DigitalNotebook extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public $selectedCampaign = null;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
    ];

    public function mount(): void
    {
        if ($this->selectedCampaign) {
            $valid = Campaign::forViticulturist(Auth::id())
                ->where('id', $this->selectedCampaign)
                ->exists();
            if (!$valid) {
                $this->selectedCampaign = null;
            }
        }

        if (!$this->selectedCampaign) {
            $campaign = Campaign::getOrCreateActiveForYear(Auth::id());

            if (!$campaign) {
                $this->toastError('No se pudo obtener la campaña activa. Por favor, crea una campaña primero.');
                $this->viticulturistRoleRedirect('campaign.index');
                return;
            }

            $this->selectedCampaign = $campaign->id;
        }

        if (session()->has('campaign_activated')) {
            $this->toastSuccess(session()->pull('campaign_activated'));
        }
    }

    public function updatedSelectedCampaign(): void
    {
        // El selector de campaña recarga automáticamente vía render()
    }

    #[Layout('layouts.app', [
        'title'       => 'Cuaderno de Campo - Agro365',
        'description' => 'Resumen de actividades agrícolas por campaña.',
    ])]
    public function render()
    {
        $user       = Auth::user();
        $repository = app(AgriculturalActivityRepository::class);

        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        $currentCampaign = Campaign::find($this->selectedCampaign);

        $stats = $currentCampaign
            ? $repository->getStatsForCampaign($user->id, $currentCampaign->id)
            : ['total' => 0, 'phytosanitary' => 0, 'fertilization' => 0, 'irrigation' => 0, 'cultural' => 0, 'observation' => 0, 'harvest' => 0];

        return view('livewire.viticulturist.digital-notebook', [
            'campaigns'       => $campaigns,
            'currentCampaign' => $currentCampaign,
            'stats'           => $stats,
        ]);
    }
}
