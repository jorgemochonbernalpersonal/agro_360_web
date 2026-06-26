<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Campaign $campaign;

    public $name = '';

    public $year = '';

    public $start_date = '';

    public $end_date = '';

    public $description = '';

    public $active = false;

    public function mount(Campaign $campaign)
    {
        if (! Auth::user()->can('update', $campaign)) {
            abort(403, __('No tienes permiso para editar esta campaña.'));
        }

        $this->campaign = $campaign;
        $this->name = $campaign->name;
        $this->year = $campaign->year;
        $this->start_date = $campaign->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $campaign->end_date?->format('Y-m-d') ?? '';
        $this->description = $campaign->description ?? '';
        $this->active = $campaign->active;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            $justActivated = app(CampaignService::class)->update($this->campaign, $user, [
                'name' => $this->name,
                'year' => $this->year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'description' => $this->description,
                'active' => $this->active,
            ]);

            if ($justActivated) {
                session()->flash('campaign_activated', __('Campaña :year activada. Ya puedes registrar actividades.', ['year' => $this->campaign->year]));
                $route = $user->isProducer() ? route('producer.digital-notebook.estimated-yields.index') : route('viticulturist.digital-notebook');

                return $this->redirect($route, navigate: true);
            }

            $this->toastSuccess(__('Campaña actualizada correctamente.'));
            $route = $user->isProducer() ? route('producer.campaign.index') : route('viticulturist.campaign.index');

            return $this->redirect($route, navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al actualizar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $this->campaign->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al actualizar la campaña. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.campaign.edit')
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
