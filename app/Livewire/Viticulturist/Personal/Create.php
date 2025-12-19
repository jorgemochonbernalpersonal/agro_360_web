<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Models\Crew;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $description = '';
    public $winery_id = '';

    public function mount()
    {
        if (!Auth::user()->can('create', Crew::class)) {
            abort(403, 'No tienes permiso para crear cuadrillas.');
        }

        $user = Auth::user();

        // Si solo tiene una bodega, auto-seleccionarla
        $wineries = $user->wineries;

        if ($wineries->count() === 1) {
            $this->winery_id = $wineries->first()->id;
        }
    }

    protected function rules(): array
    {
        $user = Auth::user();

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'winery_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        // Solo validar si se proporciona winery_id
                        $exists = WineryViticulturist::where('viticulturist_id', $user->id)
                            ->where('winery_id', $value)
                            ->exists();

                        if (!$exists) {
                            $fail('No estás asignado a esta bodega.');
                        }
                    }
                },
            ],
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                Crew::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'viticulturist_id' => $user->id,
                    'winery_id' => $this->winery_id ?: null,  // Convertir cadena vacía a NULL
                ]);
            });

            session()->flash('message', 'Cuadrilla creada correctamente.');
            return redirect()->route('viticulturist.personal.index');
        } catch (\Exception $e) {
            Log::error('Error al crear cuadrilla', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'winery_id' => $this->winery_id,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al crear la cuadrilla. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        $user = Auth::user();

        // Obtener wineries usando relación
        $wineries = $user->wineries;

        return view('livewire.viticulturist.personal.create', [
            'wineries' => $wineries,
        ])->layout('layouts.app');
    }
}
