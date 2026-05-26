<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    const CATEGORIES = [
        'grape'     => __('Proveedor de uva'),
        'packaging' => __('Envases y embalaje'),
        'chemicals' => __('Productos enológicos'),
        'equipment' => __('Maquinaria y equipos'),
        'services'  => __('Servicios'),
        'other'     => __('Otro'),
    ];

    protected $fillable = [
        'user_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'vat_number',
        'category',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
