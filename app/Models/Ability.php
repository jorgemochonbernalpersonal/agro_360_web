<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    protected $fillable = ['code', 'name', 'description', 'module'];

    // ── Abilities disponibles ─────────────────────────────────────────────────

    public const HARVEST_RECEPTION    = 'harvest_reception';
    public const WINE_PROCESS         = 'wine_process';
    public const CELLAR_MANAGEMENT    = 'cellar_management';
    public const LABEL_BATCHES        = 'label_batches';
    public const NOTEBOOK_ACCESS      = 'notebook_access';
    public const GRAPE_PURCHASE_INV   = 'grape_purchase_invoice';
    public const PRODUCT_SALES        = 'product_sales';
    public const VERIFAKTU            = 'verifaktu';
    public const YIELD_FORECASTS      = 'yield_forecasts';
    public const QUALITY_ANALYSIS     = 'quality_analysis';

    public const SEEDED = [
        [
            'code'        => self::HARVEST_RECEPTION,
            'name'        => __('Recepciones de vendimia'),
            'description' => __('Registrar y gestionar recepciones de uva.'),
            'module'      => 'winery',
        ],
        [
            'code'        => self::WINE_PROCESS,
            'name'        => __('Elaboración de vinos'),
            'description' => __('Acceso al módulo de vinificación.'),
            'module'      => 'winery',
        ],
        [
            'code'        => self::CELLAR_MANAGEMENT,
            'name'        => __('Gestión de bodega'),
            'description' => __('Contenedores, trasvases y operaciones de bodega.'),
            'module'      => 'winery',
        ],
        [
            'code'        => self::LABEL_BATCHES,
            'name'        => __('Lotes de etiquetas'),
            'description' => __('Gestión de lotes de contraetiquetas.'),
            'module'      => 'winery',
        ],
        [
            'code'        => self::QUALITY_ANALYSIS,
            'name'        => __('Análisis de calidad'),
            'description' => __('Análisis de calidad de vendimia y vinos.'),
            'module'      => 'winery',
        ],
        [
            'code'        => self::NOTEBOOK_ACCESS,
            'name'        => __('Acceso cuadernos viticultores'),
            'description' => __('Ver cuadernos de campo de viticultores asignados.'),
            'module'      => 'regulatory',
        ],
        [
            'code'        => self::YIELD_FORECASTS,
            'name'        => __('Previsiones de cosecha'),
            'description' => __('Crear y gestionar aforos de vendimia.'),
            'module'      => 'vineyard',
        ],
        [
            'code'        => self::GRAPE_PURCHASE_INV,
            'name'        => __('Facturas compra uva'),
            'description' => __('Emitir liquidaciones y facturas de compra de uva.'),
            'module'      => 'invoicing',
        ],
        [
            'code'        => self::PRODUCT_SALES,
            'name'        => __('Ventas de producto'),
            'description' => __('Facturación de venta de vinos y productos.'),
            'module'      => 'invoicing',
        ],
        [
            'code'        => self::VERIFAKTU,
            'name'        => __('VeriFactu'),
            'description' => __('Facturación electrónica (VeriFactu / AEAT).'),
            'module'      => 'invoicing',
        ],
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_abilities')
            ->withPivot('granted_by', 'granted_at')
            ->withTimestamps();
    }
}
