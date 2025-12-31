<?php

namespace App\Livewire\Viticulturist;

use App\Models\OnboardingProgress;
use Livewire\Component;

class OnboardingWelcome extends Component
{
    public bool $showModal = false;

    public function mount(): void
    {
        // Mostrar modal solo si es la primera vez (ningún paso completado)
        $userId = auth()->id();
        $hasAnyProgress = OnboardingProgress::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->exists();

        $this->showModal = !$hasAnyProgress;
    }

    public function startTour(): void
    {
        $this->showModal = false;
        // El checklist ya está visible en el dashboard
    }

    public function skipTour(): void
    {
        OnboardingProgress::skipAll(auth()->id());
        $this->showModal = false;
        
        session()->flash('message', 'Onboarding saltado. Puedes reactivarlo desde Configuración.');
    }

    public function render()
    {
        return view('livewire.viticulturist.onboarding-welcome');
    }
}
