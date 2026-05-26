<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'slug',
        'vat_number',
        'address',
        'city',
        'postal_code',
        'province_id',
        'phone',
        'email',
        'website',
        'active',
        'owner_user_id',
        'reovi_number',
        'nidpb',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const TYPE_WINERY              = 'winery';
    public const TYPE_DENOMINATION        = 'denomination_of_origin';

    public const TYPES = [
        self::TYPE_WINERY       => __('Bodega'),
        self::TYPE_DENOMINATION => __('Denominación de Origen'),
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /** Usuario principal que representa esta organización (el user con role winery/DO). */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** Todos los usuarios miembros de esta organización. */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    /** Patrones de nombre que identifican organizaciones internas/demo/test. */
    public const INTERNAL_NAME_PATTERNS = [
        'demo', 'test', 'maestro', 'hesseng', 'moh123', 'pruebas',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    /** Excluye organizaciones internas/demo/test de los listados. */
    public function scopeExcludeInternal($query)
    {
        foreach (self::INTERNAL_NAME_PATTERNS as $pattern) {
            $query->where('name', 'not like', "%{$pattern}%");
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWineries($query)
    {
        return $query->where('type', self::TYPE_WINERY);
    }

    public function scopeDenominations($query)
    {
        return $query->where('type', self::TYPE_DENOMINATION);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isInternal(): bool
    {
        foreach (self::INTERNAL_NAME_PATTERNS as $pattern) {
            if (str_contains(strtolower($this->name), strtolower($pattern))) {
                return true;
            }
        }
        return false;
    }

    public function isWinery(): bool
    {
        return $this->type === self::TYPE_WINERY;
    }

    public function isDenomination(): bool
    {
        return $this->type === self::TYPE_DENOMINATION;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
