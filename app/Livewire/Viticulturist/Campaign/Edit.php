<?php

namespace App\Livewire\Viticulturist\Campaign;

use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // Validar autorización
        if (!Auth::user()->can('update', $campaign)) {
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

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (now()->year + 5),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        // Validar que no exista otra campaña del mismo año (excepto la actual)
        $existingCampaign = Campaign::forViticulturist($user->id)
            ->forYear($this->year)
            ->where('id', '!=', $this->campaign->id)
            ->first();

        if ($existingCampaign) {
            $this->addError('year', __('Ya existe otra campaña para el año :year.', ['year' => $this->year]));
            return;
        }

        try {
            $wasActive = (bool) $this->campaign->active;

            DB::transaction(function () use ($user) {
                $this->campaign->update([
                    'name' => $this->name,
                    'year' => $this->year,
                    'start_date' => $this->start_date ?: null,
                    'end_date' => $this->end_date ?: null,
                    'description' => $this->description,
                ]);

                // Si se marca como activa, activarla
                if ($this->active && !$this->campaign->active) {
                    $this->campaign->activate();
                } elseif (!$this->active && $this->campaign->active) {
                    // Si se desmarca, solo desactivar (no activar otra)
                    $this->campaign->update(['active' => false]);
                }
            });

            $justActivated = $this->active && !$wasActive;
            if ($justActivated) {
                session()->flash('campaign_activated', __('Campaña :year activada. Ya puedes registrar actividades.', ['year' => $this->campaign->year]));
                $route = $user->isProducer() ? route('producer.digital-notebook.estimated-yields.index') : route('viticulturist.digital-notebook');
                return $this->redirect($route, navigate: true);
            }

            $this->toastSuccess(__('Campaña actualizada correctamente.'));
            $route = $user->isProducer() ? route('producer.campaign.index') : route('viticulturist.campaign.index');
            return $this->redirect($route, navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar campaña', [
                'error' => $e->getMessage(),
                'campaign_id' => $this->campaign->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al actualizar la campaña. Por favor, intenta de nuevo.'));
            return;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.campaign.edit')
            ->layout('layouts.app');
    }
}
