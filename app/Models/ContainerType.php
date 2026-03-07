<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerType extends Model
{
    protected $fillable = ['name', 'description'];

    public function containers(): HasMany
    {
        return $this->hasMany(Container::class, 'type_id');
    }
}
