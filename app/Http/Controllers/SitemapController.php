<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            // Landing page
            [
                'loc' => url('/'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'images' => [
                    [
                        'loc' => url('/images/logo.png'),
                        'caption' => 'Logo de Agro365, software profesional para viñedos y bodegas',
                        'title' => 'Agro365 - Software de Gestión Agrícola',
                    ],
                    [
                        'loc' => url('/images/dashboard-preview.png'),
                        'caption' => 'Vista del dashboard de gestión agrícola con SIGPAC y cuaderno digital',
                        'title' => 'Dashboard de Agro365',
                    ],
                ],
            ],
            
            // FAQs
            [
                'loc' => url('/faqs'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.9',
            ],
            
            // Páginas de contenido SEO
            [
                'loc' => url('/que-es-sigpac'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/cuaderno-campo-digital-2027'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/normativa-pac'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/digitalizar-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/comparativa-software-agricola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/software-para-viticultores'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/app-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/cuaderno-digital-viticultores'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            // Nuevas páginas SEO - Diciembre 2024
            [
                'loc' => url('/gestion-vendimia'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/registro-fitosanitarios'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/subvenciones-pac-2024'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/control-plagas-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/facturacion-agricola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            // Páginas SEO - Media prioridad y contenido específico
            [
                'loc' => url('/gestion-cuadrillas-agricolas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/maquinaria-agricola-registro'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/plantaciones-viñedo-variedades'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/rendimientos-cosecha-viñedo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/informes-oficiales-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/ndvi-viñedo-teledeteccion'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/calendario-viticola'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/trazabilidad-vino-origen'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/firma-digital-agricultura'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/gestion-campañas-agricolas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            
            // Páginas regionales por DO
            [
                'loc' => url('/software-viticultores-rioja'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-ribera-duero'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-rueda'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-penedes'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-la-mancha'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            
            // Blog
            [
                'loc' => url('/blog'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/blog/novedades-pac-2025'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => url('/blog/errores-cuaderno-campo'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => url('/blog/calendario-viticola-2025'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            
            // Páginas regionales por DO (segunda tanda)
            [
                'loc' => url('/software-viticultores-priorat'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-rias-baixas'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-toro'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/software-viticultores-jumilla'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            
            // Páginas legales
            [
                'loc' => url('/privacidad'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => url('/terminos'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => url('/cookies'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => url('/aviso-legal'),
                'lastmod' => now()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
        ];
        
        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}
