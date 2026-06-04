<?php

if (! function_exists('content_route')) {
    /**
     * Helper para generar URLs de contenido usando nombres legacy o slugs
     *
     * @param string $nameOrSlug Nombre de ruta legacy o slug directo
     */
    function content_route(string $nameOrSlug): string
    {
        // Intentar obtener el mapeo desde config
        $mapping = config('content.route_name_mapping', []);

        // Si existe en el mapeo, usar el slug mapeado
        if (isset($mapping[$nameOrSlug])) {
            return route('content.show', ['slug' => $mapping[$nameOrSlug]]);
        }

        // Si no está en el mapeo, asumir que es un slug directo
        return route('content.show', ['slug' => $nameOrSlug]);
    }
}

if (! function_exists('blog_route')) {
    /**
     * Helper para generar URLs de blog usando nombres legacy o slugs
     *
     * @param string $nameOrSlug Nombre de ruta legacy o slug directo
     */
    function blog_route(string $nameOrSlug): string
    {
        // Intentar obtener el mapeo desde config
        $mapping = config('content.route_name_mapping', []);

        // Si existe en el mapeo, usar el slug mapeado
        if (isset($mapping[$nameOrSlug])) {
            return route('blog.show', ['slug' => $mapping[$nameOrSlug]]);
        }

        // Si no está en el mapeo, asumir que es un slug directo
        return route('blog.show', ['slug' => $nameOrSlug]);
    }
}
