<?php

namespace App\Models;

use App\Models\Traits\HasBetaAccess;
use App\Models\Traits\HasHierarchy;
use App\Models\Traits\HasInvoicing;
use App\Models\Traits\HasSubscriptions;
use App\Models\SupportTicket;
use App\Models\UserProfile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use HasSubscriptions, HasBetaAccess, HasHierarchy, HasInvoicing;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'dni',
        'password_must_reset',
        'can_login',
        'invitation_sent_at',
        'invitation_token',
        'invitation_expires_at',
        'is_beta_user',
        'beta_ends_at',
        'beta_access_granted',
        'compra_uva_externa',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_must_reset' => 'boolean',
            'can_login' => 'boolean',
            'invitation_sent_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'is_beta_user' => 'boolean',
            'beta_ends_at' => 'datetime',
            'beta_access_granted' => 'boolean',
            'compra_uva_externa' => 'boolean',
        ];
    }

    /**
     * Enviar notificación de verificación de email personalizada según rol.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }

    /**
     * Normaliza un email eliminando el sufijo _tag de cuentas Gmail.
     * Ejemplo: bernalmochonjorge_test@gmail.com → bernalmochonjorge@gmail.com
     * Solo se usa para entrega de correo, no para almacenamiento.
     */
    public static function canonicalizeEmail(string $email): string
    {
        $email = strtolower(trim($email));
        // Solo para cuentas bernalmochonjorge_xxx@gmail.com → bernalmochonjorge@gmail.com
        return preg_replace('/^(bernalmochonjorge)_[^@]+(@gmail\.com)$/', '$1$2', $email);
    }

    /**
     * Laravel usa este método para determinar la dirección de entrega de notificaciones.
     * El email se guarda tal cual en DB, pero se entrega al email canónico (sin _tag).
     */
    public function routeNotificationForMail(): string
    {
        return static::canonicalizeEmail($this->email);
    }

    /**
     * Roles disponibles
     */
    public const ROLE_ADMIN              = 'admin';
    public const ROLE_SUPERVISOR         = 'supervisor';
    public const ROLE_WINERY             = 'winery';
    public const ROLE_VITICULTURIST      = 'viticulturist';
    public const ROLE_PRODUCER           = 'producer';
    public const ROLE_DO                 = 'do';

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Verificar si el usuario es supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    /**
     * Verificar si el usuario es winery
     */
    public function isWinery(): bool
    {
        return $this->role === self::ROLE_WINERY;
    }

    /**
     * Verificar si el usuario es viticulturist
     */
    public function isViticulturist(): bool
    {
        return $this->role === self::ROLE_VITICULTURIST;
    }

    public function isProducer(): bool
    {
        return $this->role === self::ROLE_PRODUCER;
    }

    public function isDO(): bool
    {
        return $this->role === self::ROLE_DO;
    }

    public function hasViticulturistAccess(): bool
    {
        return in_array($this->role, [self::ROLE_VITICULTURIST, self::ROLE_PRODUCER]);
    }

    public function hasWineryAccess(): bool
    {
        return in_array($this->role, [self::ROLE_WINERY, self::ROLE_PRODUCER]);
    }

    /**
     * Perfil del usuario
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Tickets de soporte creados por el usuario
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Tickets de soporte asignados al usuario
     */
    public function assignedTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    /**
     * Parcelas donde el usuario es viticultor
     */
    public function plots()
    {
        return $this->hasMany(Plot::class, 'viticulturist_id');
    }

    /**
     * Campañas donde el usuario es viticultor
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'viticulturist_id');
    }

    /**
     * Boot del modelo - limpiar cache cuando se actualiza
     */
    protected static function booted()
    {
        // Limpiar propiedades de cache antes de guardar
        static::saving(function ($user) {
            unset($user->_wineries_cache);
            unset($user->_supervisor_cache);
            unset($user->_was_created_by_another_cache);
            unset($user->_needs_password_change_cache);
        });

        static::saved(function ($user) {
            $user->clearAttributeCache();
            // Limpiar cache de sesión cuando se actualiza el usuario
            if ($user->wasChanged(['email_verified_at', 'password'])) {
                session()->forget("user_{$user->id}_needs_password_change");
            }
        });
    }
}

