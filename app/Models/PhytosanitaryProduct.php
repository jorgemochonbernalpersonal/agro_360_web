<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhytosanitaryProduct extends Model
{
    protected $fillable = [
        'name',
        'active_ingredient',
        'registration_number',
        'manufacturer',
        'type',
        'toxicity_class',
        'withdrawal_period_days',
        'description',
    ];

    /**
     * Tratamientos que usan este producto
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(PhytosanitaryTreatment::class, 'product_id');
    }
}
