<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\InvoicingSetting;
use App\Models\OnboardingProgress;
use App\Models\Wine;
use App\Models\WineryViticulturist;
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
            ->whereIn('step', OnboardingProgress::WINERY_STEPS)
            ->whereNotNull('completed_at')
            ->count();

        $this->show = $completedSteps < count(OnboardingProgress::WINERY_STEPS);

        if (!$this->show) {
            return;
        }

        $this->steps = collect(OnboardingProgress::WINERY_STEPS)->map(function ($step) use ($userId) {
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

        $total = count(OnboardingProgress::WINERY_STEPS);
        $this->progressPercentage = $total > 0
            ? (int) (($completedSteps / $total) * 100)
            : 0;
    }

    public function skipAll(): void
    {
        $userId = auth()->id();
        foreach (OnboardingProgress::WINERY_STEPS as $step) {
            $progress = OnboardingProgress::getOrCreate($userId, $step);
            if (!$progress->isCompleted()) {
                $progress->markAsSkipped();
            }
        }
        $this->show = false;
        $this->toastInfo(__('Tour saltado. Puedes reactivarlo desde el dashboard.'));
    }

    public function resetOnboarding(): void
    {
        OnboardingProgress::where('user_id', auth()->id())
            ->whereIn('step', OnboardingProgress::WINERY_STEPS)
            ->delete();
        $this->loadProgress();
        $this->toastSuccess(__('Tour reiniciado.'));
    }

    private function autoCompleteExistingData(int $userId): void
    {
        $checks = [
            OnboardingProgress::STEP_WINERY_FISCAL => fn () =>
                InvoicingSetting::where('user_id', $userId)
                    ->whereNotNull('issuer_legal_name')
                    ->where('issuer_legal_name', '!=', '')
                    ->exists(),

            OnboardingProgress::STEP_WINERY_CONTAINERS => fn () =>
                Container::where('user_id', $userId)->exists(),

            OnboardingProgress::STEP_WINERY_VITICULTURIST => fn () =>
                WineryViticulturist::where('winery_id', $userId)->exists(),

            OnboardingProgress::STEP_WINERY_HARVEST => fn () =>
                Harvest::where('winery_id', $userId)->exists(),

            OnboardingProgress::STEP_WINERY_WINE => fn () =>
                Wine::where('user_id', $userId)->exists(),
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
            OnboardingProgress::STEP_WINERY_FISCAL        => __('Configura tus datos fiscales'),
            OnboardingProgress::STEP_WINERY_CONTAINERS    => __('Añade depósitos o barricas'),
            OnboardingProgress::STEP_WINERY_VITICULTURIST => __('Vincula un viticultor'),
            OnboardingProgress::STEP_WINERY_HARVEST       => __('Registra tu primera recepción'),
            OnboardingProgress::STEP_WINERY_WINE          => __('Crea tu primer vino'),
            default => __('Paso desconocido'),
        };
    }

    private function getStepDescription(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_WINERY_FISCAL        => __('Razón social, NIF y dirección para facturas y declaraciones'),
            OnboardingProgress::STEP_WINERY_CONTAINERS    => __('El inventario de tu bodega parte del aforo de tus envases'),
            OnboardingProgress::STEP_WINERY_VITICULTURIST => __('Conecta con proveedores de uva para gestionar parcelas y cosechas'),
            OnboardingProgress::STEP_WINERY_HARVEST       => __('Registra la primera entrada de uva y empieza la trazabilidad'),
            OnboardingProgress::STEP_WINERY_WINE          => '¡Ya puedes declarar en SILICIE e INFOVI!',
            default => '',
        };
    }

    private function getStepIcon(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_WINERY_FISCAL        => '⚙️',
            OnboardingProgress::STEP_WINERY_CONTAINERS    => '🏺',
            OnboardingProgress::STEP_WINERY_VITICULTURIST => '👤',
            OnboardingProgress::STEP_WINERY_HARVEST       => '🍇',
            OnboardingProgress::STEP_WINERY_WINE          => '🧪',
            default => '✓',
        };
    }

    private function getStepRoute(string $step): string
    {
        return match ($step) {
            OnboardingProgress::STEP_WINERY_FISCAL        => route('winery.settings'),
            OnboardingProgress::STEP_WINERY_CONTAINERS    => route('winery.containers.create'),
            OnboardingProgress::STEP_WINERY_VITICULTURIST => route('winery.viticulturists.create'),
            OnboardingProgress::STEP_WINERY_HARVEST       => route('winery.grape-reception.create'),
            OnboardingProgress::STEP_WINERY_WINE          => route('winery.wines.create'),
            default => route('winery.dashboard'),
        };
    }

    public function render()
    {
        return view('livewire.winery.onboarding-checklist');
    }
}
