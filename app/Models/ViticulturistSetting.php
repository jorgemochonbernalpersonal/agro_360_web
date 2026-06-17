<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViticulturistSetting extends Model
{
    protected $fillable = [
        'viticulturist_id',
        'default_limit_kg_per_ha',
        'degree_day_base',
        'document_prefix_activity',
        'document_prefix_harvest',
        'legal_text_fieldbook',
        'notify_harvest_alerts',
        'notify_activity_alerts',
        'default_irpf_rate',
    ];

    protected $casts = [
        'default_limit_kg_per_ha' => 'decimal:3',
        'degree_day_base' => 'decimal:1',
        'notify_harvest_alerts' => 'boolean',
        'notify_activity_alerts' => 'boolean',
        'default_irpf_rate' => 'decimal:2',
    ];

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    // -------------------------------------------------------
    // Helpers estáticos (misma interfaz que InvoicingSetting)
    // -------------------------------------------------------

    public static function forUser(int $userId): ?self
    {
        return static::where('viticulturist_id', $userId)->first();
    }

    public static function createDefaultForUser(int $userId): self
    {
        return static::create([
            'viticulturist_id' => $userId,
            'degree_day_base' => 10.0,
            'document_prefix_activity' => 'ACT',
            'document_prefix_harvest' => 'VND',
            'notify_harvest_alerts' => true,
            'notify_activity_alerts' => true,
        ]);
    }
}
