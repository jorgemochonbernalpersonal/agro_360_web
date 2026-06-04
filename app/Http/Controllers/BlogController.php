<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;

class BlogController extends Controller
{
    /**
     * Configuración de páginas de blog
     * Mapeo de slug -> vista
     */
    protected const BLOG_POSTS = [
        'cuaderno-campo-digital-obligatorio-2027' => 'blog.cuaderno-campo-digital-obligatorio-2027',
        'novedades-pac-2026' => 'blog.novedades-pac-2026',
        'novedades-pac-2025' => 'blog.novedades-pac-2025',
        'errores-cuaderno-campo' => 'blog.errores-cuaderno-campo',
        'calendario-viticola-2025' => 'blog.calendario-viticola-2025',
    ];

    /**
     * Listar posts del blog
     */
    public function index()
    {
        return view('blog.index');
    }

    /**
     * Mostrar post individual
     */
    public function show(string $slug)
    {
        // Verificar si el slug existe en la configuración
        if (! isset(self::BLOG_POSTS[$slug])) {
            abort(404);
        }

        $view = self::BLOG_POSTS[$slug];

        // Verificar si la vista existe
        if (! View::exists($view)) {
            abort(404);
        }

        return view($view);
    }

    /**
     * Obtener todos los slugs de blog válidos
     */
    public static function getAllSlugs(): array
    {
        return array_keys(self::BLOG_POSTS);
    }
}
