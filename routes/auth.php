<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Página intermedia de apertura para notificaciones (web o app móvil) ──────
Route::get('/app/open', [\App\Http\Controllers\AppRedirectController::class, 'open'])
    ->name('app.open');

// ─── Verificación de email (sin sesión web, compatible con app móvil) ─────────
// Seguridad garantizada por: URL firmada (signed) + hash del email del usuario.

Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Enlace de verificación inválido.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // Móvil: parámetro firmado incluido en la URL por MobileVerifyEmailNotification
    if ($request->query('platform') === 'mobile') {
        return view('auth.email-verified-mobile');
    }

    // Web con sesión activa → flujo web normal
    if (auth()->check()) {
        return redirect()->route('verification.verified');
    }

    // Web sin sesión (incógnito / otro dispositivo) → login con email pre-rellenado
    return redirect()->route('login')->with('verified_email', $user->email);
})->middleware(['signed'])->name('verification.verify');

// ─── Google OAuth ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
    Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)
        ->name('password.reset')
        ->where('token', '.*'); // Permitir cualquier carácter en el token (incluye caracteres especiales)
});

// Página de éxito de reset de contraseña desde app móvil (sin sesión, sin middleware)
Route::get('/password/reset/mobile-success', function () {
    return view('auth.password-reset-mobile');
})->name('password.reset.mobile-success');

Route::middleware('auth')->group(function () {
    // Cambio de contraseña obligatorio (debe estar antes de otras rutas protegidas)
    Route::get('/change-password-required', \App\Livewire\Auth\ChangePasswordRequired::class)
        ->name('auth.change-password-required');

    Route::post('/logout', function () {
        $user = Auth::user();
        if ($user) {
            \App\Services\SecurityLogger::logSecurityEvent('user_logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        }
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    // Rutas de verificación de email
    Route::get('/email/verify', function () {
        $user = auth()->user();

        // Si el email ya está verificado, redirigir directamente al dashboard según el rol
        if ($user && $user->hasVerifiedEmail()) {
            $dashboardRoute = $user->isAdmin() ? 'admin.dashboard'
                : ($user->isSupervisor() ? 'supervisor.dashboard'
                : ($user->isWinery() ? 'winery.dashboard'
                : ($user->isProducer() ? 'producer.dashboard' : 'viticulturist.dashboard')));

            return redirect()->route($dashboardRoute);
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    // La ruta verification.verify está fuera de este grupo (ver abajo) para soportar
    // verificación desde app móvil sin sesión web activa.

    // Página de éxito de verificación
    Route::get('/email/verified', function () {
        if (! auth()->check() || ! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $dashboardRoute = auth()->user()->isAdmin() ? 'admin.dashboard' :
            (auth()->user()->isSupervisor() ? 'supervisor.dashboard' :
            (auth()->user()->isWinery() ? 'winery.dashboard' :
            (auth()->user()->isProducer() ? 'producer.dashboard' : 'viticulturist.dashboard')));

        return view('auth.email-verified', [
            'dashboardRoute' => $dashboardRoute,
        ]);
    })->name('verification.verified');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');

    // Endpoint para verificar estado de verificación (AJAX)
    Route::get('/email/verify/check', function (Request $request) {
        $user = $request->user();
        $verified = $user->hasVerifiedEmail();

        if ($verified) {
            $dashboardRoute = $user->isAdmin() ? 'admin.dashboard' :
                ($user->isSupervisor() ? 'supervisor.dashboard' :
                ($user->isWinery() ? 'winery.dashboard' :
                ($user->isProducer() ? 'producer.dashboard' : 'viticulturist.dashboard')));

            return response()->json([
                'verified' => true,
                'redirect_url' => route($dashboardRoute),
            ]);
        }

        return response()->json([
            'verified' => false,
        ]);
    })->name('verification.check');
});
