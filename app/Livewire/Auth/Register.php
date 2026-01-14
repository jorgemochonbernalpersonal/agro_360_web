<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends Component
{
    use WithToastNotifications;
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
        } else {
            // Prefijar email desde enlace de invitación, si viene en la URL
            if (request()->has('email')) {
                $this->email = request()->query('email');
            }
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            // La unicidad se gestionará manualmente en register() para permitir activar viticultores pre-creados
            'email' => 'required|string|email|max:255',
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

    protected function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.confirmed' => 'Las contraseñas no coinciden. Por favor, verifica que ambas contraseñas sean iguales.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'Debes seleccionar un rol.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
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
        // Honeypot: Si está lleno, es un bot
        if (!empty($this->honeypot)) {
            \App\Services\SecurityLogger::logSecurityEvent('honeypot_triggered_register', [
                'email' => $this->email,
                'name' => $this->name,
                'honeypot_value' => substr($this->honeypot, 0, 50),
            ]);
            
            // Simular éxito para confundir al bot
            sleep(2);
            $this->toastSuccess('Registro completado. Revisa tu email para verificar tu cuenta.');
            return;
        }
        
        $this->validate();

        $existing = User::where('email', $this->email)->first();

        // Registro público (sin usuario autenticado): puede ser alta nueva o activación de viticultor pre-creado
        if (!Auth::check()) {
            if ($existing) {
                // Activar viticultor que fue creado previamente sin acceso (can_login = false)
                if ($existing->role === User::ROLE_VITICULTURIST && $existing->can_login === false) {
                    // Actualizar el usuario en la base de datos
                    $existing->update([
                        'name' => $this->name,
                        'password' => Hash::make($this->password),
                        'can_login' => true,
                        'password_must_reset' => false,
                        // NO marcamos email_verified_at - el usuario debe verificar desde el email
                    ]);

                    // Loguear al usuario
                    Auth::login($existing);
                    session()->regenerate();
                    
                    // Enviar email de verificación después del registro
                    $existing->sendEmailVerificationNotification();
                    
                    $this->toastSuccess('Cuenta activada correctamente. Se ha enviado un email de verificación. Por favor, verifica tu email antes de continuar.');

                    // Redirigir a la página de verificación de email
                    return $this->redirect(route('verification.notice'), navigate: true);
                }

                // Cualquier otro caso: email ya usado por una cuenta activa
                $this->addError('email', 'Este email ya está registrado.');
                return;
            }
        } else {
            // Creación interna (admin/supervisor/winery/viticultor): no permitir reutilizar emails
            if ($existing) {
                $this->addError('email', 'Este email ya está registrado.');
                return;
            }
        }

        // Detectar si viticultor esta creando otro viticultor (requiere password temporal)
        $isViticulturistCreatingViticulturist = Auth::check() 
            && Auth::user()->isViticulturist() 
            && $this->role === 'viticulturist';
        
        // Generar contraseña temporal si es necesario
        $temporaryPassword = null;
        if ($isViticulturistCreatingViticulturist) {
            $temporaryPassword = \Illuminate\Support\Str::random(12);
            $password = Hash::make($temporaryPassword);
        } else {
            $password = Hash::make($this->password);
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $password,
            'role' => $this->role,
            'password_must_reset' => $isViticulturistCreatingViticulturist,
            'can_login' => true,
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

            // Enviar email según el tipo de usuario creado
            if ($isViticulturistCreatingViticulturist) {
                // Generar PDF con credenciales
                $pdf = \PDF::loadView('pdf.credentials', [
                    'email' => $user->email,
                    'password' => $temporaryPassword,
                    'created_at' => now()->format('d/m/Y H:i'),
                ]);
                
                // Guardar PDF temporalmente
                $pdfPath = storage_path('app/temp/credentials_' . $user->id . '_' . time() . '.pdf');
                if (!file_exists(dirname($pdfPath))) {
                    mkdir(dirname($pdfPath), 0755, true);
                }
                $pdf->save($pdfPath);
                
                // Enviar email con PDF adjunto
                $user->notify(new \App\Notifications\TemporaryPasswordNotification($temporaryPassword, $pdfPath));
                
                $this->toastSuccess('Viticultor creado correctamente. Se ha enviado un email con las credenciales de acceso.');
                session()->flash('pdf_download', base64_encode($pdf->output()));
                session()->flash('pdf_filename', 'credenciales_' . str_replace(['@', '.'], '_', $user->email) . '.pdf');
                
                // Eliminar PDF temp después de 1 minuto (dar tiempo a enviar email)
                dispatch(function() use ($pdfPath) {
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                })->delay(now()->addMinute());
            } else {
                // Enviar email de verificación tradicional
                $user->sendEmailVerificationNotification();
                $this->toastSuccess('Usuario creado correctamente. Se ha enviado un email de verificación.');
            }

            return $this->redirect(route($this->getRedirectRoute()), navigate: true);
        }

        // Para registro público: enviar email de verificación y activar beta
        $user->sendEmailVerificationNotification();
        
        // Activar acceso beta (6 meses gratis)
        $user->grantBetaAccess();
        
        // Si es viticultor, crear registro en WineryViticulturist para que aparezca en listas
        if ($this->role === 'viticulturist') {
            try {
                WineryViticulturist::create([
                    'winery_id' => null, // Viticultor independiente
                    'viticulturist_id' => $user->id,
                    'source' => WineryViticulturist::SOURCE_SELF, // Se registró él mismo
                    'parent_viticulturist_id' => null,
                    'assigned_by' => $user->id, // Se asignó a sí mismo
                ]);
                
                \Illuminate\Support\Facades\Log::info('WineryViticulturist created during public registration', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to create WineryViticulturist during registration', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Auth::login($user);
        session()->regenerate();
        $this->toastSuccess('Registro exitoso. Por favor, verifica tu email antes de continuar.');

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
