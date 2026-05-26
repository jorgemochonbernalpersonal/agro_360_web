<?php

namespace App\Services;

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContentController;

class SitemapService
{
    /**
     * Prioridad y changefreq por slug de contenido.
     * Cualquier slug que no aparezca aquí hereda los defaults: 0.7 / monthly.
     * Este mapa es la única fuente de verdad SEO; los slugs vienen de ContentController.
     */
    private const CONTENT_PRIORITIES = [
        // Pricing
        'precios'                        => ['0.9', 'monthly'],

        // Comerciales principales
        'software-gestion-agricola'      => ['0.9', 'weekly'],
        'software-viticultura'           => ['0.9', 'weekly'],
        'software-bodegas'               => ['0.9', 'weekly'],
        'cuaderno-digital'               => ['0.9', 'weekly'],
        'trazabilidad-agricola'          => ['0.9', 'weekly'],
        'software-para-viticultores'     => ['0.9', 'weekly'],
        'cuaderno-digital-viticultores'  => ['0.9', 'weekly'],
        'facturacion-agricola'           => ['0.9', 'weekly'],
        'ndvi-viñedo-teledeteccion'      => ['0.9', 'weekly'],

        // Por sector
        'viticultores'                   => ['0.9', 'weekly'],
        'bodegas'                        => ['0.9', 'weekly'],
        'cooperativas'                   => ['0.9', 'weekly'],
        'ingenieros-agronomos'           => ['0.9', 'weekly'],

        // Informativas clave
        'que-es-sigpac'                  => ['0.9', 'weekly'],
        'cuaderno-campo-digital-2027'    => ['0.8', 'monthly'],
        'normativa-pac'                  => ['0.8', 'monthly'],
        'digitalizar-viñedo'             => ['0.8', 'monthly'],
        'gestion-vendimia'               => ['0.8', 'monthly'],
        'registro-fitosanitarios'        => ['0.8', 'monthly'],
        'subvenciones-pac'               => ['0.8', 'monthly'],
        'control-plagas-viñedo'          => ['0.8', 'monthly'],
        'app-agricultura'                => ['0.8', 'monthly'],

        // DOs principales
        'software-viticultores-rioja'        => ['0.8', 'monthly'],
        'software-viticultores-ribera-duero' => ['0.8', 'monthly'],
        'software-viticultores-rueda'        => ['0.8', 'monthly'],
        'software-viticultores-penedes'      => ['0.8', 'monthly'],
        'software-viticultores-la-mancha'    => ['0.8', 'monthly'],
        'software-viticultores-priorat'      => ['0.8', 'monthly'],
        'software-viticultores-rias-baixas'  => ['0.8', 'monthly'],
        'software-viticultores-toro'         => ['0.8', 'monthly'],
        'software-viticultores-jumilla'      => ['0.8', 'monthly'],

        // Default implícito para todo lo demás: ['0.7', 'monthly']
    ];

    private function getAbsoluteUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path    = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    private function priority(string $slug): array
    {
        return self::CONTENT_PRIORITIES[$slug] ?? ['0.7', 'monthly'];
    }

    public function getUrls(): array
    {
        $urls = [];

        // ── Landing page ────────────────────────────────────────────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl(''),
            'lastmod'    => now()->toIso8601String(),
            'changefreq' => 'weekly',
            'priority'   => '1.0',
            'images'     => [
                [
                    'loc'     => $this->getAbsoluteUrl('images/logo.png'),
                    'caption' => __('Logo de Agro365, software profesional para viñedos y bodegas'),
                    'title'   => __('Agro365 - Software de Gestión Agrícola'),
                ],
                [
                    'loc'     => $this->getAbsoluteUrl('images/dashboard-preview.png'),
                    'caption' => __('Vista del dashboard de gestión agrícola con SIGPAC y cuaderno digital'),
                    'title'   => __('Dashboard de Agro365'),
                ],
            ],
        ];

        // ── FAQs (ruta propia, no pasa por ContentController) ───────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl('faqs'),
            'lastmod'    => now()->toIso8601String(),
            'changefreq' => 'monthly',
            'priority'   => '0.9',
        ];

        // ── Páginas de contenido — generadas automáticamente ────────────────
        foreach (ContentController::getAllSlugs() as $slug) {
            [$priority, $changefreq] = $this->priority($slug);
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl($slug),
                'lastmod'    => now()->toIso8601String(),
                'changefreq' => $changefreq,
                'priority'   => $priority,
            ];
        }

        // ── Blog índice ──────────────────────────────────────────────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl('blog'),
            'lastmod'    => now()->toIso8601String(),
            'changefreq' => 'weekly',
            'priority'   => '0.7',
        ];

        // ── Posts de blog — generados automáticamente ───────────────────────
        foreach (BlogController::getAllSlugs() as $slug) {
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl("blog/{$slug}"),
                'lastmod'    => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ];
        }

        // ── Páginas legales ──────────────────────────────────────────────────
        foreach (['privacidad', 'terminos', 'cookies', 'aviso-legal'] as $slug) {
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl($slug),
                'lastmod'    => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority'   => '0.3',
            ];
        }

        return $urls;
    }
}
