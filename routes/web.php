<?php

use App\Livewire\Counter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        // Si el admin está impersonando, no forzar cambio de contraseña
        if (!session()->has('impersonating') || session()->get('impersonating') !== true) {
            // PRIMERO verificar si necesita cambiar contraseña
            if ($user->password_must_reset) {
                return redirect()->route('password.force-reset');
            }
        }
        
        // LUEGO redirigir a dashboard
        return redirect()->route($user->role . '.dashboard');
    }
    
    // Si no está autenticado, mostrar landing page
    return view('welcome');
})->name('home');

// FAQ Page
Route::get('/faqs', function () {
    return view('faqs');
})->name('faqs');

// Páginas públicas de contenido SEO
Route::get('/que-es-sigpac', function () {
    return view('content.que-es-sigpac');
})->name('content.que-es-sigpac');

Route::get('/cuaderno-campo-digital-2027', function () {
    return view('content.cuaderno-campo-digital-2027');
})->name('content.cuaderno-campo-2027');

Route::get('/normativa-pac', function () {
    return view('content.normativa-pac-2027');
})->name('content.normativa-pac');

Route::get('/digitalizar-viñedo', function () {
    return view('content.digitalizar-viñedo');
})->name('content.digitalizar-viñedo');

Route::get('/comparativa-software-agricola', function () {
    return view('content.comparativa-software-agricola');
})->name('content.comparativa');

// Nuevas páginas SEO para búsquedas clave
Route::get('/software-para-viticultores', function () {
    return view('content.software-para-viticultores');
})->name('content.software-viticultores');

Route::get('/app-agricultura', function () {
    return view('content.app-agricultura');
})->name('content.app-agricultura');

Route::get('/cuaderno-digital-viticultores', function () {
    return view('content.cuaderno-digital-viticultores');
})->name('content.cuaderno-digital-viticultores');

// Páginas comerciales clave (alta prioridad SEO)
Route::get('/software-gestion-agricola', function () {
    return view('content.software-gestion-agricola');
})->name('content.software-gestion-agricola');

Route::get('/software-viticultura', function () {
    return view('content.software-viticultura');
})->name('content.software-viticultura');

Route::get('/software-bodegas', function () {
    return view('content.software-bodegas');
})->name('content.software-bodegas');

Route::get('/cuaderno-digital', function () {
    return view('content.cuaderno-digital');
})->name('content.cuaderno-digital');

Route::get('/sigpac', function () {
    return view('content.sigpac');
})->name('content.sigpac');

Route::get('/trazabilidad-agricola', function () {
    return view('content.trazabilidad-agricola');
})->name('content.trazabilidad-agricola');

// Páginas por sector (alta prioridad SEO)
Route::get('/viticultores', function () {
    return view('content.viticultores');
})->name('content.viticultores');

Route::get('/bodegas', function () {
    return view('content.bodegas');
})->name('content.bodegas');

Route::get('/cooperativas', function () {
    return view('content.cooperativas');
})->name('content.cooperativas');

Route::get('/ingenieros-agronomos', function () {
    return view('content.ingenieros-agronomos');
})->name('content.ingenieros-agronomos');

// Nuevas páginas SEO - Vendimia, Fitosanitarios, PAC, Plagas, Facturación
Route::get('/gestion-vendimia', function () {
    return view('content.gestion-vendimia');
})->name('content.gestion-vendimia');

Route::get('/registro-fitosanitarios', function () {
    return view('content.registro-fitosanitarios');
})->name('content.registro-fitosanitarios');

Route::get('/subvenciones-pac-2024', function () {
    return view('content.subvenciones-pac-2024');
})->name('content.subvenciones-pac');

Route::get('/control-plagas-viñedo', function () {
    return view('content.control-plagas-viñedo');
})->name('content.control-plagas');

Route::get('/facturacion-agricola', function () {
    return view('content.facturacion-agricola');
})->name('content.facturacion-agricola');

// Páginas SEO - Media prioridad
Route::get('/gestion-cuadrillas-agricolas', function () {
    return view('content.gestion-cuadrillas-agricolas');
})->name('content.gestion-cuadrillas');

Route::get('/maquinaria-agricola-registro', function () {
    return view('content.maquinaria-agricola-registro');
})->name('content.maquinaria-agricola');

Route::get('/plantaciones-viñedo-variedades', function () {
    return view('content.plantaciones-viñedo-variedades');
})->name('content.plantaciones-viñedo');

Route::get('/rendimientos-cosecha-viñedo', function () {
    return view('content.rendimientos-cosecha-viñedo');
})->name('content.rendimientos-cosecha');

