<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = [
        'name',
        'code',
        'rate',
        'region',
        'is_default',
        'active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Usuarios que tienen este impuesto disponible
     */
    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_taxes')
            ->withPivot('is_default', 'order')
            ->withTimestamps();
    }

    /**
     * Items de factura que usan este impuesto
     */
    /** @return HasMany<InvoiceItem, $this> */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope para impuestos activos
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para impuestos por defecto
     *
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope para impuestos por región
     *
     * @param mixed $query
     */
    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Obtener tasa formateada
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format((float) $this->rate, 2).'%';
    }
}
