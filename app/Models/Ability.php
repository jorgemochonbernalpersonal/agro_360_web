<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    // ── Abilities disponibles ─────────────────────────────────────────────────

    public const HARVEST_RECEPTION = 'harvest_reception';

    public const WINE_PROCESS = 'wine_process';

    public const CELLAR_MANAGEMENT = 'cellar_management';

    public const LABEL_BATCHES = 'label_batches';

    public const NOTEBOOK_ACCESS = 'notebook_access';

    public const GRAPE_PURCHASE_INV = 'grape_purchase_invoice';

    public const PRODUCT_SALES = 'product_sales';

    public const VERIFAKTU = 'verifaktu';

    public const YIELD_FORECASTS = 'yield_forecasts';

    public const QUALITY_ANALYSIS = 'quality_analysis';

    public const SEEDED = [
        [
            'code' => self::HARVEST_RECEPTION,
            'name' => 'Recepciones de vendimia',
            'description' => 'Registrar y gestionar recepciones de uva.',
            'module' => 'winery',
        ],
        [
            'code' => self::WINE_PROCESS,
            'name' => 'Elaboración de vinos',
            'description' => 'Acceso al módulo de vinificación.',
            'module' => 'winery',
        ],
        [
            'code' => self::CELLAR_MANAGEMENT,
            'name' => 'Gestión de bodega',
            'description' => 'Contenedores, trasvases y operaciones de bodega.',
            'module' => 'winery',
        ],
        [
            'code' => self::LABEL_BATCHES,
            'name' => 'Lotes de etiquetas',
            'description' => 'Gestión de lotes de contraetiquetas.',
            'module' => 'winery',
        ],
        [
            'code' => self::QUALITY_ANALYSIS,
            'name' => 'Análisis de calidad',
            'description' => 'Análisis de calidad de vendimia y vinos.',
            'module' => 'winery',
        ],
        [
            'code' => self::NOTEBOOK_ACCESS,
            'name' => 'Acceso cuadernos viticultores',
            'description' => 'Ver cuadernos de campo de viticultores asignados.',
            'module' => 'regulatory',
        ],
        [
            'code' => self::YIELD_FORECASTS,
            'name' => 'Previsiones de cosecha',
            'description' => 'Crear y gestionar aforos de vendimia.',
            'module' => 'vineyard',
        ],
        [
            'code' => self::GRAPE_PURCHASE_INV,
            'name' => 'Facturas compra uva',
            'description' => 'Emitir liquidaciones y facturas de compra de uva.',
            'module' => 'invoicing',
        ],
        [
            'code' => self::PRODUCT_SALES,
            'name' => 'Ventas de producto',
            'description' => 'Facturación de venta de vinos y productos.',
            'module' => 'invoicing',
        ],
        [
            'code' => self::VERIFAKTU,
            'name' => 'VeriFactu',
            'description' => 'Facturación electrónica (VeriFactu / AEAT).',
            'module' => 'invoicing',
        ],
    ];

    protected $fillable = ['code', 'name', 'description', 'module'];

    // ── Relaciones ────────────────────────────────────────────────────────────

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_abilities')
            ->withPivot('granted_by', 'granted_at')
            ->withTimestamps();
    }
}
