<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * Disable updated_at timestamp
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'ip_address',
        'user_agent',
        'url',
        'old_values',
        'new_values',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the changes made
     */
    public function getChanges(): array
    {
        if ($this->event === 'created') {
            return $this->new_values ?? [];
        }

        if ($this->event === 'deleted') {
            return $this->old_values ?? [];
        }

        // For updates, return the diff
        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        foreach ($new as $key => $value) {
            if (! isset($old[$key]) || $old[$key] !== $value) {
                $changes[$key] = [
                    'old' => $old[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get a human-readable description
     */
    public function getDescription(): string
    {
        $userName = $this->user->name ?? 'Sistema';
        $modelName = class_basename($this->auditable_type);

        return match ($this->event) {
            'created' => "{$userName} creó {$modelName} #{$this->auditable_id}",
            'updated' => "{$userName} actualizó {$modelName} #{$this->auditable_id}",
            'deleted' => "{$userName} eliminó {$modelName} #{$this->auditable_id}",
            'restored' => "{$userName} restauró {$modelName} #{$this->auditable_id}",
            default => "{$userName} realizó {$this->event} en {$modelName} #{$this->auditable_id}",
        };
    }

    /**
     * Scope for specific model
     *
     * @param mixed $query
     */
    public function scopeForModel($query, string $modelType, int $modelId)
    {
        return $query->where('auditable_type', $modelType)
            ->where('auditable_id', $modelId);
    }

    /**
     * Scope for specific user
     *
     * @param mixed $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific event
     *
     * @param mixed $query
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
