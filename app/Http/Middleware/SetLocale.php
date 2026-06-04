<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['es', 'en', 'ca', 'eu', 'gl'];

    private const COOKIE_NAME = 'app_locale';

    private const COOKIE_DAYS = 365;

    public function handle(Request $request, Closure $next): Response
    {
        // Cambio explícito via query param ?lang=ca → persistir y redirigir sin ?lang=
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if (in_array($lang, self::SUPPORTED, true)) {
                session(['locale' => $lang]);
                if (auth()->check()) {
                    auth()->user()->update(['locale' => $lang]);
                }
                $cleanUrl = $request->url();
                $query = $request->except('lang');
                if ($query) {
                    $cleanUrl .= '?'.http_build_query($query);
                }

                return redirect($cleanUrl)
                    ->cookie(self::COOKIE_NAME, $lang, 60 * 24 * self::COOKIE_DAYS, '/', null, null, false);
            }
        }

        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        session(['locale' => $locale]);

        $response = $next($request);

        // BinaryFileResponse (assets estáticos como Flux JS) no soporta ->cookie()
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return $response;
        }

        // Persistir en cookie para sobrevivir expiración de sesión y wire:navigate
        $response->cookie(self::COOKIE_NAME, $locale, 60 * 24 * self::COOKIE_DAYS, '/', null, null, false);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Sesión
        if (session()->has('locale') && in_array(session('locale'), self::SUPPORTED, true)) {
            return session('locale');
        }

        // 2. Cookie (sobrevive expiración de sesión y recargas de wire:navigate)
        $cookie = $request->cookie(self::COOKIE_NAME);
        if ($cookie && in_array($cookie, self::SUPPORTED, true)) {
            session(['locale' => $cookie]);

            return $cookie;
        }

        // 3. Preferencia guardada del usuario autenticado
        if (auth()->check() && auth()->user()->locale && in_array(auth()->user()->locale, self::SUPPORTED, true)) {
            session(['locale' => auth()->user()->locale]);

            return auth()->user()->locale;
        }

        // 4. Fallback al español
        return 'es';
    }
}
