<?php

namespace App\Livewire\Viticulturist;

use App\Models\OnboardingProgress;
use Livewire\Component;

class OnboardingChecklist extends Component
{
    public bool $show = true;
    public array $steps = [];
    public int $progressPercentage = 0;

    public function mount(): void
    {
        $this->loadProgress();
    }

    public function loadProgress(): void
    {
        $userId = auth()->id();
        
        // Verificar si debe mostrarse
        $this->show = !OnboardingProgress::isOnboardingComplete($userId);
        
        if (!$this->show) {
            return;
        }

        // Cargar progreso de cada paso
        $this->steps = collect(OnboardingProgress::ALL_STEPS)->map(function ($step) use ($userId) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            
            return [
                'key' => $step,
                'title' => $this->getStepTitle($step),
                'description' => $this->getStepDescription($step),
                'icon' => $this->getStepIcon($step),
                'route' => $this->getStepRoute($step),
                'completed' => $progress->isCompleted(),
                'skipped' => $progress->skipped,
            ];
        })->toArray();

        $this->progressPercentage = OnboardingProgress::getProgressPercentage($userId);
    }

    public function completeStep(string $step): void
    {
        $progress = OnboardingProgress::getOrCreate(auth()->id(), $step);
        $progress->markAsCompleted();
        
        $this->loadProgress();
        
        // Si todos los pasos están completados, mostrar mensaje de éxito
        if ($this->progressPercentage === 100) {
            session()->flash('onboarding_complete', true);
        }
    }

    public function skipAll(): void
    {
        OnboardingProgress::skipAll(auth()->id());
        $this->show = false;
        
        session()->flash('message', 'Onboarding saltado. Puedes reactivarlo desde el dashboard.');
    }

    public function resetOnboarding(): void
    {
        OnboardingProgress::resetOnboarding(auth()->id());
        $this->loadProgress();
        
        session()->flash('message', 'Onboarding reiniciado. Recarga la página para ver el tour de nuevo.');
    }

    private function getStepTitle(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_REVIEW_CAMPAIGN => 'Revisa tu campaña',
            OnboardingProgress::STEP_CREATE_PLOT => 'Añade tus parcelas',
            OnboardingProgress::STEP_ADD_PRODUCTS => 'Añade productos fitosanitarios',
            OnboardingProgress::STEP_REGISTER_ACTIVITY => 'Registra tu primera actividad',
            default => 'Paso desconocido',
        };
    }

    private function getStepDescription(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_REVIEW_CAMPAIGN => 'Verifica tu campaña activa',
            OnboardingProgress::STEP_CREATE_PLOT => 'Datos maestros de tus parcelas',
            OnboardingProgress::STEP_ADD_PRODUCTS => 'Catálogo de productos para tratamientos',
            OnboardingProgress::STEP_REGISTER_ACTIVITY => '¡Ya puedes empezar a trabajar!',
            default => '',
        };
    }

    private function getStepIcon(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_REVIEW_CAMPAIGN => '📅',
            OnboardingProgress::STEP_CREATE_PLOT => '🗺️',
            OnboardingProgress::STEP_ADD_PRODUCTS => '🧪',
            OnboardingProgress::STEP_REGISTER_ACTIVITY => '✅',
            default => '✓',
        };
    }

    private function getStepRoute(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_REVIEW_CAMPAIGN => route('viticulturist.campaign.index'),
            OnboardingProgress::STEP_CREATE_PLOT => route('plots.create'),
            OnboardingProgress::STEP_ADD_PRODUCTS => route('viticulturist.phytosanitary-products.index'),
            OnboardingProgress::STEP_REGISTER_ACTIVITY => route('viticulturist.digital-notebook'),
            default => route('viticulturist.dashboard'),
        };
    }

    public function render()
    {
        return view('livewire.viticulturist.onboarding-checklist');
    }
}
