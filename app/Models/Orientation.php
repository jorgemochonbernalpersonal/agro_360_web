<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orientation extends Model
{
    protected $fillable = ['name', 'abbreviation', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }
}