Route::get('/informes-oficiales-agricultura', function () {
    return view('content.informes-oficiales-agricultura');
})->name('content.informes-oficiales');

// Páginas SEO - Contenido específico
Route::get('/ndvi-viñedo-teledeteccion', function () {
    return view('content.ndvi-viñedo-teledeteccion');
})->name('content.ndvi-teledeteccion');

Route::get('/calendario-viticola', function () {
    return view('content.calendario-viticola');
})->name('content.calendario-viticola');

Route::get('/trazabilidad-vino-origen', function () {
    return view('content.trazabilidad-vino-origen');
})->name('content.trazabilidad-vino');

Route::get('/firma-digital-agricultura', function () {
    return view('content.firma-digital-agricultura');
})->name('content.firma-digital');

Route::get('/gestion-campañas-agricolas', function () {
    return view('content.gestion-campañas-agricolas');
})->name('content.gestion-campañas');

// Páginas SEO - Regionales por DO
Route::get('/software-viticultores-rioja', function () {
    return view('content.software-viticultores-rioja');
})->name('content.viticultores-rioja');

Route::get('/software-viticultores-ribera-duero', function () {
    return view('content.software-viticultores-ribera-duero');
})->name('content.viticultores-ribera');

Route::get('/software-viticultores-rueda', function () {
    return view('content.software-viticultores-rueda');
})->name('content.viticultores-rueda');

Route::get('/software-viticultores-penedes', function () {
    return view('content.software-viticultores-penedes');
})->name('content.viticultores-penedes');

Route::get('/software-viticultores-la-mancha', function () {
    return view('content.software-viticultores-la-mancha');
})->name('content.viticultores-la-mancha');

// Blog
Route::get('/blog', function () {
    return view('blog.index');
})->name('blog.index');

Route::get('/blog/novedades-pac-2025', function () {
    return view('blog.novedades-pac-2025');
})->name('blog.pac-2025');

Route::get('/blog/errores-cuaderno-campo', function () {
    return view('blog.errores-cuaderno-campo');
})->name('blog.errores-cuaderno');

Route::get('/blog/calendario-viticola-2025', function () {
    return view('blog.calendario-viticola-2025');
})->name('blog.calendario-2025');

// Páginas SEO - Más DOs regionales
Route::get('/software-viticultores-priorat', function () {
    return view('content.software-viticultores-priorat');
})->name('content.viticultores-priorat');

Route::get('/software-viticultores-rias-baixas', function () {
    return view('content.software-viticultores-rias-baixas');
})->name('content.viticultores-rias-baixas');

Route::get('/software-viticultores-toro', function () {
    return view('content.software-viticultores-toro');
})->name('content.viticultores-toro');

Route::get('/software-viticultores-jumilla', function () {
    return view('content.software-viticultores-jumilla');
})->name('content.viticultores-jumilla');

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
})->name('health');

// Rutas públicas legales
Route::get('/privacidad', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terminos', fn() => view('legal.terms'))->name('terms');
Route::get('/cookies', fn() => view('legal.privacy'))->name('cookies'); // Misma vista que privacidad
Route::get('/aviso-legal', fn() => view('legal.aviso-legal'))->name('aviso-legal');

// Sitemap dinámico
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Verificación pública de informes oficiales
Route::get('/verify-report/{code}', [App\Http\Controllers\ReportVerificationController::class, 'verify'])
    ->name('reports.verify');

Route::get('/counter', Counter::class)->name('counter');

// Ruta de beta expirada (requiere auth)
Route::middleware('auth')->get('/beta/expired', \App\Livewire\Beta\Expired::class)->name('beta.expired');


// Ruta para forzar cambio de contraseña (debe estar fuera del middleware 'auth' principal)
Route::middleware('auth')->get('/password/force-reset', \App\Livewire\Auth\ForcePasswordReset::class)    ->name('password.force-reset');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'password.changed'])->group(function () {
    // password.changed debe ejecutarse ANTES de verified
    // Si tiene password_must_reset, redirige a force-reset sin verificar email
})->withoutMiddleware('verified'); // Asegurar que verified no se ejecute aquí

// Rutas protegidas: password cambiado Y email verificado
Route::middleware(['auth', 'password.changed', 'verified'])->group(function () {
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

    // Dashboards por rol
    require __DIR__ . '/admin.php';
    require __DIR__ . '/supervisor.php';
    require __DIR__ . '/winery.php';
    require __DIR__ . '/viticulturist.php';
    require __DIR__ . '/remote-sensing.php';
});
