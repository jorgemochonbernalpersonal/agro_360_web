<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Producto del Registro Oficial de Productos Fitosanitarios (MAPA).
 * Catálogo público importado con `phyto:import-registry`; no lo edita nadie.
 *
 * @property int                                                                                     $mapa_id
 * @property string                                                                                  $registration_number
 * @property string                                                                                  $name
 * @property string|null                                                                             $holder
 * @property string|null                                                                             $formulation
 * @property string                                                                                  $status
 * @property \Carbon\Carbon|null                                                                     $expires_on
 * @property string|null                                                                             $conditions
 * @property array<int, array{substance: string, concentration: float|null, unit: string|null}>|null $composition
 * @property array<int, string>|null                                                                 $other_names
 */
class PhytoRegistryProduct extends Model
{
    public const STATUS_ACTIVE = 'Vigente';

    /** Clave en app_settings con la fecha de la última actualización del MAPA. */
    public const SOURCE_DATE_SETTING = 'phyto_registry.source_date';

    protected $fillable = [
        'mapa_id',
        'registration_number',
        'name',
        'holder',
        'formulation',
        'status',
        'expires_on',
        'conditions',
        'composition',
        'active_substances',
        'other_names',
    ];

    protected $casts = [
        'expires_on' => 'date',
        'composition' => 'array',
        'other_names' => 'array',
    ];

    /** @return HasMany<PhytoRegistryUse, $this> */
    public function uses(): HasMany
    {
        return $this->hasMany(PhytoRegistryUse::class, 'product_id');
    }

    public function activeSubstances(): string
    {
        return collect($this->composition ?? [])
            ->map(fn (array $c) => trim($c['substance'].' '.($c['concentration'] !== null ? rtrim(rtrim(number_format($c['concentration'], 2, ',', ''), '0'), ',').($c['unit'] ?? '') : '')))
            ->implode(' + ');
    }
}
