<?php

namespace App\Livewire\Viticulturist\CampaignSign;

use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    public ?Campaign $campaign = null;
    public $selectedCampaignId = null;
    public bool $confirmMid = false;
    public bool $confirmFinal = false;

    protected $queryString = [
        'selectedCampaignId' => ['except' => ''],
    ];

    public function mount()
    {
        $user = Auth::user();

        // Si viene por queryString, verificar ownership
        if ($this->selectedCampaignId) {
            $this->campaign = Campaign::forViticulturist($user->id)
                ->find($this->selectedCampaignId);
        }

        // Si no hay campaña válida, usar la activa
        if (!$this->campaign) {
            $this->campaign = Campaign::getOrCreateActiveForYear($user->id);
            $this->selectedCampaignId = $this->campaign?->id;
        }
    }

    public function updatedSelectedCampaignId(): void
    {
        $this->campaign = Campaign::forViticulturist(Auth::id())
            ->find($this->selectedCampaignId);

        $this->confirmMid   = false;
        $this->confirmFinal = false;
        $this->resetValidation();
    }

    public function signMidValidation()
    {
        if (!$this->confirmMid) {
            $this->addError('confirmMid', 'Debes confirmar la validación intermedia.');
            return;
        }

        if ($this->campaign->mid_validation_signed) {
            $this->toastError('La validación intermedia ya está firmada.');
            return;
        }

        $this->campaign->update([
            'mid_validation_signed'  => true,
            'mid_validation_date'    => now(),
            'mid_validation_user_id' => Auth::id(),
        ]);

        $this->campaign->refresh();
        $this->confirmMid = false;
        $this->toastSuccess('Validación intermedia firmada correctamente.');
    }

    public function signFinalValidation()
    {
        if (!$this->confirmFinal) {
            $this->addError('confirmFinal', 'Debes confirmar el cierre de campaña.');
            return;
        }

        if ($this->campaign->final_validation_signed) {
            $this->toastError('El cierre de campaña ya está firmado.');
            return;
        }

        if (!$this->campaign->mid_validation_signed) {
            $this->toastError('Debes firmar la validación intermedia antes del cierre.');
            return;
        }

        $this->campaign->update([
            'final_validation_signed'  => true,
            'final_validation_date'    => now(),
            'final_validation_user_id' => Auth::id(),
            'locked_at'                => now(),
        ]);

        $this->campaign->refresh();
        $this->confirmFinal = false;
        $this->toastSuccess('Campaña cerrada y bloqueada correctamente.');
    }

    public function render()
    {
        $campaigns = Campaign::forViticulturist(Auth::id())
            ->orderByDesc('year')
            ->get();

        return view('livewire.viticulturist.campaign-sign.index', [
            'campaigns' => $campaigns,
        ]);
    }
}
