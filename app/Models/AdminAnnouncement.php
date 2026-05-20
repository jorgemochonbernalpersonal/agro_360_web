<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAnnouncement extends Model
{
    protected $fillable = [
        'admin_id', 'title', 'message', 'type', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function typeColor(): string
    {
        return match($this->type) {
            'warning' => 'amber',
            'success' => 'green',
            'danger'  => 'red',
            default   => 'blue',
        };
    }

    public function typeIcon(): string
    {
        return match($this->type) {
            'warning' => 'exclamation-triangle',
            'success' => 'check-circle',
            'danger'  => 'x-circle',
            default   => 'information-circle',
        };
    }
}
