<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasurement extends Model
{
    use HasFactory;

    protected $table = 'units_of_measurement';

    protected $fillable = [
        'name',
        'symbol',
        'type',
    ];

    /**
     * Contenedores que usan esta unidad
     */
    public function containers(): HasMany
    {
        return $this->hasMany(Container::class);
    }
}
