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
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasSubscriptions, HasBetaAccess, HasHierarchy, HasInvoicing;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'apple_id',
        'password',
        'role',
        'dni',
        'password_must_reset',
        'can_login',
        'email_verified_at',
        'invitation_sent_at',
        'invitation_token',
        'invitation_expires_at',
        'activated_at',
        'is_beta_user',
        'beta_ends_at',
        'beta_access_granted',
        'compra_uva_externa',
        'organization_id',
        'locale',
        'preferences',
        'notification_preferences',
        'last_login_at',
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
            'invitation_sent_at'   => 'datetime',
            'invitation_expires_at' => 'datetime',
            'activated_at'          => 'datetime',
            'is_beta_user' => 'boolean',
            'beta_ends_at' => 'datetime',
            'beta_access_granted' => 'boolean',
            'compra_uva_externa' => 'boolean',
            'preferences' => 'array',
            'notification_preferences' => 'array',
            'last_login_at' => 'datetime',
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
     * Roles disponibles
     */
    public const ROLE_ADMIN              = 'admin';
    public const ROLE_SUPERVISOR         = 'supervisor';
    public const ROLE_WINERY             = 'winery';
    public const ROLE_VITICULTURIST      = 'viticulturist';
    public const ROLE_PRODUCER           = 'producer';
    // ROLE_DO is an alias for ROLE_SUPERVISOR — denomination_of_origin uses 'supervisor' in DB
    public const ROLE_DO                 = 'supervisor';

    /** Patrones de email que identifican cuentas internas/demo/test. */
    public const INTERNAL_EMAIL_PATTERNS = [
        'demo', 'test', '@noemail.agro365.es',
        'bernalmochonjorge', 'bernhshsj', 'jorgemochonb',
    ];

    /** Patrones de nombre que identifican cuentas internas/demo/test. */
    public const INTERNAL_NAME_PATTERNS = ['demo', 'test', 'maestro'];

    /**
     * Scope: excluir usuarios demo/test/placeholder/internos.
     */
    public function scopeExcludeDemo($query)
    {
        return $query->where(function ($q) {
            foreach (self::INTERNAL_EMAIL_PATTERNS as $pattern) {
                $q->where('email', 'not like', "%{$pattern}%");
            }
            foreach (self::INTERNAL_NAME_PATTERNS as $pattern) {
                $q->where('name', 'not like', "%{$pattern}%");
            }
        });
    }

    /**
     * Indica si esta cuenta es interna (demo/test/placeholder).
     */
    public function isInternal(): bool
    {
        foreach (self::INTERNAL_EMAIL_PATTERNS as $pattern) {
            if (str_contains(strtolower($this->email), strtolower($pattern))) {
                return true;
            }
        }
        foreach (self::INTERNAL_NAME_PATTERNS as $pattern) {
            if (str_contains(strtolower($this->name), strtolower($pattern))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Admin de solo lectura (no puede modificar datos)
     */
    public function isReadOnlyAdmin(): bool
    {
        return $this->isAdmin() && (bool) $this->is_readonly_admin;
    }

    /**
     * Verificar si el usuario es supervisor / denomination of origin
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
     * Organización a la que pertenece este usuario (winery/DO).
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Comprueba si la bodega tiene una ability concreta habilitada.
     *
     * Retrocompatibilidad: si la bodega no tiene NINGUNA ability configurada
     * (supervisor no ha establecido restricciones) devuelve TRUE para todo,
     * de modo que el acceso existente no se rompe.
     */
    public function hasAbility(string $code): bool
    {
        if (! $this->hasWineryAccess()) {
            return false;
        }

        $granted = $this->abilities()->pluck('code');

        // Sin restricciones configuradas → acceso total (retrocompatible)
        if ($granted->isEmpty()) {
            return true;
        }

        return $granted->contains($code);
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class, 'user_abilities')
            ->withPivot('granted_by', 'granted_at')
            ->withTimestamps();
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

    // ── Preferences ──────────────────────────────────────────────────────────

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences ?? [], $key, $default);
    }

    public function setPreference(string $key, mixed $value): void
    {
        $prefs = $this->preferences ?? [];
        data_set($prefs, $key, $value);
        $this->update(['preferences' => $prefs]);
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

