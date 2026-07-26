<?php

namespace App\Support;

/**
 * Genera URLs intermedias que redirigen a la web (si hay sesión) o
 * muestran una página de apertura en la app móvil (deep link).
 *
 * Uso en notificaciones:
 *   ->action('Ver contenedores', AppLink::url(
 *       route('winery.containers.index'),
 *       'agro365://containers'
 *   ))
 */
class AppLink
{
    /**
     * Genera la URL de la página intermedia.
     *
     * @param string $webUrl   URL web de destino (debe ser del mismo dominio)
     * @param string $deepLink Deep link de la app (agro365://...)
     */
    public static function url(string $webUrl, string $deepLink = 'agro365://home'): string
    {
        return route('app.open', ['web' => $webUrl, 'deep' => $deepLink]);
    }
}
