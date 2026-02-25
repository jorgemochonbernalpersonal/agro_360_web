<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ContentController extends Controller
{
    /**
     * Configuración de páginas de contenido SEO
     * Mapeo de slug -> vista
     */
    protected const CONTENT_PAGES = [
        // Páginas informativas
        'que-es-sigpac' => 'content.que-es-sigpac',
        'cuaderno-campo-digital-2027' => 'content.cuaderno-campo-digital-2027',
        'normativa-pac' => 'content.normativa-pac-2027',
        'digitalizar-viñedo' => 'content.digitalizar-viñedo',
        'comparativa-software-agricola' => 'content.comparativa-software-agricola',
        'software-para-viticultores' => 'content.software-para-viticultores',
        'app-agricultura' => 'content.app-agricultura',
        'cuaderno-digital-viticultores' => 'content.cuaderno-digital-viticultores',

        // Pricing page
        'precios' => 'content.precios',

        // Páginas comerciales (alta prioridad SEO)
        'software-gestion-agricola' => 'content.software-gestion-agricola',
        'software-viticultura' => 'content.software-viticultura',
        'software-bodegas' => 'content.software-bodegas',
        'cuaderno-digital' => 'content.cuaderno-digital',
        'trazabilidad-agricola' => 'content.trazabilidad-agricola',

        // Páginas por sector
        'viticultores' => 'content.viticultores',
        'bodegas' => 'content.bodegas',
        'cooperativas' => 'content.cooperativas',
        'ingenieros-agronomos' => 'content.ingenieros-agronomos',

        // Funcionalidades específicas
        'gestion-vendimia' => 'content.gestion-vendimia',
        'registro-fitosanitarios' => 'content.registro-fitosanitarios',
        'subvenciones-pac-2024' => 'content.subvenciones-pac-2024',
        'control-plagas-viñedo' => 'content.control-plagas-viñedo',
        'facturacion-agricola' => 'content.facturacion-agricola',

        // Media prioridad
        'gestion-cuadrillas-agricolas' => 'content.gestion-cuadrillas-agricolas',
        'maquinaria-agricola-registro' => 'content.maquinaria-agricola-registro',
        'plantaciones-viñedo-variedades' => 'content.plantaciones-viñedo-variedades',
        'rendimientos-cosecha-viñedo' => 'content.rendimientos-cosecha-viñedo',
        'informes-oficiales-agricultura' => 'content.informes-oficiales-agricultura',

        // Contenido técnico
        'ndvi-viñedo-teledeteccion' => 'content.ndvi-viñedo-teledeteccion',
        'calendario-viticola' => 'content.calendario-viticola',
        'trazabilidad-vino-origen' => 'content.trazabilidad-vino-origen',
        'firma-digital-agricultura' => 'content.firma-digital-agricultura',
        'gestion-campañas-agricolas' => 'content.gestion-campañas-agricolas',

        // Páginas regionales por DO
        'software-viticultores-rioja' => 'content.software-viticultores-rioja',
        'software-viticultores-ribera-duero' => 'content.software-viticultores-ribera-duero',
        'software-viticultores-rueda' => 'content.software-viticultores-rueda',
        'software-viticultores-penedes' => 'content.software-viticultores-penedes',
        'software-viticultores-la-mancha' => 'content.software-viticultores-la-mancha',
        'software-viticultores-priorat' => 'content.software-viticultores-priorat',
        'software-viticultores-rias-baixas' => 'content.software-viticultores-rias-baixas',
        'software-viticultores-toro' => 'content.software-viticultores-toro',
        'software-viticultores-jumilla' => 'content.software-viticultores-jumilla',
    ];

    /**
     * Mostrar página de contenido
     */
    public function show(string $slug)
    {
        // Verificar si el slug existe en la configuración
        if (!isset(self::CONTENT_PAGES[$slug])) {
            abort(404);
        }

        $view = self::CONTENT_PAGES[$slug];

        // Verificar si la vista existe
        if (!View::exists($view)) {
            abort(404);
        }

        return view($view);
    }

    /**
     * Obtener todos los slugs válidos (útil para generar rutas)
     */
    public static function getAllSlugs(): array
    {
        return array_keys(self::CONTENT_PAGES);
    }

    /**
     * Verificar si un slug es válido
     */
    public static function isValidSlug(string $slug): bool
    {
        return isset(self::CONTENT_PAGES[$slug]);
    }
}
