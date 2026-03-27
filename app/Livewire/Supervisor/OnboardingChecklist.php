<?php

namespace App\Livewire\Supervisor;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\InvoicingSetting;
use App\Models\OnboardingProgress;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use Livewire\Component;

class OnboardingChecklist extends Component
{
    use WithToastNotifications;

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

        $this->autoCompleteExistingData($userId);

        $completedSteps = OnboardingProgress::where('user_id', $userId)
            ->whereIn('step', OnboardingProgress::SUPERVISOR_STEPS)
            ->whereNotNull('completed_at')
            ->count();

        $this->show = $completedSteps < count(OnboardingProgress::SUPERVISOR_STEPS);

        if (!$this->show) {
            return;
        }

        $this->steps = collect(OnboardingProgress::SUPERVISOR_STEPS)->map(function ($step) use ($userId) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);

            return [
                'key'         => $step,
                'title'       => $this->getStepTitle($step),
                'description' => $this->getStepDescription($step),
                'icon'        => $this->getStepIcon($step),
                'route'       => $this->getStepRoute($step),
                'completed'   => $progress->isCompleted(),
                'skipped'     => $progress->skipped,
            ];
        })->toArray();

        $total = count(OnboardingProgress::SUPERVISOR_STEPS);
        $this->progressPercentage = $total > 0
            ? (int) (($completedSteps / $total) * 100)
            : 0;
    }

    public function skipAll(): void
    {
        $userId = auth()->id();
        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            if (!$progress->isCompleted()) {
                $progress->markAsSkipped();
            }
        }
        $this->show = false;
        $this->toastInfo('Tour saltado. Puedes reactivarlo desde el dashboard.');
    }

    public function resetOnboarding(): void
    {
        OnboardingProgress::where('user_id', auth()->id())
            ->whereIn('step', OnboardingProgress::SUPERVISOR_STEPS)
            ->delete();
        $this->loadProgress();
        $this->toastSuccess('Tour reiniciado.');
    }

    private function autoCompleteExistingData(int $userId): void
    {
        $checks = [
            OnboardingProgress::STEP_SUPERVISOR_PROFILE => fn () =>
                InvoicingSetting::where('user_id', $userId)
                    ->whereNotNull('issuer_legal_name')
                    ->where('issuer_legal_name', '!=', '')
                    ->exists(),

            OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY => fn () =>
                SupervisorWinery::where('supervisor_id', $userId)->exists(),

            OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST => fn () =>
                SupervisorViticulturist::where('supervisor_id', $userId)->exists(),
        ];

        foreach ($checks as $step => $hasData) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            if (!$progress->isCompleted() && $hasData()) {
                $progress->markAsCompleted();
            }
        }
    }

    private function getStepTitle(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_SUPERVISOR_PROFILE          => 'Configura el perfil de la DO',
            OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY       => 'Vincula tu primera bodega',
            OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST => 'Añade tu primer viticultor',
            default => 'Paso desconocido',
        };
    }

    private function getStepDescription(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_SUPERVISOR_PROFILE          => 'Razón social, NIF y datos de la denominación para certificados y documentos oficiales',
            OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY       => 'Las bodegas adscritas podrán declarar bajo el amparo de tu DO',
            OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST => '¡Ya puedes supervisar el cuaderno de campo de tus viticultores!',
            default => '',
        };
    }

    private function getStepIcon(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_SUPERVISOR_PROFILE          => '🏛️',
            OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY       => '🏭',
            OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST => '👤',
            default => '✓',
        };
    }

    private function getStepRoute(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_SUPERVISOR_PROFILE          => route('supervisor.settings.index'),
            OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY       => route('supervisor.oversight.wineries.index'),
            OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST => route('supervisor.growers.index'),
            default => route('supervisor.dashboard'),
        };
    }

    public function render()
    {
        return view('livewire.supervisor.onboarding-checklist');
    }
}
