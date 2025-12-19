<?php

use App\Livewire\Counter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        // PRIMERO verificar si necesita cambiar contraseña
        if ($user->password_must_reset) {
            return redirect()->route('password.force-reset');
        }
        
        // LUEGO redirigir a dashboard
        return redirect()->route($user->role . '.dashboard');
    }
    
    // Si no está autenticado, mostrar landing page
    return view('welcome');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

// Ruta para forzar cambio de contraseña (debe estar fuera del middleware 'auth' principal)
Route::middleware('auth')->get('/password/force-reset', \App\Livewire\Auth\ForcePasswordReset::class)    ->name('password.force-reset');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'password.changed'])->group(function () {
    // password.changed debe ejecutarse ANTES de verified
    // Si tiene password_must_reset, redirige a force-reset sin verificar email
})->withoutMiddleware('verified'); // Asegurar que verified no se ejecute aquí

// Rutas protegidas: password cambiado Y email verificado
Route::middleware(['auth', 'password.changed', 'verified'])->group(function () {
    require __DIR__ . '/plots.php';
    require __DIR__ . '/sigpac.php';
    require __DIR__ . '/config.php';
    require __DIR__ . '/profile.php';
    require __DIR__ . '/subscription.php';
    require __DIR__ . '/payment.php';

    // Dashboards por rol
    require __DIR__ . '/admin.php';
    require __DIR__ . '/supervisor.php';
    require __DIR__ . '/winery.php';
    require __DIR__ . '/viticulturist.php';
});
