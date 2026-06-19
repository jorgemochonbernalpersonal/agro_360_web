<?php

namespace App\Models;

use App\Models\AgriculturalActivity;
use App\Models\Traits\HasBetaAccess;
use App\Models\Traits\HasHierarchy;
use App\Models\Traits\HasInvoicing;
use App\Models\Traits\HasSubscriptions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property array<string, mixed>|null $notification_preferences
 * @property array<string, mixed>|null $preferences
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $invitation_sent_at
 * @property \Illuminate\Support\Carbon|null $invitation_expires_at
 * @property \Illuminate\Support\Carbon|null $beta_ends_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property mixed $total
 * @property mixed $role_admin
 * @property mixed $role_supervisor
 * @property mixed $role_winery
 * @property mixed $role_viticulturist
 * @property mixed $role_producer
 * @property mixed $active
 * @property mixed $inactive
 * @property mixed $verified
 * @property mixed $unverified
 * @property mixed $beta_active
 * @property mixed $beta_expired
 * @property mixed $can_edit
 * @property mixed $activeSubscription
 * @property mixed $adminNotes
 */
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasBetaAccess, HasHierarchy, HasInvoicing, HasSubscriptions;

    /**
     * Roles disponibles
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_WINERY = 'winery';

    public const ROLE_VITICULTURIST = 'viticulturist';

    public const ROLE_PRODUCER = 'producer';

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
        'abilities_configured',
        'email_verified_at',
        'invitation_sent_at',
        'invitation_token',
        'invitation_expires_at',
        'activated_at',
        'is_beta_user',
        'beta_ends_at',
        'beta_access_granted',
        'is_founder',
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

    public function preferredLocale(): string
    {
        return $this->locale ?? 'es';
    }

    /**
     * Enviar notificación de verificación de email personalizada según rol.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
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
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Comprueba si la bodega tiene una ability concreta habilitada.
     *
     * Retrocompatibilidad: mientras la DO no haya configurado la bodega
     * (abilities_configured = false) devuelve TRUE para todo. Una vez configurada,
     * el set de abilities es vinculante: un set vacío significa "ningún módulo",
     * no "todos".
     */
    public function hasAbility(string $code): bool
    {
        return in_array($code, $this->effectiveAbilityCodes(), true);
    }

    /**
     * Conjunto efectivo de ability codes del usuario (la misma lógica que [hasAbility],
     * pero materializada como lista). Fuente única de verdad para autorización y para
     * exponer las abilities al cliente (UserResource → app móvil).
     *
     * - Roles sin acceso a bodega → lo que cubra su plan.
     * - Bodega sin configurar por la DO → lo que cubra su plan (retrocompatible).
     * - Bodega configurada por la DO → plan ∩ overrides concedidos por la DO.
     *
     * @return array<int, string>
     */
    public function effectiveAbilityCodes(): array
    {
        $plan = $this->planAbilities();

        if (! $this->hasWineryAccess() || ! $this->abilities_configured) {
            return array_values($plan);
        }

        $granted = $this->abilities()->pluck('code')->all();

        return array_values(array_intersect($plan, $granted));
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class, 'user_abilities')
            ->withPivot('granted_by', 'granted_at')
            ->withTimestamps();
    }

    /**
     * Perfil del usuario
     *
     * @return HasOne<UserProfile, $this>
     */
    public function profile(): HasOne
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
     *
     * @return HasMany<Plot, $this>
     */
    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class, 'viticulturist_id');
    }

    /** @return HasMany<AgriculturalActivity, $this> */
    public function agriculturalActivities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'viticulturist_id');
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
            'abilities_configured' => 'boolean',
            'invitation_sent_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'is_beta_user' => 'boolean',
            'beta_ends_at' => 'datetime',
            'beta_access_granted' => 'boolean',
            'is_founder' => 'boolean',
            'compra_uva_externa' => 'boolean',
            'preferences' => 'array',
            'notification_preferences' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Boot del modelo - limpiar cache cuando se actualiza
     */
    protected static function booted()
    {
        // Limpiar propiedades de cache antes de guardar
        static::saving(function ($user) {
            $user->_wineries_cache = null;
            $user->_supervisor_cache = null;
            $user->_was_created_by_another_cache = null;
            $user->_needs_password_change_cache = null;
        });

        static::saved(function ($user) {
            $user->clearAttributeCache();
            // Limpiar cache de sesión cuando se actualiza el usuario
            if ($user->wasChanged(['email_verified_at', 'password'])) {
                session()->forget("user_{$user->id}_needs_password_change");
            }
        });

        static::deleting(function ($user) {
            // Al eliminar una cuenta de DO (supervisor), enrutamos el borrado de
            // sus vínculos con bodegas por Eloquent para que SupervisorWinery::deleting
            // se dispare: convierte los viticultores asignados a 'own' y devuelve cada
            // bodega supervisada a estado independiente (acceso total). Un $user->delete()
            // dejaría que la cascada de BD eliminara esas filas en silencio, dejando las
            // bodegas congeladas en las restricciones de la DO ya eliminada.
            if ($user->isSupervisor()) {
                SupervisorWinery::where('supervisor_id', $user->id)
                    ->get()
                    ->each
                    ->delete();
            }
        });
    }
}
