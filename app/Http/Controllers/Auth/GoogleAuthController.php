<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            SecurityLogger::logSecurityEvent('google_oauth_error', [
                'error' => $e->getMessage(),
                'ip'    => request()->ip(),
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'No se pudo completar la autenticación con Google. Inténtalo de nuevo.']);
        }

        // 1. Find by google_id (returning user)
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. Find by email (existing account without Google linked)
        if (! $user) {
            $user = User::where('email', strtolower($googleUser->getEmail()))->first();

            if ($user) {
                // Si es un ghost (can_login=false), activarlo — Google ya verificó el email
                if (! $user->can_login) {
                    $user->update([
                        'google_id'         => $googleUser->getId(),
                        'can_login'         => true,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'invitation_token'      => null,
                        'invitation_expires_at' => null,
                        'invitation_sent_at'    => null,
                    ]);
                } else {
                    // Link Google to existing active account
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }
        }

        // 3. Create new user
        if (! $user) {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => strtolower($googleUser->getEmail()),
                'google_id'         => $googleUser->getId(),
                'email_verified_at' => now(), // Google already verified the email
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'viticulturist',
            ]);
        }

        // Guard: account must be active (shouldn't happen after ghost activation above)
        if (! $user->can_login) {
            SecurityLogger::logSecurityEvent('google_login_blocked_can_login', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta no está activada. Contacta con el administrador.']);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended($this->dashboardRoute($user));
    }

    private function dashboardRoute(User $user): string
    {
        return route(match ($user->role) {
            'admin'        => 'admin.dashboard',
            'supervisor'   => 'supervisor.dashboard',
            'winery'       => 'winery.dashboard',
            'producer'     => 'producer.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            default        => 'home',
        });
    }
}
