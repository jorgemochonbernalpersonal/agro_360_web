<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectProducerToViticulturistPlots
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()?->isProducer()) {
            $path = ltrim(str_replace('/winery', '', $request->getPathInfo()), '/');
            $qs = $request->getQueryString();
            return redirect('/' . $path . ($qs ? '?' . $qs : ''));
        }

        return $next($request);
    }
}
