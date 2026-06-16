<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    public const TYPE_WINERY = 'winery';

    public const TYPE_DENOMINATION = 'denomination_of_origin';

    public const TYPES = [
        self::TYPE_WINERY => 'Bodega',
        self::TYPE_DENOMINATION => 'Denominación de Origen',
    ];

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

    public static function typeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TYPES);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /** @return BelongsTo<Organization, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /** @return HasMany<Organization, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    /** @return BelongsTo<Province, $this> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /** Usuario principal que representa esta organización (el user con role winery/DO). */
    /** @return BelongsTo<User, $this> */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** Todos los usuarios miembros de esta organización. */
    /** @return HasMany<User, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

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
        return __(self::TYPES[$this->type] ?? $this->type);
    }
}
