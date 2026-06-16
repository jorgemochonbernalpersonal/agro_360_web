<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    const CATEGORIES = [
        'grape' => 'Proveedor de uva',
        'packaging' => 'Envases y embalaje',
        'chemicals' => 'Productos enológicos',
        'equipment' => 'Maquinaria y equipos',
        'services' => 'Servicios',
        'other' => 'Otro',
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

    public static function categoryOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CATEGORIES);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->category] ?? $this->category);
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
