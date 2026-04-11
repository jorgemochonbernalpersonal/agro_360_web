<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotAlertPreference extends Model
{
    protected $fillable = [
        'plot_id',
        'user_id',
        'ndvi_threshold',
        'email_enabled',
    ];

    protected $casts = [
        'ndvi_threshold' => 'float',
        'email_enabled'  => 'boolean',
    ];

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or instantiate (without saving) a preference for the given user + plot.
     */
    public static function forUser(int $plotId, int $userId): self
    {
        return static::firstOrNew(
            ['plot_id' => $plotId, 'user_id' => $userId],
            ['ndvi_threshold' => 0.30, 'email_enabled' => false]
        );
    }
}
