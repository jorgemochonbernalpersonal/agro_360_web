<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryType extends Model
{
    use HasFactory;

    protected $table = 'machinery_types';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Maquinaria asociada a este tipo.
     */
    public function machinery(): HasMany
    {
        return $this->hasMany(Machinery::class, 'machinery_type_id');
    }
}


