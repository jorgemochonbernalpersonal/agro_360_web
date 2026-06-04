<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProducerBuysExternalGrape
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user?->isProducer() || ! $user->compra_uva_externa) {
            abort(403, __('Este módulo requiere tener activa la opción de compra de uva externa.'));
        }

        return $next($request);
    }
}
