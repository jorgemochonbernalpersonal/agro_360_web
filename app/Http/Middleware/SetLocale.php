<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['es', 'en', 'ca', 'eu', 'gl'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Cambio explícito via query param ?lang=ca
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if (in_array($lang, self::SUPPORTED, true)) {
                if (auth()->check()) {
                    auth()->user()->update(['locale' => $lang]);
                }
                return $lang;
            }
        }

        // 2. Preferencia guardada del usuario autenticado
        if (auth()->check() && in_array(auth()->user()->locale, self::SUPPORTED, true)) {
            return auth()->user()->locale;
        }

        // 3. Locale en sesión
        if (session()->has('locale') && in_array(session('locale'), self::SUPPORTED, true)) {
            return session('locale');
        }

        // 4. Fallback al español
        return 'es';
    }
}
