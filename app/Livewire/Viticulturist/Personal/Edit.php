<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Crew;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Crew $crew;

    public $name = '';

    public $description = '';

    public $winery_id = '';

    public function mount(Crew $crew)
    {
        if (! Auth::user()->can('update', $crew)) {
            abort(403, __('No tienes permiso para editar esta cuadrilla.'));
        }

        $this->crew = $crew;
        $this->name = $crew->name;
        $this->description = $crew->description;
        $this->winery_id = $crew->winery_id ?? ''; // Convertir NULL a cadena vacía para el formulario
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $this->crew->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'winery_id' => $this->winery_id ?: null, // Convertir cadena vacía a NULL
                ]);
            });

            $this->toastSuccess(__('Cuadrilla actualizada correctamente.'));

            return $this->viticulturistRoleRedirect('personal.show', $this->crew);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cuadrilla', [
                'error' => $e->getMessage(),
                'crew_id' => $this->crew->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al actualizar la cuadrilla. Por favor, intenta de nuevo.'));

            return;
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();

        // Obtener wineries usando relación
        $wineries = $user->wineries;

        return view('livewire.viticulturist.personal.edit', [
            'wineries' => $wineries,
        ]);
    }

    protected function rules(): array
    {
        $user = Auth::user();

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'winery_id' => [
                'nullable', // Cambiar de 'required' a 'nullable'
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        // Solo validar si se proporciona winery_id
                        $exists = WineryViticulturist::where('viticulturist_id', $user->id)
                            ->where('winery_id', $value)
                            ->exists();

                        if (! $exists) {
                            $fail(__('No estás asignado a esta bodega.'));
                        }
                    }
                },
            ],
        ];
    }
}
