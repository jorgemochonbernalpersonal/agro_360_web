<?php

namespace App\Livewire\Viticulturist\Viticulturists;

use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $winery_id = '';

    public function mount()
    {
        $user = Auth::user();
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
            'email' => 'required|string|email|max:255|unique:users',
            'winery_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
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

        $creator = Auth::user();
        $wineries = $creator->wineries;

        if ($this->winery_id && !$wineries->contains('id', $this->winery_id)) {
            $this->addError('winery_id', 'No estás asignado a esta bodega.');
            return;
        }

        $password = Str::password(12);

        try {
            $viticulturist = null;

            DB::transaction(function () use ($creator, $password, &$viticulturist) {
                $viticulturist = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($password),
                    'role' => 'viticulturist',
                    'can_login' => false,
                    'invitation_sent_at' => null,
                ]);

                WineryViticulturist::create([
                    'winery_id' => $this->winery_id ?: null,
                    'viticulturist_id' => $viticulturist->id,
                    'source' => WineryViticulturist::SOURCE_VITICULTURIST,
                    'parent_viticulturist_id' => $creator->id,
                    'assigned_by' => $creator->id,
                ]);
            });

            session()->flash('message', 'Viticultor creado correctamente. Puedes enviar la invitación desde la tabla de acciones.');
            return $this->redirect(route('viticulturist.personal.index', ['viewMode' => 'personal']), navigate: true);
        } catch (\Exception $e) {
            Log::error('Error al crear viticultor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al crear el viticultor. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $wineries = $user->wineries;

        return view('livewire.viticulturist.viticulturists.create', [
            'wineries' => $wineries,
        ])->layout('layouts.app');
    }
}
