<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
