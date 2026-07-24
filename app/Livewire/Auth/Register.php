<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    use WithToastNotifications;

    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $dni = '';

    public $role = 'viticulturist'; // Por defecto

    public $winery_id = ''; // Si lo crea un winery

    public $supervisor_id = ''; // Si lo crea un supervisor

    public $honeypot = ''; // Honeypot anti-bots

    public $founder_code = ''; // Código secreto del programa de fundadores (llega por URL, no se muestra)

    public function mount()
    {
        // Si está autenticado, determinar rol por defecto según quién crea
        if (Auth::check()) {
            $user = Auth::user();
            $this->role = match ($user->role) {
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
            // Pre-seleccionar rol desde query param (botones de la landing page)
            if (request()->has('role')) {
                $requested = request()->query('role');
                if (in_array($requested, ['viticulturist', 'winery', 'producer'])) {
                    $this->role = $requested;
                }
            }
            // Código de fundador (llega vía URL enviada por el equipo por WhatsApp)
            if (request()->has('founder_code')) {
                $this->founder_code = (string) request()->query('founder_code');
            }
        }
    }

    public function getAllowedRoles(?User $user = null): array
    {
        if (! $user) {
            return ['viticulturist', 'winery', 'producer']; // Registro público (supervisor solo por invitación/admin)
        }

        return match ($user->role) {
            'admin' => ['admin', 'supervisor', 'winery', 'viticulturist', 'producer'],
            'supervisor' => ['winery', 'viticulturist', 'producer'],
            'winery' => ['viticulturist'],
            'viticulturist' => ['viticulturist'],
            default => [],
        };
    }

    public function register()
    {
        if (! empty($this->honeypot)) {
            \App\Services\SecurityLogger::logSecurityEvent('honeypot_triggered_register', [
                'email' => $this->email,
                'name' => $this->name,
                'honeypot_value' => substr($this->honeypot, 0, 50),
            ]);
            $this->toastSuccess(__('Registro completado. Revisa tu email para verificar tu cuenta.'));

            return;
        }

        $this->validate();

        $this->email = strtolower(trim($this->email));
        $normalizedDni = $this->dni ? strtoupper(preg_replace('/\s+/', '', $this->dni)) : null;
        $existing = User::where('email', $this->email)->first();
        $service = app(UserRegistrationService::class);

        if (! Auth::check()) {
            if ($existing) {
                // Activar viticultor ghost creado previamente sin acceso (can_login = false)
                if ($existing->role === User::ROLE_VITICULTURIST && $existing->can_login === false) {
                    $ghostEmailMatches = strtolower($existing->email) === strtolower($this->email);
                    $activated = $service->activateGhostByEmail($existing, $this->name, $this->password, $normalizedDni, $this->email);

                    if (! $ghostEmailMatches) {
                        $activated->sendEmailVerificationNotification();
                    }

                    Auth::login($activated);
                    session()->regenerate();
                    $activated->update(['last_login_at' => now()]);
                    $this->toastSuccess(__('Cuenta activada correctamente. ¡Bienvenido a Agro365!'));

                    return $this->redirect(route($ghostEmailMatches ? 'viticulturist.dashboard' : 'verification.notice'), navigate: true);
                }

                $this->addError('email', __('Este email ya está registrado.'));

                return;
            }

            // DNI merge: ghost sin email coincidente pero con DNI registrado por la bodega
            if ($normalizedDni && $this->role === 'viticulturist') {
                $merged = $service->mergeGhostByDni($normalizedDni, $this->email, $this->name, $this->password);

                if ($merged instanceof User) {
                    Auth::login($merged);
                    session()->regenerate();
                    $merged->update(['last_login_at' => now()]);
                    $this->toastSuccess(__('¡Cuenta vinculada! Tu bodega ya tenía tus datos registrados. Bienvenido a Agro365.'));

                    return $this->redirect(route($merged->email_verified_at ? 'viticulturist.dashboard' : 'verification.notice'), navigate: true);
                }

                if ($merged === 'email_taken') {
                    $this->addError('email', __('Este email ya está registrado.'));

                    return;
                }
                // null → no ghost found, continue with normal registration
            }

            if ($normalizedDni) {
                if (User::where('dni', $normalizedDni)->where('can_login', true)->exists()) {
                    $this->addError('dni', __('Este DNI ya está registrado en el sistema.'));

                    return;
                }
            }
        } else {
            if ($existing) {
                $this->addError('email', __('Este email ya está registrado.'));

                return;
            }
        }

        $isViticulturistCreatingViticulturist = Auth::check()
            && Auth::user()->hasViticulturistAccess()
            && $this->role === 'viticulturist';

        $temporaryPassword = null;
        if ($isViticulturistCreatingViticulturist) {
            $temporaryPassword = \Illuminate\Support\Str::random(12);
            $password = Hash::make($temporaryPassword);
        } else {
            $password = Hash::make($this->password);
        }

        $isFounder = ! Auth::check() && $service->isFounder($this->founder_code);

        try {
            $user = $service->createUserWithRelationships([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $password,
                'role' => $this->role,
                'dni' => $normalizedDni ?? null,
                'password_must_reset' => $isViticulturistCreatingViticulturist,
                'can_login' => true,
                'is_founder' => $isFounder,
            ], Auth::check() ? Auth::user() : null);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'users_email_unique')) {
                $this->addError('email', __('Este email ya está registrado.'));

                return;
            }
            if (str_contains($e->getMessage(), 'users_dni_unique')) {
                $this->addError('dni', __('Este DNI ya está registrado en el sistema.'));

                return;
            }
            throw $e;
        }

        // Enviar emails FUERA de la transacción (no deben hacer rollback)
        if (Auth::check()) {
            if ($isViticulturistCreatingViticulturist) {
                $pdf = \PDF::loadView('pdf.credentials', [
                    'email' => $user->email,
                    'password' => $temporaryPassword,
                    'created_at' => now()->format('d/m/Y H:i'),
                ]);
                $pdfContent = $pdf->output();

                $user->notify(new \App\Notifications\TemporaryPasswordNotification($temporaryPassword, $pdfContent));
                $this->toastSuccess(__('Viticultor creado correctamente. Se ha enviado un email con las credenciales de acceso.'));
                session()->flash('pdf_download', base64_encode($pdfContent));
                session()->flash('pdf_filename', 'credenciales_'.str_replace(['@', '.'], '_', $user->email).'.pdf');
            } else {
                $user->sendEmailVerificationNotification();
                $this->toastSuccess(__('Usuario creado correctamente. Se ha enviado un email de verificación.'));
            }

            return $this->redirect(route($this->getRedirectRoute()), navigate: true);
        }

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        session()->regenerate();
        $user->update(['last_login_at' => now()]);
        $this->toastSuccess(__('¡Bienvenido a Agro365! Revisa tu email para verificar tu cuenta.'));

        return $this->redirect(route('verification.notice'), navigate: true);
    }

    public function getRedirectRoute(): string
    {
        $user = Auth::user();

        // Redirigir según quién creó el usuario
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.personal.index', // Redirigir a Personal después de crear viticultor
            'producer' => 'producer.dashboard',
            default => 'home',
        };
    }

    public function render()
    {
        return view('livewire.auth.register')->layout(\Illuminate\Support\Facades\Auth::check() ? 'layouts.app' : 'layouts.guest');
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            // La unicidad se gestionará manualmente en register() para permitir activar viticultores pre-creados
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/'],
        ];

        // Si está autenticado, puede seleccionar rol
        if (Auth::check()) {
            $user = Auth::user();
            $allowedRoles = $this->getAllowedRoles($user);
            $rules['role'] = 'required|in:'.implode(',', $allowedRoles);
        } else {
            // Registro público: viticulturist, winery, producer (supervisor solo por invitación/admin)
            $rules['role'] = 'required|in:viticulturist,winery,producer';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El campo nombre es obligatorio.'),
            'name.max' => __('El nombre no puede tener más de 255 caracteres.'),
            'email.required' => __('El campo email es obligatorio.'),
            'email.email' => __('El email debe ser una dirección de correo válida.'),
            'email.max' => __('El email no puede tener más de 255 caracteres.'),
            'password.required' => __('El campo contraseña es obligatorio.'),
            'password.confirmed' => __('Las contraseñas no coinciden. Por favor, verifica que ambas contraseñas sean iguales.'),
            'password.min' => __('La contraseña debe tener al menos 8 caracteres.'),
            'role.required' => __('Debes seleccionar un rol.'),
            'role.in' => __('El rol seleccionado no es válido.'),
            'dni.max' => __('El DNI no puede tener más de 20 caracteres.'),
            'dni.regex' => __('El DNI solo puede contener letras, números y guiones.'),
        ];
    }

    protected function getDashboardRoute(): string
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            'producer' => 'producer.dashboard',
            default => 'home',
        };
    }

}
