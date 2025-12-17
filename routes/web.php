<?php

use App\Livewire\Counter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        // Si está autenticado, redirigir a su dashboard según el rol
        $user = auth()->user();
        return redirect()->route($user->role . '.dashboard');
    }
    // Si no está autenticado, redirigir al login
    return redirect()->route('login');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'require.password.change', 'verified'])->group(function () {
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
