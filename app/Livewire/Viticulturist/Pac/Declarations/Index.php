<?php

namespace App\Livewire\Viticulturist\Pac\Declarations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\PacDeclaration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public function submit(int $id): void
    {
        $declaration = PacDeclaration::forViticulturist(Auth::id())->findOrFail($id);

        if (!$declaration->isDraft()) return;

        if ($declaration->items()->count() === 0) {
            $this->toastError(__('La declaración no tiene parcelas. Añade al menos una antes de presentarla.'));
            return;
        }

        $declaration->update([
            'status'       => PacDeclaration::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->toastSuccess("Declaración PAC {$declaration->year} presentada correctamente.");
    }

    public function delete(int $id): void
    {
        $declaration = PacDeclaration::forViticulturist(Auth::id())->findOrFail($id);

        if (!$declaration->isDraft()) {
            $this->toastError(__('Solo se pueden eliminar declaraciones en borrador.'));
            return;
        }

        $declaration->delete();
        $this->toastSuccess(__('Declaración eliminada.'));
    }

    public function render()
    {
        $declarations = PacDeclaration::forViticulturist(Auth::id())
            ->withCount('items')
            ->orderByDesc('year')
            ->get();

        $stats = [
            'total'     => $declarations->count(),
            'draft'     => $declarations->where('status', 'draft')->count(),
            'submitted' => $declarations->where('status', 'submitted')->count(),
            'approved'  => $declarations->where('status', 'approved')->count(),
        ];

        return view('livewire.viticulturist.pac.declarations.index', [
            'declarations' => $declarations,
            'stats'        => $stats,
            'currentYear'  => now()->year,
        ])->layout('layouts.app');
    }
}
