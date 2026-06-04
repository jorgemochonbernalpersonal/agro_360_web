<?php

namespace App\Livewire\Viticulturist\Viticulturists;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

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

    public function save()
    {
        $this->validate();

        $creator = Auth::user();
        $wineries = $creator->wineries;

        if ($this->winery_id && ! $wineries->contains('id', $this->winery_id)) {
            $this->addError('winery_id', __('No estás asignado a esta bodega.'));

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

            $this->toastSuccess(__('Viticultor creado correctamente. Puedes enviar la invitación desde la tabla de acciones.'));

            return $this->viticulturistRoleRedirect('personal.index', ['viewMode' => 'personal']);
        } catch (\Exception $e) {
            Log::error('Error al crear viticultor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError(__('Error al crear el viticultor. Por favor, intenta de nuevo.'));
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();
        $wineries = $user->wineries;

        return view('livewire.viticulturist.viticulturists.create', [
            'wineries' => $wineries,
        ]);
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
                        if (! $exists) {
                            $fail(__('No estás asignado a esta bodega.'));
                        }
                    }
                },
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El campo nombre es obligatorio.'),
            'name.max' => __('El nombre no puede tener más de 255 caracteres.'),
            'email.required' => __('El campo email es obligatorio.'),
            'email.email' => __('El email debe ser una dirección de correo válida.'),
            'email.max' => __('El email no puede tener más de 255 caracteres.'),
            'email.unique' => __('Este email ya está registrado. Por favor, usa otro email.'),
            'winery_id.exists' => __('La bodega seleccionada no es válida.'),
        ];
    }
}
