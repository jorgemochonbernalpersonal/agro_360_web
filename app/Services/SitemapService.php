<?php

namespace App\Services;

class SitemapService
{
    /**
     * ✅ OPTIMIZACIÓN SEO: Asegurar URLs absolutas correctas
     * Usa config('app.url') para garantizar dominio correcto en producción
     */
    private function getAbsoluteUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    public function getUrls(): array
    {
        return [
            // Landing page
            [
                'loc' => $this->getAbsoluteUrl(''),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'images' => [
                    [
                        'loc' => $this->getAbsoluteUrl('images/logo.png'),
                        'caption' => 'Logo de Agro365, software profesional para viñedos y bodegas',
                        'title' => 'Agro365 - Software de Gestión Agrícola',
                    ],
                    [
                        'loc' => $this->getAbsoluteUrl('images/dashboard-preview.png'),
                        'caption' => 'Vista del dashboard de gestión agrícola con SIGPAC y cuaderno digital',
                        'title' => 'Dashboard de Agro365',
                    ],
                ],
            ],
            
            // FAQs
            [
                'loc' => $this->getAbsoluteUrl('faqs'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.9',
            ],
            
            // Páginas de contenido SEO
            [
                'loc' => $this->getAbsoluteUrl('que-es-sigpac'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('cuaderno-campo-digital-2027'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('normativa-pac'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('digitalizar-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('comparativa-software-agricola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-para-viticultores'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('app-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('cuaderno-digital-viticultores'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            // Nuevas páginas SEO - Diciembre 2024
            [
                'loc' => $this->getAbsoluteUrl('gestion-vendimia'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('registro-fitosanitarios'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('subvenciones-pac-2024'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('control-plagas-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('facturacion-agricola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            // Páginas SEO - Media prioridad y contenido específico
            [
                'loc' => $this->getAbsoluteUrl('gestion-cuadrillas-agricolas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('maquinaria-agricola-registro'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('plantaciones-viñedo-variedades'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('rendimientos-cosecha-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('informes-oficiales-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('ndvi-viñedo-teledeteccion'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('calendario-viticola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('trazabilidad-vino-origen'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('firma-digital-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('gestion-campañas-agricolas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            
            // Páginas regionales por DO
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-rioja'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-ribera-duero'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-rueda'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-penedes'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-la-mancha'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            
            // Blog
            [
                'loc' => $this->getAbsoluteUrl('blog'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => $this->getAbsoluteUrl('blog/novedades-pac-2025'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => $this->getAbsoluteUrl('blog/errores-cuaderno-campo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => $this->getAbsoluteUrl('blog/calendario-viticola-2025'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            
            // Páginas regionales por DO (segunda tanda)
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-priorat'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-rias-baixas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-toro'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => $this->getAbsoluteUrl('software-viticultores-jumilla'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            
            // Páginas legales
            [
                'loc' => $this->getAbsoluteUrl('privacidad'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => $this->getAbsoluteUrl('terminos'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => $this->getAbsoluteUrl('cookies'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => $this->getAbsoluteUrl('aviso-legal'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
        ];
    }
}
