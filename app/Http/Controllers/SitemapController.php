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
