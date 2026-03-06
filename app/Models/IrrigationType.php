<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationType extends Model
{
    use HasFactory;

    protected $table = 'irrigation_types';

    protected $fillable = ['user_id', 'name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
