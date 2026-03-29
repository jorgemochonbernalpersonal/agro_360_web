<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// FAQ Page
Route::get('/faqs', function () {
    return view('faqs');
})->name('faqs');

// Páginas públicas de contenido SEO
Route::permanentRedirect('/servicios', '/software-gestion-agricola');
Route::permanentRedirect('/noticias', '/blog');
Route::permanentRedirect('/contacto', '/faqs');
Route::permanentRedirect('/quienes-somos', '/');
Route::permanentRedirect('/tutoriales', '/blog');
Route::permanentRedirect('/subvenciones-pac-2024', '/subvenciones-pac');

// ✅ REFACTOR: Rutas de contenido SEO dinámicas
// Todas las páginas de contenido ahora pasan por ContentController
Route::get('/{slug}', [App\Http\Controllers\ContentController::class, 'show'])
    ->where('slug', implode('|', App\Http\Controllers\ContentController::getAllSlugs()))
    ->name('content.show');

// ✅ REFACTOR: Rutas de blog dinámicas
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'show'])
    ->where('slug', implode('|', App\Http\Controllers\BlogController::getAllSlugs()))
    ->name('blog.show');

// Dusk debug (solo testing)
Route::get('/dusk-env', function () {
    return response()->json([
        'env'     => app()->environment(),
        'db'      => DB::connection()->getDatabaseName(),
        'session' => config('session.driver'),
    ]);
})->middleware('throttle:60,1');

// Health Check Endpoint para UptimeRobot y monitoreo
Route::get('/health', function () {
    try {
        // Verificar conexión a base de datos
        \DB::connection()->getPdo();
        
        // Verificar que la aplicación está funcionando
        $checks = [
            'status' => 'ok',
            'database' => 'connected',
            'timestamp' => now()->toIso8601String(),
        ];
        
        return response()->json($checks, 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Service unavailable',
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }
})->middleware('throttle:10,1')->name('health');

// Rutas públicas legales
Route::get('/privacidad', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terminos', fn() => view('legal.terms'))->name('terms');
Route::get('/cookies', fn() => view('legal.cookies'))->name('cookies');
Route::get('/aviso-legal', fn() => view('legal.aviso-legal'))->name('aviso-legal');

// Sitemap dinámico
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Verificación pública de informes oficiales
Route::get('/verify-report/{code}', [App\Http\Controllers\ReportVerificationController::class, 'verify'])
    ->name('reports.verify');

// Trazabilidad pública de vinos (sin auth, por token UUID)
Route::get('/vino/{token}', [App\Http\Controllers\WineTraceController::class, 'show'])
    ->name('wine.trace');

// Ruta de beta expirada (requiere auth)
Route::middleware('auth')->get('/beta/expired', \App\Livewire\Beta\Expired::class)->name('beta.expired');


// Ruta para forzar cambio de contraseña (debe estar fuera del middleware 'auth' principal)
Route::middleware('auth')->get('/password/force-reset', \App\Livewire\Auth\ForcePasswordReset::class)    ->name('password.force-reset');

require __DIR__ . '/auth.php';

// Activación de cuenta por invitación de bodega (pública, sin auth)
Route::middleware('guest')->get('/activar-cuenta/{token}', \App\Livewire\Auth\ClaimAccount::class)
    ->name('auth.claim-account');

// Rutas protegidas: password cambiado Y email verificado
// require.password.change debe ejecutarse ANTES de verified para usuarios creados por otro
Route::middleware(['auth', 'password.changed', 'require.password.change', 'verified'])->group(function () {
    // Laravel Log Viewer - Solo para administradores
    Route::get('logs', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a los logs.');
        }
        return app('\Rap2hpoutre\LaravelLogViewer\LogViewerController')->index();
    })->name('logs');
    
    require __DIR__ . '/plots.php';
    require __DIR__ . '/map.php';
    require __DIR__ . '/sigpac.php';
    // require __DIR__ . '/config.php'; // Eliminado - no es útil
    require __DIR__ . '/profile.php';
    require __DIR__ . '/subscription.php';
    require __DIR__ . '/payment.php';

    // Dashboard combinado para productor
    Route::middleware(['role:producer', 'check.beta'])
        ->get('/producer/dashboard', \App\Livewire\Producer\Dashboard::class)
        ->name('producer.dashboard');

    // Dashboards por rol
    require __DIR__ . '/admin.php';
    require __DIR__ . '/supervisor.php';
    require __DIR__ . '/winery.php';
    require __DIR__ . '/viticulturist.php';
    require __DIR__ . '/producer.php';
    require __DIR__ . '/remote-sensing.php';
    require __DIR__ . '/do.php';
});
