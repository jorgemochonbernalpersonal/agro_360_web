<?php

namespace App\Livewire\Producer;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\InvoicingSetting;
use App\Models\OnboardingProgress;
use App\Models\Plot;
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
            ->whereIn('step', OnboardingProgress::PRODUCER_STEPS)
            ->whereNotNull('completed_at')
            ->count();

        $this->show = $completedSteps < count(OnboardingProgress::PRODUCER_STEPS);

        if (! $this->show) {
            return;
        }

        $this->steps = collect(OnboardingProgress::PRODUCER_STEPS)->map(function ($step) use ($userId) {
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

        $total = count(OnboardingProgress::PRODUCER_STEPS);
        $this->progressPercentage = $total > 0
            ? (int) (($completedSteps / $total) * 100)
            : 0;
    }

    public function skipAll(): void
    {
        $userId = auth()->id();
        foreach (OnboardingProgress::PRODUCER_STEPS as $step) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            if (! $progress->isCompleted()) {
                $progress->markAsSkipped();
            }
        }
        $this->show = false;
        $this->toastInfo(__('Tour saltado. Puedes reactivarlo desde el dashboard.'));
    }

    public function resetOnboarding(): void
    {
        OnboardingProgress::where('user_id', auth()->id())
            ->whereIn('step', OnboardingProgress::PRODUCER_STEPS)
            ->delete();
        $this->loadProgress();
        $this->toastSuccess(__('Tour reiniciado.'));
    }

    public function render()
    {
        return view('livewire.producer.onboarding-checklist');
    }

    private function autoCompleteExistingData(int $userId): void
    {
        $checks = [
            OnboardingProgress::STEP_PRODUCER_FISCAL => fn () => InvoicingSetting::where('user_id', $userId)
                ->whereNotNull('issuer_legal_name')
                ->where('issuer_legal_name', '!=', '')
                ->exists(),

            OnboardingProgress::STEP_PRODUCER_PLOT => fn () => Plot::where('viticulturist_id', $userId)->exists(),

            OnboardingProgress::STEP_PRODUCER_CONTAINER => fn () => Container::where('user_id', $userId)->exists(),

            OnboardingProgress::STEP_PRODUCER_ACTIVITY => fn () => AgriculturalActivity::where('viticulturist_id', $userId)->exists(),

            OnboardingProgress::STEP_PRODUCER_RECEPTION => fn () => Harvest::where('winery_id', $userId)->exists(),
        ];

        foreach ($checks as $step => $hasData) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            if (! $progress->isCompleted() && $hasData()) {
                $progress->markAsCompleted();
            }
        }
    }

    private function getStepTitle(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_PRODUCER_FISCAL => __('Configura tus datos fiscales'),
            OnboardingProgress::STEP_PRODUCER_PLOT => __('Registra tu primera parcela'),
            OnboardingProgress::STEP_PRODUCER_CONTAINER => __('Añade tu primer contenedor'),
            OnboardingProgress::STEP_PRODUCER_ACTIVITY => __('Registra una actividad de campo'),
            OnboardingProgress::STEP_PRODUCER_RECEPTION => __('Registra tu primera recepción'),
            default => __('Paso desconocido'),
        };
    }

    private function getStepDescription(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_PRODUCER_FISCAL => __('NIF, razón social y dirección para facturas'),
            OnboardingProgress::STEP_PRODUCER_PLOT => __('Añade una parcela para empezar con el cuaderno de campo'),
            OnboardingProgress::STEP_PRODUCER_CONTAINER => __('Depósitos o barricas para la gestión de bodega'),
            OnboardingProgress::STEP_PRODUCER_ACTIVITY => __('Tratamiento, riego, labor cultural... ¡tu cuaderno digital!'),
            OnboardingProgress::STEP_PRODUCER_RECEPTION => __('Registra la primera entrada de uva en bodega'),
            default => '',
        };
    }

    private function getStepIcon(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_PRODUCER_FISCAL => '⚙️',
            OnboardingProgress::STEP_PRODUCER_PLOT => '🗺️',
            OnboardingProgress::STEP_PRODUCER_CONTAINER => '🏺',
            OnboardingProgress::STEP_PRODUCER_ACTIVITY => '📝',
            OnboardingProgress::STEP_PRODUCER_RECEPTION => '🍇',
            default => '✓',
        };
    }

    private function getStepRoute(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_PRODUCER_FISCAL => route('producer.settings', ['tab' => 'fiscal']),
            OnboardingProgress::STEP_PRODUCER_PLOT => route('producer.plots.create'),
            OnboardingProgress::STEP_PRODUCER_CONTAINER => route('producer.containers.create'),
            OnboardingProgress::STEP_PRODUCER_ACTIVITY => route('producer.digital-notebook'),
            OnboardingProgress::STEP_PRODUCER_RECEPTION => route('producer.grape-reception.create'),
            default => route('producer.dashboard'),
        };
    }
}
