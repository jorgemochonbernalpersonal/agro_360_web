<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'password_must_reset',
        'can_login',
        'invitation_sent_at',
        'is_beta_user',
        'beta_ends_at',
        'beta_access_granted',
    ];

    /**
     * Cache properties (not stored in database)
     */
    protected $_wineries_cache;
    protected $_supervisor_cache;
    protected $_was_created_by_another_cache;
    protected $_needs_password_change_cache;

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
            'is_beta_user' => 'boolean',
            'beta_ends_at' => 'datetime',
            'beta_access_granted' => 'boolean',
        ];
    }

    /**
     * Roles disponibles
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_WINERY = 'winery';
    public const ROLE_VITICULTURIST = 'viticulturist';

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

    // Relaciones como Supervisor
    public function supervisedWineries()
    {
        return $this->hasMany(SupervisorWinery::class, 'supervisor_id');
    }

    public function supervisedViticulturists()
    {
        return $this->hasMany(SupervisorViticulturist::class, 'supervisor_id');
    }

    // Relaciones como Winery
    public function supervisorRelations()
    {
        return $this->hasMany(SupervisorWinery::class, 'winery_id');
    }

    public function wineryViticulturists()
    {
        return $this->hasMany(WineryViticulturist::class, 'winery_id');
    }

    public function crews()
    {
        return $this->hasMany(Crew::class, 'winery_id');
    }

    // Relaciones como Viticulturist
    public function supervisorRelationsAsViticulturist()
    {
        return $this->hasMany(SupervisorViticulturist::class, 'viticulturist_id');
    }

    public function wineryRelationsAsViticulturist()
    {
        return $this->hasMany(WineryViticulturist::class, 'viticulturist_id');
    }

    public function ledCrews()
    {
        return $this->hasMany(Crew::class, 'viticulturist_id');
    }

    public function crewMemberships()
    {
        return $this->hasMany(CrewMember::class, 'viticulturist_id');
    }

    // Relaciones de perfil y suscripciones
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Relaciones de facturación
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceGroups()
    {
        return $this->hasMany(InvoiceGroup::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'user_taxes')
            ->withPivot('is_default', 'order')
            ->withTimestamps();
    }

    public function defaultTax()
    {
        return $this->belongsToMany(Tax::class, 'user_taxes')
            ->wherePivot('is_default', true)
            ->withPivot('is_default', 'order')
            ->withTimestamps();
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
     * Verificar si el usuario tiene una suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    // ============ BETA ACCESS METHODS ============
    
    /**
     * Verificar si el usuario es usuario beta
     */
    public function isBetaUser(): bool
    {
        return $this->is_beta_user === true;
    }

    /**
     * Verificar si la beta ha expirado
     */
    public function betaExpired(): bool
    {
        return $this->is_beta_user 
            && $this->beta_ends_at 
            && $this->beta_ends_at->isPast()
            && !$this->hasActiveSubscription();
    }

    /**
     * Obtener días restantes de beta
     */
    public function betaDaysRemaining(): int
    {
        if (!$this->isBetaUser() || !$this->beta_ends_at) {
            return 0;
        }
        
        if ($this->beta_ends_at->isPast()) {
            return 0;
        }
        
        return (int) now()->diffInDays($this->beta_ends_at, false);
    }

    /**
     * Activar acceso beta (hasta 30/06/2026)
     */
    public function grantBetaAccess(): void
    {
        $this->update([
            'is_beta_user' => true,
            'beta_ends_at' => \Carbon\Carbon::parse('2026-06-30 23:59:59'),
            'beta_access_granted' => true,
        ]);
    }

    /**
     * Verificar si tiene acceso activo (beta o suscripción)
     */
    public function hasActiveAccess(): bool
    {
        // Beta activo
        if ($this->isBetaUser() && !$this->betaExpired()) {
            return true;
        }
        
        // Suscripción activa
        if ($this->hasActiveSubscription()) {
            return true;
        }
        
        return false;
    }

    /**
     * Trabajadores individuales gestionados por este viticultor
     */
    public function individualWorkers()
    {
        return $this->hasMany(CrewMember::class, 'assigned_by')
                    ->whereNull('crew_id');
    }

    /**
     * Obtener el supervisor del viticultor (desde WineryViticulturist)
     * Busca en WineryViticulturist con source = 'supervisor'
     * ✅ OPTIMIZADO: Usa Laravel Cache en lugar de propiedades privadas
     */
    public function getSupervisorAttribute(): ?User
    {
        if (!$this->isViticulturist()) {
            return null;
        }
        
        // ✅ OPTIMIZACIÓN: Cache persistente con TTL de 1 hora
        // Cache en memoria durante la request como fallback
        if (!isset($this->_supervisor_cache)) {
            $cacheKey = "user_{$this->id}_supervisor";
            
            $this->_supervisor_cache = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
                $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->id)
                    ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                    ->whereNotNull('supervisor_id')
                    ->with('supervisor')
                    ->first();
                
                return $wineryRelation?->supervisor;
            });
        }
        
        return $this->_supervisor_cache;
    }

    /**
     * Verificar si tiene supervisor
     */
    public function hasSupervisor(): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereNotNull('supervisor_id')
            ->exists();
    }

    /**
     * Obtener las wineries del viticultor (usando relación existente)
     * ✅ OPTIMIZADO: Usa Laravel Cache en lugar de propiedades privadas
     */
    public function getWineriesAttribute()
    {
        if (!$this->isViticulturist()) {
            return collect();
        }
        
        // ✅ OPTIMIZACIÓN: Cache persistente con TTL de 1 hora
        // Cache en memoria durante la request como fallback
        if (!isset($this->_wineries_cache)) {
            $cacheKey = "user_{$this->id}_wineries";
            
            $this->_wineries_cache = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
                // Usar query directa en lugar de la relación para evitar problemas con scopes
                $wineryIds = \App\Models\WineryViticulturist::where('viticulturist_id', $this->id)
                    ->whereNotNull('winery_id')
                    ->pluck('winery_id')
                    ->unique();
                
                if ($wineryIds->isEmpty()) {
                    return collect();
                }
                
                return User::whereIn('id', $wineryIds)
                    ->where('role', self::ROLE_WINERY)
                    ->select(['id', 'name', 'email', 'role']) // ✅ Solo campos necesarios
                    ->get();
            });
        }
        
        return $this->_wineries_cache;
    }

    /**
     * Verificar si tiene winery
     */
    public function hasWinery(): bool
    {
        return $this->isViticulturist() && $this->wineryRelationsAsViticulturist()->exists();
    }

    /**
     * Verificar si puede editar un viticultor (solo los que creó)
     */
    public function canEditViticulturist($viticulturistId): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->exists();
    }

    /**
     * Verificar si el usuario puede seleccionar viticultores
     * Admin, supervisor y winery siempre pueden seleccionar
     * Viticultores solo pueden seleccionar si tienen viticultores creados
     */
    public function canSelectViticulturist(): bool
    {
        // Admin, supervisor y winery siempre pueden seleccionar
        if (in_array($this->role, ['admin', 'supervisor', 'winery'])) {
            return true;
        }
        
        // Viticultores solo pueden seleccionar si tienen viticultores creados
        if ($this->isViticulturist()) {
            return WineryViticulturist::editableBy($this)->exists();
        }
        
        return false;
    }

    /**
     * Verificar si este viticultor fue creado por otro viticultor
     */
    public function wasCreatedByViticulturist(): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->whereNotNull('parent_viticulturist_id')
            ->exists();
    }

    /**
     * Verificar si este usuario fue creado por otro usuario (viticultor, winery o supervisor)
     * Esto indica que tiene una contraseña temporal y debe cambiarla
     * Cacheado para evitar queries repetidas en cada request
     */
    public function wasCreatedByAnotherUser(): bool
    {
        // Cachear en memoria durante la request
        if (!isset($this->_was_created_by_another_cache)) {
            $result = false;
            
            // Si es viticultor, verificar si fue creado por otro usuario
            if ($this->isViticulturist()) {
                $result = WineryViticulturist::where('viticulturist_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->whereIn('source', [
                        WineryViticulturist::SOURCE_VITICULTURIST,
                        WineryViticulturist::SOURCE_OWN,
                        WineryViticulturist::SOURCE_SUPERVISOR
                    ])
                    ->exists();
            }
            // Si es winery, verificar si fue creado por supervisor
            elseif ($this->isWinery()) {
                $result = \App\Models\SupervisorWinery::where('winery_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->exists();
            }
            
            $this->_was_created_by_another_cache = $result;
        }
        
        return $this->_was_created_by_another_cache;
    }

    /**
     * Verificar si el email está verificado
     * Si fue creado por otro usuario y no ha verificado email, necesita cambiar contraseña
     * Cacheado para evitar queries repetidas en cada request
     */
    public function needsPasswordChange(): bool
    {
        // Cachear en memoria durante la request
        if (!isset($this->_needs_password_change_cache)) {
            $this->_needs_password_change_cache = $this->wasCreatedByAnotherUser() && !$this->hasVerifiedEmail();
        }
        
        return $this->_needs_password_change_cache;
    }

    /**
     * Limpiar cache de atributos calculados
     * Útil cuando se actualiza el usuario (verificación de email, cambio de contraseña, etc.)
     * ✅ OPTIMIZADO: También limpia cache de Laravel
     */
    public function clearAttributeCache(): void
    {
        // Limpiar cache en memoria
        unset($this->_wineries_cache);
        unset($this->_supervisor_cache);
        unset($this->_was_created_by_another_cache);
        unset($this->_needs_password_change_cache);
        
        // ✅ Limpiar cache persistente de Laravel
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_supervisor");
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_wineries");
    }

    /**
     * Boot del modelo - limpiar cache cuando se actualiza
     */
    protected static function booted()
    {
        // Limpiar propiedades de cache antes de guardar para evitar que se guarden en la BD
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
