<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for Agro365';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $baseUrl = config('app.url');
        
        // Asegurar que la URL use HTTPS en producción
        if (config('app.env') === 'production') {
            $baseUrl = str_replace('http://', 'https://', $baseUrl);
        }

        $sitemap = Sitemap::create();
        
        // Homepage with images
        $homepage = Url::create($baseUrl . '/')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(1.0);
        
        // Add images to homepage
        if (file_exists(public_path('images/logo.png'))) {
            $homepage->addImage(
                $baseUrl . '/images/logo.png',
                'Logo de Agro365, software profesional para viñedos y bodegas',
                '',
                'Agro365 - Software de Gestión Agrícola'
            );
        }
        
        if (file_exists(public_path('images/dashboard-preview.png'))) {
            $homepage->addImage(
                $baseUrl . '/images/dashboard-preview.png',
                'Vista del dashboard de gestión agrícola con SIGPAC y cuaderno digital',
                '',
                'Dashboard de Agro365'
            );
        }
        
        $sitemap->add($homepage)
            ->add(Url::create($baseUrl . '/faqs')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.8))
            ->add(Url::create($baseUrl . '/quienes-somos')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7))
            ->add(Url::create($baseUrl . '/blog')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8))
            ->add(Url::create($baseUrl . '/tutoriales')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7))
            ->add(Url::create($baseUrl . '/privacidad')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.3))
            ->add(Url::create($baseUrl . '/terminos')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.3))
            ->add(Url::create($baseUrl . '/cookies')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.3))
            ->add(Url::create($baseUrl . '/aviso-legal')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.3));
        
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml');
        $this->info('Base URL used: ' . $baseUrl);
    }
}
