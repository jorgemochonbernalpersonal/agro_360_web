<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class MachineryType extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name'];

    protected $table = 'machinery_types';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function useFallbackLocale(): bool
    {
        return true;
    }

    /**
     * Maquinaria asociada a este tipo.
     */
    /** @return HasMany<Machinery, $this> */
    public function machinery(): HasMany
    {
        return $this->hasMany(Machinery::class, 'machinery_type_id');
    }
}
