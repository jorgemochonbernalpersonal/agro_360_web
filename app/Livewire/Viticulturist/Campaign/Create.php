<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public $name = '';

    public $year = '';

    public $start_date = '';

    public $end_date = '';

    public $description = '';

    public $active = false;

    public function mount()
    {
        if (! Auth::user()->can('create', Campaign::class)) {
            abort(403, __('No tienes permiso para crear campañas.'));
        }

        $this->year = now()->year;
        $this->start_date = now()->startOfYear()->format('Y-m-d');
        $this->end_date = now()->endOfYear()->format('Y-m-d');
        $this->name = "Campaña {$this->year}";
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            $result = app(CampaignService::class)->create($user, [
                'name' => $this->name,
                'year' => $this->year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'description' => $this->description,
                'active' => $this->active,
            ]);

            if ($result['activated']) {
                session()->flash('campaign_activated', __('Campaña :year activada. Ya puedes registrar actividades.', ['year' => $this->year]));
                $route = $user->isProducer() ? route('producer.digital-notebook.estimated-yields.index') : route('viticulturist.digital-notebook');

                return $this->redirect($route, navigate: true);
            }

            $this->toastSuccess(__('Campaña creada correctamente. Actívala cuando quieras empezar a registrar actividades.'));
            $route = $user->isProducer() ? route('producer.campaign.index') : route('viticulturist.campaign.index');

            return $this->redirect($route, navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al crear campaña', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'year' => $this->year,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al crear la campaña. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.campaign.create')
            ->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:'.(now()->year + 5),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }
}
