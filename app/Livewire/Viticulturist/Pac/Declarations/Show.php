<?php

namespace App\Livewire\Viticulturist\Pac\Declarations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\PacDeclaration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public PacDeclaration $declaration;

    public function mount(PacDeclaration $declaration): void
    {
        if ($declaration->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        $this->declaration = $declaration->load(['items.plot.municipality']);
    }

    public function submit(): void
    {
        if (! $this->declaration->isDraft()) {
            return;
        }

        if ($this->declaration->items()->count() === 0) {
            $this->toastError(__('No hay parcelas en la declaración.'));

            return;
        }

        $this->declaration->update([
            'status' => PacDeclaration::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->declaration->refresh();
        $this->toastSuccess("Declaración PAC {$this->declaration->year} presentada.");
    }

    public function render()
    {
        $ecoSchemesMap = PacDeclaration::ECO_SCHEMES;

        return view('livewire.viticulturist.pac.declarations.show', [
            'ecoSchemesMap' => $ecoSchemesMap,
        ])->layout('layouts.app');
    }
}
