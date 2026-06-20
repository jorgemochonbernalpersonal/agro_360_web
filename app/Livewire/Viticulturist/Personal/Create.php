<?php

namespace App\Livewire\Viticulturist\Personal;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Crew;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $description = '';

    public $winery_id = '';

    public function mount(): void
    {
        if (! Auth::user()->can('create', Crew::class)) {
            abort(403);
        }

        $wineries = Auth::user()->wineries;

        if ($wineries->count() === 1) {
            $this->winery_id = $wineries->first()->id;
        }
    }

    protected function performCreate(): void
    {
        DB::transaction(function () {
            Crew::create([
                'name' => $this->name,
                'description' => $this->description,
                'viticulturist_id' => $this->viticulturistId(),
                'winery_id' => $this->winery_id ?: null,
            ]);
        });
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

    protected function successMessage(): string
    {
        return __('Cuadrilla creada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'personal.index';
    }

    protected function viewData(): array
    {
        return ['wineries' => Auth::user()->wineries];
    }
}
