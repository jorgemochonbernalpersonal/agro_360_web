<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'viticulturist'; // Por defecto
    public $winery_id = ''; // Si lo crea un winery
    public $supervisor_id = ''; // Si lo crea un supervisor

    public function mount()
    {
        // Si está autenticado, determinar rol por defecto según quién crea
        if (Auth::check()) {
            $user = Auth::user();
            $this->role = match($user->role) {
                'admin' => 'viticulturist', // Admin puede elegir cualquier rol
                'supervisor' => 'viticulturist', // Supervisor crea winery o viticulturist
                'winery' => 'viticulturist', // Winery solo crea viticulturist
                default => 'viticulturist',
            };
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        // Si está autenticado, puede seleccionar rol
        if (Auth::check()) {
            $user = Auth::user();
            $allowedRoles = $this->getAllowedRoles($user);
            $rules['role'] = 'required|in:' . implode(',', $allowedRoles);
        } else {
            // Registro público: solo winery y viticulturist
            $rules['role'] = 'required|in:winery,viticulturist';
        }

        return $rules;
    }

    public function getAllowedRoles(?User $user = null): array
    {
        if (!$user) {
            return ['winery', 'viticulturist']; // Registro público
        }

        return match($user->role) {
            'admin' => ['admin', 'supervisor', 'winery', 'viticulturist'],
            'supervisor' => ['winery', 'viticulturist'],
            'winery' => ['viticulturist'],
            'viticulturist' => ['viticulturist'], // Viticultor puede crear otros viticultores
            default => [],
        };
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ]);

        // Crear relaciones automáticas si está autenticado
        if (Auth::check()) {
            $creator = Auth::user();

            // Si supervisor crea winery
            if ($creator->isSupervisor() && $this->role === 'winery') {
                SupervisorWinery::create([
                    'supervisor_id' => $creator->id,
                    'winery_id' => $user->id,
                    'assigned_by' => $creator->id,
                ]);
            }

            // Si supervisor crea viticulturist
            if ($creator->isSupervisor() && $this->role === 'viticulturist') {
                // El viticulturist queda en el pool del supervisor
                // Se asignará a winery cuando corresponda
            }

            // Si winery crea viticulturist
            if ($creator->isWinery() && $this->role === 'viticulturist') {
                WineryViticulturist::create([
                    'winery_id' => $creator->id,
                    'viticulturist_id' => $user->id,
                    'source' => 'own',
                    'assigned_by' => $creator->id,
                ]);
            }

            // Si viticultor crea viticultor
            if ($creator->isViticulturist() && $this->role === 'viticulturist') {
                // Obtener winery del viticultor creador (si tiene)
                $creatorWinery = $creator->wineries->first();
                
                WineryViticulturist::create([
                    'winery_id' => $creatorWinery?->id, // Puede ser NULL si no tiene winery
                    'viticulturist_id' => $user->id,
                    'source' => WineryViticulturist::SOURCE_VITICULTURIST,
                    'parent_viticulturist_id' => $creator->id,
                    'assigned_by' => $creator->id,
                ]);
            }

            // Enviar email de verificación cuando un usuario autenticado crea otro usuario
            $user->sendEmailVerificationNotification();

            session()->flash('message', 'Usuario creado correctamente. Se ha enviado un email de verificación.');
            return $this->redirect(route($this->getRedirectRoute()), navigate: true);
        }

        // Para registro público: enviar email de verificación
        $user->sendEmailVerificationNotification();
        
        Auth::login($user);
        session()->regenerate();
        session()->flash('message', 'Registro exitoso. Por favor, verifica tu email antes de continuar.');

        return $this->redirect(route('verification.notice'), navigate: true);
    }

    protected function getDashboardRoute(): string
    {
        $user = Auth::user();
        
        return match($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            default => 'home',
        };
    }

    public function getRedirectRoute(): string
    {
        $user = Auth::user();
        
        // Redirigir según quién creó el usuario
        return match($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.personal.index', // Redirigir a Personal después de crear viticultor
            default => 'home',
        };
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app');
    }
}
