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

        // Asegurar que la URL use HTTPS en producción
        $forceHttps = config('app.env') === 'production';

        $sitemap = Sitemap::create();
        $service = new \App\Services\SitemapService();
        $urls = $service->getUrls();

        foreach ($urls as $urlData) {
            $loc = $urlData['loc'];
            
            if ($forceHttps) {
                $loc = str_replace('http://', 'https://', $loc);
            }

            $sitemapUrl = Url::create($loc)
                ->setLastModificationDate(\Carbon\Carbon::parse($urlData['lastmod']))
                ->setChangeFrequency($urlData['changefreq'])
                ->setPriority((float) $urlData['priority']);

            if (isset($urlData['images'])) {
                foreach ($urlData['images'] as $image) {
                    $imgLoc = $image['loc'];
                    if ($forceHttps) {
                        $imgLoc = str_replace('http://', 'https://', $imgLoc);
                    }
                    
                    $sitemapUrl->addImage(
                        $imgLoc,
                        $image['caption'],
                        '', // Geo location not used
                        $image['title']
                    );
                }
            }

            $sitemap->add($sitemapUrl);
        }
        
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml');
        $this->info('Total URLs: ' . count($urls));
    }
}
