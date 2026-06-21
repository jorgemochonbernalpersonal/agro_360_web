<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    // ── Abilities disponibles ─────────────────────────────────────────────────

    // ── Abilities existentes (winery.ability:*) ───────────────────────────────
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

    // ── Abilities nuevas (rediseño de precios) ────────────────────────────────

    // Free
    public const DIGITAL_NOTEBOOK = 'digital_notebook';

    public const SIGPAC_PLOTS = 'sigpac_plots';

    public const MAPS = 'maps';

    public const PLANTINGS = 'plantings';

    public const PHENOLOGY = 'phenology';

    public const PEST_MANAGEMENT = 'pest_management';

    public const HARVESTS_VIEW = 'harvests_view';

    public const INVOICING = 'invoicing';

    // Capa A — Compliance / Inteligencia
    public const REMOTE_SENSING = 'remote_sensing';

    public const PAC = 'pac';

    public const HARVEST_OPERATIONS = 'harvest_operations';

    public const FINANCIAL_STATS = 'financial_stats';

    public const ROPO_APPLICATORS = 'ropo_applicators';

    // Capa B — Red
    public const VITICULTURIST_PANEL = 'viticulturist_panel';

    public const CONSOLIDATED_REPORTS = 'consolidated_reports';

    public const DO_OVERSIGHT = 'do_oversight';

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

        // ── Free ─────────────────────────────────────────────────────────────
        [
            'code' => self::DIGITAL_NOTEBOOK,
            'name' => 'Cuaderno de campo',
            'description' => 'Actividades, tratamientos y registro de campo.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::SIGPAC_PLOTS,
            'name' => 'Parcelas y SIGPAC',
            'description' => 'Gestión de parcelas e integración SIGPAC.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::MAPS,
            'name' => 'Mapas',
            'description' => 'Visualización cartográfica de parcelas.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::PLANTINGS,
            'name' => 'Plantaciones',
            'description' => 'Gestión de plantaciones y cultivos.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::PHENOLOGY,
            'name' => 'Fenología',
            'description' => 'Registro y seguimiento de estados fenológicos.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::PEST_MANAGEMENT,
            'name' => 'Plagas y enfermedades',
            'description' => 'Consulta de plagas y enfermedades (solo lectura).',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::HARVESTS_VIEW,
            'name' => 'Consulta de entregas',
            'description' => 'Ver entregas de uva a bodega.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::INVOICING,
            'name' => 'Facturación básica',
            'description' => 'Emitir facturas y borradores sin sello Verifactu.',
            'module' => 'invoicing',
        ],

        // ── Capa A — Compliance / Inteligencia ───────────────────────────────
        [
            'code' => self::REMOTE_SENSING,
            'name' => 'Teledetección satelital',
            'description' => 'NDVI, LAI, estrés térmico y suelo (NASA/Copernicus).',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::PAC,
            'name' => 'PAC y declaraciones',
            'description' => 'PAC, eco-regímenes y declaraciones de superficies.',
            'module' => 'regulatory',
        ],
        [
            'code' => self::HARVEST_OPERATIONS,
            'name' => 'Operaciones de entrega',
            'description' => 'Crear entregas, albaranes y exportar PDF.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::FINANCIAL_STATS,
            'name' => 'Estadísticas financieras',
            'description' => 'Resumen económico y estadísticas de campaña.',
            'module' => 'viticulturist',
        ],
        [
            'code' => self::ROPO_APPLICATORS,
            'name' => 'Aplicadores ROPO',
            'description' => 'Registro de aplicadores fitosanitarios (ROPO).',
            'module' => 'regulatory',
        ],

        // ── Capa B — Red ─────────────────────────────────────────────────────
        [
            'code' => self::VITICULTURIST_PANEL,
            'name' => 'Panel de viticultores',
            'description' => 'Panel en tiempo real e invitación de viticultores.',
            'module' => 'winery',
        ],
        [
            'code' => self::CONSOLIDATED_REPORTS,
            'name' => 'Informes consolidados',
            'description' => 'Informes agregados y trazabilidad completa.',
            'module' => 'winery',
        ],
        [
            'code' => self::DO_OVERSIGHT,
            'name' => 'Supervisión DO',
            'description' => 'Panel de supervisión, agregados, firma y API para Denominación de Origen.',
            'module' => 'do',
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
