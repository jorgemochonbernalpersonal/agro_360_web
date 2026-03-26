<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppRedirectController extends Controller
{
    public function open(Request $request)
    {
        $appUrl  = rtrim(config('app.url'), '/');
        $webUrl  = $request->query('web', $appUrl . '/login');
        $deepUrl = $request->query('deep', 'agro365://home');

        // Prevenir open redirect: solo URLs del mismo dominio
        if (! str_starts_with($webUrl, $appUrl)) {
            $webUrl = $appUrl . '/login';
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
}
