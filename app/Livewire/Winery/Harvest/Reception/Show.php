<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Notifications\HarvestDeliveryResolvedNotification;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public Harvest $harvest;

    public string $resolutionNote = '';

    public bool $showResolveForm = false;

    public function mount(Harvest $harvest): void
    {
        $this->authorize('manageAsWinery', $harvest);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
            'editor',
            'delivery',
        ]);
    }

    public function openResolveDispute(): void
    {
        $this->showResolveForm = true;
        $this->resolutionNote = '';
    }

    public function cancelResolveDispute(): void
    {
        $this->showResolveForm = false;
        $this->resolutionNote = '';
    }

    public function resolveDispute(): void
    {
        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'resolutionNote.required' => __('Escribe una respuesta para el viticultor.'),
            'resolutionNote.min' => __('La respuesta debe tener al menos 5 caracteres.'),
            'resolutionNote.max' => __('La respuesta no puede superar los 1000 caracteres.'),
        ]);

        $delivery = $this->harvest->delivery;

        abort_unless(
            $delivery && $delivery->status === 'disputed' && $delivery->hasDisputeNote(),
            403
        );

        $delivery->update([
            'status' => 'resolved',
            'dispute_resolution_note' => $this->resolutionNote,
            'dispute_resolved_at' => now(),
        ]);

        // Reload so the view reflects the change immediately
        $this->harvest->load('delivery');

        // Notify the viticulturist
        $viticulturist = $delivery->viticulturist;
        if ($viticulturist?->email) {
            $viticulturist->notify(new HarvestDeliveryResolvedNotification($delivery));
        }

        $this->showResolveForm = false;
        $this->resolutionNote = '';

        $name = $viticulturist->name ?? 'el viticultor';
        $this->toastSuccess("Respuesta enviada a {$name}. Recibirá una notificación por email.");
    }

    public function render()
    {
        return view('livewire.winery.harvest.reception.show')
            ->layout('layouts.app');
    }
}
