<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ContainerType extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    public function useFallbackLocale(): bool
    {
        return true;
    }

    protected $fillable = ['name', 'description'];

    public function containers(): HasMany
    {
        return $this->hasMany(Container::class, 'type_id');
    }
}
