<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacPayment extends Model
{
    public const PAYMENT_TYPES = [
        'basic_payment' => 'Pago básico',
        'eco_scheme' => 'Eco-régimen',
        'associated_aid' => 'Ayuda asociada viñedo',
        'transitional' => 'Pago transitorio',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'viticulturist_id',
        'declaration_id',
        'year',
        'payment_type',
        'amount',
        'payment_date',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'year' => 'integer',
    ];

    public static function paymentTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::PAYMENT_TYPES);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<PacDeclaration, $this> */
    public function declaration(): BelongsTo
    {
        return $this->belongsTo(PacDeclaration::class, 'declaration_id');
    }

    public function typeLabel(): string
    {
        return __(self::PAYMENT_TYPES[$this->payment_type] ?? $this->payment_type);
    }

    public function scopeForViticulturist($query, int $id)
    {
        return $query->where('viticulturist_id', $id);
    }
}
