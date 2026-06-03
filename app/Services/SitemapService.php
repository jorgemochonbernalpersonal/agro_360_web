<?php

namespace App\Services;

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContentController;
use Carbon\Carbon;

class SitemapService
{
    /**
     * Prioridad y changefreq por slug de contenido.
     * Cualquier slug que no aparezca aquí hereda los defaults: 0.7 / monthly.
     * Las páginas de región (software-viticultores-*) se tratan aparte (0.6 / monthly).
     * Este mapa es la única fuente de verdad SEO; los slugs vienen de ContentController.
     *
     * Nota SEO: la prioridad es RELATIVA dentro del propio sitio. Si todo fuese 0.9
     * la señal no diría nada, así que se reserva 0.9 para el home + páginas dinero.
     * Y changefreq refleja la realidad: son landing pages evergreen, no cambian cada semana.
     */
    private const CONTENT_PRIORITIES = [
        // Pricing + comerciales núcleo
        'precios'                        => ['0.9', 'monthly'],
        'software-gestion-agricola'      => ['0.9', 'monthly'],
        'software-viticultura'           => ['0.9', 'monthly'],
        'software-bodegas'               => ['0.9', 'monthly'],
        'cuaderno-digital'               => ['0.9', 'monthly'],
        'cuaderno-campo-digital-2027'    => ['0.9', 'monthly'],

        // Comerciales secundarios
        'software-para-viticultores'     => ['0.8', 'monthly'],
        'cuaderno-digital-viticultores'  => ['0.8', 'monthly'],
        'trazabilidad-agricola'          => ['0.8', 'monthly'],
        'facturacion-agricola'           => ['0.8', 'monthly'],
        'ndvi-vinedo-teledeteccion'      => ['0.8', 'monthly'],
        'que-es-sigpac'                  => ['0.8', 'monthly'],

        // Por sector
        'viticultores'                   => ['0.8', 'monthly'],
        'bodegas'                        => ['0.8', 'monthly'],
        'cooperativas'                   => ['0.7', 'monthly'],
        'ingenieros-agronomos'           => ['0.7', 'monthly'],

        // Informativas clave
        'normativa-pac'                  => ['0.7', 'monthly'],
        'digitalizar-vinedo'             => ['0.7', 'monthly'],
        'gestion-vendimia'               => ['0.7', 'monthly'],
        'registro-fitosanitarios'        => ['0.7', 'monthly'],
        'subvenciones-pac'               => ['0.7', 'monthly'],
        'control-plagas-vinedo'          => ['0.7', 'monthly'],
        'app-agricultura'                => ['0.7', 'monthly'],

        // Default implícito para todo lo demás: ['0.7', 'monthly']
        // Regiones (software-viticultores-*): ['0.6', 'monthly'] — ver priority()
    ];

    /**
     * Fecha de respaldo cuando no se puede leer la fecha real del archivo
     * (formato Y-m-d, constante para que el sitemap sea estable entre regeneraciones).
     */
    private const FALLBACK_DATE = '2026-06-03';

    private function getAbsoluteUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path    = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    private function priority(string $slug): array
    {
        if (isset(self::CONTENT_PRIORITIES[$slug])) {
            return self::CONTENT_PRIORITIES[$slug];
        }

        // Páginas de región: muchas y evergreen → prioridad media-baja.
        if (str_starts_with($slug, 'software-viticultores-')) {
            return ['0.6', 'monthly'];
        }

        return ['0.7', 'monthly'];
    }

    /**
     * Devuelve la fecha de última modificación real de la primera vista que exista,
     * en formato ISO 8601. Si ninguna existe, usa FALLBACK_DATE.
     *
     * Usar filemtime (en vez de now()) hace que el lastmod sea estable: solo cambia
     * cuando el contenido cambia de verdad, no en cada regeneración del sitemap.
     *
     * @param array<int, string> $relativeViewPaths Rutas relativas a resources/views (sin extensión)
     */
    private function lastmod(array $relativeViewPaths): string
    {
        foreach ($relativeViewPaths as $relative) {
            $file = resource_path('views/' . $relative . '.blade.php');
            if (is_file($file)) {
                return Carbon::createFromTimestamp(filemtime($file))->toIso8601String();
            }
        }

        return Carbon::parse(self::FALLBACK_DATE)->toIso8601String();
    }

    public function getUrls(): array
    {
        $urls = [];

        // ── Landing page ────────────────────────────────────────────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl(''),
            'lastmod'    => $this->lastmod(['welcome']),
            'changefreq' => 'weekly',
            'priority'   => '1.0',
            'images'     => [
                [
                    'loc'     => $this->getAbsoluteUrl('images/logo.png'),
                    'caption' => 'Logo de Agro365, software profesional para viñedos y bodegas',
                    'title'   => 'Agro365 - Software de Gestión Agrícola',
                ],
                [
                    'loc'     => $this->getAbsoluteUrl('images/dashboard-preview.png'),
                    'caption' => 'Vista del dashboard de gestión agrícola con SIGPAC y cuaderno digital',
                    'title'   => 'Dashboard de Agro365',
                ],
            ],
        ];

        // ── FAQs (ruta propia, no pasa por ContentController) ───────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl('faqs'),
            'lastmod'    => $this->lastmod(['faqs']),
            'changefreq' => 'monthly',
            'priority'   => '0.9',
        ];

        // ── Páginas de contenido — generadas automáticamente ────────────────
        foreach (ContentController::getAllSlugs() as $slug) {
            [$priority, $changefreq] = $this->priority($slug);
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl($slug),
                // Página propia por slug, o la plantilla compartida de región.
                'lastmod'    => $this->lastmod(['content/' . $slug, 'content/software-viticultores-region']),
                'changefreq' => $changefreq,
                'priority'   => $priority,
            ];
        }

        // ── Blog índice ──────────────────────────────────────────────────────
        $urls[] = [
            'loc'        => $this->getAbsoluteUrl('blog'),
            'lastmod'    => $this->lastmod(['blog/index']),
            'changefreq' => 'monthly',
            'priority'   => '0.7',
        ];

        // ── Posts de blog — generados automáticamente ───────────────────────
        foreach (BlogController::getAllSlugs() as $slug) {
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl("blog/{$slug}"),
                'lastmod'    => $this->lastmod(['blog/' . $slug]),
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ];
        }

        // ── Páginas legales ──────────────────────────────────────────────────
        $legalViews = [
            'privacidad'  => 'legal/privacy',
            'terminos'    => 'legal/terms',
            'cookies'     => 'legal/cookies',
            'aviso-legal' => 'legal/aviso-legal',
        ];
        foreach ($legalViews as $slug => $view) {
            $urls[] = [
                'loc'        => $this->getAbsoluteUrl($slug),
                'lastmod'    => $this->lastmod([$view]),
                'changefreq' => 'yearly',
                'priority'   => '0.3',
            ];
        }

        return $urls;
    }
}
