<?php

namespace Tests\Browser\Auth;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Flujo crítico: Login real por formulario para cada rol.
 *
 * Los tests de form login esperan la redirección SPA de Livewire.
 * El CSRF es el principal riesgo: se resuelve borrando cookies en tearDown.
 */
class LoginTest extends DuskTestCase
{
    #[Test]
    public function viticulturist_can_login_and_lands_on_dashboard(): void
    {
        $user = $this->createViticulturist(['email' => 'vitic@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->waitFor('[wire\\:model="email"]', 8)
                ->assertSee('Iniciar Sesión')
                ->type('[wire\\:model="email"]', $user->email)
                ->type('[wire\\:model="password"]', 'password')
                ->pause(500)
                ->click('button[type="submit"]')
                ->waitForLocation('/viticulturist/dashboard', 20)
                ->assertPathBeginsWith('/viticulturist')
                ->screenshot('login-viticulturist-success');
        });
    }

    #[Test]
    public function winery_can_login_and_lands_on_dashboard(): void
    {
        $user = $this->createWinery(['email' => 'bodega@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->waitFor('[wire\\:model="email"]', 8)
                ->type('[wire\\:model="email"]', $user->email)
                ->type('[wire\\:model="password"]', 'password')
                ->pause(500)
                ->click('button[type="submit"]')
                ->waitForLocation('/winery/dashboard', 20)
                ->assertPathBeginsWith('/winery')
                ->screenshot('login-winery-success');
        });
    }

    #[Test]
    public function producer_can_login_and_lands_on_dashboard(): void
    {
        $user = $this->createProducer(['email' => 'producer@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->waitFor('[wire\\:model="email"]', 8)
                ->type('[wire\\:model="email"]', $user->email)
                ->type('[wire\\:model="password"]', 'password')
                ->pause(500)
                ->click('button[type="submit"]')
                ->waitForLocation('/producer/dashboard', 20)
                ->assertPathBeginsWith('/producer')
                ->screenshot('login-producer-success');
        });
    }

    #[Test]
    public function wrong_password_shows_error(): void
    {
        $user = $this->createViticulturist(['email' => 'fail@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->waitFor('[wire\\:model="email"]', 8)
                ->type('[wire\\:model="email"]', $user->email)
                ->type('[wire\\:model="password"]', 'wrongpassword')
                ->pause(500)
                ->click('button[type="submit"]')
                ->pause(500)
                ->assertPathIs('/login')
                ->assertSee('credenciales')
                ->screenshot('login-wrong-password');
        });
    }

    #[Test]
    public function login_redirects_authenticated_user_away_from_login(): void
    {
        $user = $this->createWinery(['email' => 'already@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/login')
                ->waitFor('body', 8)
                // Debe redirigir fuera de /login
                ->assertPathIsNot('/login')
                ->screenshot('login-already-authed-redirect');
        });
    }

    #[Test]
    public function logout_clears_session_and_redirects_to_login(): void
    {
        $user = $this->createWinery(['email' => 'logout@test.com']);

        $this->browse(function (Browser $browser) use ($user) {
            // Login vía sesión
            $browser->loginAs($user)
                ->visit('/winery/dashboard')
                ->waitFor('body', 8)
                ->assertPathBeginsWith('/winery');

            // Logout via POST es el estándar en Laravel. Visitamos la ruta GET /logout si existe
            // o usamos JS para hacer el submit del form de logout.
            $browser->script("
                fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.content ?? '',
                        'Content-Type': 'application/json'
                    }
                }).then(() => window.location.href = '/login');
            ");

            $browser->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->screenshot('logout-redirect');
        });
    }
}
