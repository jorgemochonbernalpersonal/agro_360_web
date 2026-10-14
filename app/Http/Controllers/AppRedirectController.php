<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppRedirectController extends Controller
{
    public function open(Request $request)
    {
        $appUrl = rtrim(config('app.url'), '/');
        $webUrl = $request->query('web', $appUrl.'/login');
        $deepUrl = $request->query('deep', 'agro365://home');

        // Prevenir open redirect: solo URLs del mismo host+scheme que la app, o rutas
        // relativas de un solo "/" (se rechaza "//evil.com", que los navegadores tratan
        // como protocol-relative). str_starts_with() con el prefijo del dominio es
        // insuficiente: "https://agro365.es.evil.com" o "https://agro365.es@evil.com"
        // también "empiezan por" el dominio esperado sin serlo.
        if (! $this->isSameAppHost($webUrl, $appUrl)) {
            $webUrl = $appUrl.'/login';
        }

        // Solo deep links con el scheme de la app
        if (! str_starts_with($deepUrl, 'agro365://')) {
            $deepUrl = 'agro365://home';
        }

        // Usuario con sesión web → redirigir directamente sin mostrar la página
        if (auth()->check()) {
            return redirect($webUrl);
        }

        return view('app.open', compact('webUrl', 'deepUrl'));
    }

    /**
     * Comprueba que $url es una ruta relativa segura (empieza por un único "/", nunca "//")
     * o que su host+scheme coinciden EXACTAMENTE con los de $appUrl. Evita open redirects
     * vía prefijo ("agro365.es.evil.com") o userinfo ("agro365.es@evil.com").
     */
    private function isSameAppHost(string $url, string $appUrl): bool
    {
        // Ruta relativa: solo se admite un único "/" inicial (no "//", que el navegador
        // interpreta como protocol-relative hacia otro host).
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $target = parse_url($url);
        $expected = parse_url($appUrl);

        if (! $target || ! $expected || empty($target['host']) || empty($expected['host'])) {
            return false;
        }

        $targetScheme = strtolower($target['scheme'] ?? 'https');
        $expectedScheme = strtolower($expected['scheme'] ?? 'https');

        return $targetScheme === $expectedScheme
            && strtolower($target['host']) === strtolower($expected['host']);
    }
}
