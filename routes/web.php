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
});
