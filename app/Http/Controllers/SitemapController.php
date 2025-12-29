<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    protected $sitemapService;

    public function __construct(\App\Services\SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    public function index(): Response
    {
        $urls = $this->sitemapService->getUrls();
        
        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}
