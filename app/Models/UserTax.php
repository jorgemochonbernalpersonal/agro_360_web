<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTax extends Model
{
    protected $table = 'user_taxes';

    protected $fillable = [
        'user_id',
        'tax_id',
        'is_default',
        'order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Impuesto
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Scope para impuesto por defecto del usuario
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
