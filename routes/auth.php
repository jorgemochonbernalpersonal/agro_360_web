<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
    Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)
        ->name('password.reset')
        ->where('token', '.*'); // Permitir cualquier carácter en el token (incluye caracteres especiales)
});

Route::middleware('auth')->group(function () {
    // Cambio de contraseña obligatorio (debe estar antes de otras rutas protegidas)
    Route::get('/change-password-required', \App\Livewire\Auth\ChangePasswordRequired::class)
        ->name('auth.change-password-required');
    
    Route::post('/logout', function () {
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
                : ($user->isWinery() ? 'winery.dashboard' : 'viticulturist.dashboard'));

            return redirect()->route($dashboardRoute);
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('verification.verified')
            ->with('verified', true);
    })->middleware(['signed'])->name('verification.verify');

    // Página de éxito de verificación
    Route::get('/email/verified', function () {
        if (!auth()->check() || !auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        
        $dashboardRoute = auth()->user()->isAdmin() ? 'admin.dashboard' : 
            (auth()->user()->isSupervisor() ? 'supervisor.dashboard' : 
            (auth()->user()->isWinery() ? 'winery.dashboard' : 'viticulturist.dashboard'));
        
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
                ($user->isWinery() ? 'winery.dashboard' : 'viticulturist.dashboard'));
            
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
