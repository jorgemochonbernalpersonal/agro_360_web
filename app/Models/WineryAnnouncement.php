<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WineryAnnouncement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public const TYPE_INFO            = 'info';
    public const TYPE_ACTION_REQUIRED = 'action_required';
    public const TYPE_HARVEST_ALERT   = 'harvest_alert';

    public const TARGET_ALL      = 'all';
    public const TARGET_SPECIFIC = 'specific';

    public const TYPE_LABELS = [
        self::TYPE_INFO            => 'Informativo',
        self::TYPE_ACTION_REQUIRED => 'Acción requerida',
        self::TYPE_HARVEST_ALERT   => 'Alerta de vendimia',
    ];

    public static function typeLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::TYPE_LABELS);
    }

    public const TYPE_COLORS = [
        self::TYPE_INFO            => 'blue',
        self::TYPE_ACTION_REQUIRED => 'amber',
        self::TYPE_HARVEST_ALERT   => 'red',
    ];

    public const TYPE_ICONS = [
        self::TYPE_INFO            => 'information-circle',
        self::TYPE_ACTION_REQUIRED => 'exclamation-triangle',
        self::TYPE_HARVEST_ALERT   => 'bell-alert',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    public function viticulturists(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'winery_announcement_viticulturist', 'announcement_id', 'viticulturist_id')
            ->withPivot('read_at');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * Avisos visibles para un viticulturist concreto:
     * target=all de sus bodegas vinculadas, o target=specific donde esté incluido.
     */
    public function scopeVisibleTo($query, User $viticulturist)
    {
        $wineryIds = WineryViticulturist::where('viticulturist_id', $viticulturist->id)
            ->whereNotNull('winery_id')
            ->pluck('winery_id');

        return $query->whereIn('winery_id', $wineryIds)
            ->where(function ($q) use ($viticulturist) {
                $q->where('target', self::TARGET_ALL)
                  ->orWhere(function ($q2) use ($viticulturist) {
                      $q2->where('target', self::TARGET_SPECIFIC)
                         ->whereHas('viticulturists', fn ($q3) => $q3->where('users.id', $viticulturist->id));
                  });
            });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at->isPast();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function typeLabel(): string
    {
        return __(self::TYPE_LABELS[$this->type] ?? $this->type);
    }

    public function typeColor(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'zinc';
    }

    public function typeIcon(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'information-circle';
    }
}
