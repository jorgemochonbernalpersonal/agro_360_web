<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class QuickEntry extends Component
{
    use WithToastNotifications, WithViticulturistValidation;

    public int $step = 1;

    public string $activityType = '';

    public ?int $plotId = null;

    public string $activityDate = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->activityDate = now()->toDateString();
    }

    public function selectType(string $type): void
    {
        $this->activityType = $type;
        $this->step = 2;
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function save(): void
    {
        $this->validate([
            'activityType' => 'required|string',
            'plotId' => $this->plotOwnershipRule(),
            'activityDate' => 'required|date',
        ]);

        $user = Auth::user();

        Plot::where('id', $this->plotId)
            ->where('viticulturist_id', $user->id)
            ->firstOrFail();

        $campaign = Campaign::forViticulturist($user->id)
            ->where('active', true)
            ->first();

        AgriculturalActivity::create([
            'viticulturist_id' => $user->id,
            'plot_id' => $this->plotId,
            'campaign_id' => $campaign?->id,
            'activity_type' => $this->activityType,
            'activity_date' => $this->activityDate,
            'notes' => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Actividad registrada.'));
        $this->reset(['activityType', 'plotId', 'notes']);
        $this->step = 1;
        $this->activityDate = now()->toDateString();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $plots = Plot::where('viticulturist_id', Auth::id())
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $activityTypes = [
            'observation' => ['label' => __('Observación'),    'icon' => '👁️',  'color' => 'bg-zinc-100 border-zinc-300 text-zinc-700'],
            'irrigation' => ['label' => __('Riego'),          'icon' => '💧',  'color' => 'bg-blue-50 border-blue-300 text-blue-700'],
            'fertilization' => ['label' => __('Fertilización'),  'icon' => '🌿',  'color' => 'bg-green-50 border-green-300 text-green-700'],
            'pruning' => ['label' => __('Poda'),           'icon' => '✂️',  'color' => 'bg-yellow-50 border-yellow-300 text-yellow-700'],
            'phytosanitary' => ['label' => __('Fitosanitario'),  'icon' => '💊',  'color' => 'bg-red-50 border-red-300 text-red-700'],
            'cultural' => ['label' => __('Labor cultural'), 'icon' => '🚜',  'color' => 'bg-orange-50 border-orange-300 text-orange-700'],
            'harvest' => ['label' => __('Cosecha'),        'icon' => '🍇',  'color' => 'bg-purple-50 border-purple-300 text-purple-700'],
            'post_harvest' => ['label' => __('Post-vendimia'),  'icon' => '📦',  'color' => 'bg-indigo-50 border-indigo-300 text-indigo-700'],
        ];

        return view('livewire.viticulturist.quick-entry', [
            'plots' => $plots,
            'activityTypes' => $activityTypes,
        ])->layout('layouts.app', ['title' => __('Entrada rápida - Agro365')]);
    }
}
