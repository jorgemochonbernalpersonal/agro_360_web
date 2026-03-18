<?php

namespace Tests;

use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Whether the DB has been migrated fresh for this test run.
     * We only migrate:fresh ONCE per process to avoid CSRF token issues.
     */
    protected static bool $migrated = false;

    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            static::$migrated = true;
        }

        // Truncate user-generated data between tests (keeps schema + geography).
        $this->truncateUserData();
    }

    protected function tearDown(): void
    {
        // Clear browser cookies so CSRF tokens from previous test don't bleed over.
        if ($this->hasBrowser()) {
            $this->browse(function ($browser) {
                $browser->deleteCookie('laravel_session')
                        ->deleteCookie('XSRF-TOKEN');
            });
        }
        parent::tearDown();
    }

    /**
     * Truncate tables that tests write to, preserving schema and geography data.
     */
    protected function truncateUserData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->testableTables() as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Tables written to during tests. Extend in subclasses if needed.
     */
    protected function testableTables(): array
    {
        return [
            'users', 'plots', 'campaigns', 'wines', 'wine_process_details',
            'wine_analyses', 'containers', 'product_lots', 'product_lot_movements',
            'bottlings', 'labelings', 'label_batches', 'invoice_items', 'invoices',
            'sessions', 'cache', 'jobs', 'failed_jobs',
            'personal_access_tokens', 'password_reset_tokens',
            'winery_viticulturists', 'notifications',
        ];
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-dev-shm-usage',
            '--no-sandbox',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    // ─── User helpers ────────────────────────────────────────────────────────

    protected function createUser(string $role, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'role'                => $role,
            'password'            => bcrypt('password'),
            'email_verified_at'   => now(),
            'can_login'           => true,
            'password_must_reset' => false,
            'is_beta_user'        => true,
            'beta_ends_at'        => now()->addYear(),
            'beta_access_granted' => true,
        ], $extra));
    }

    protected function createWinery(array $extra = []): User        { return $this->createUser('winery', $extra); }
    protected function createViticulturist(array $extra = []): User  { return $this->createUser('viticulturist', $extra); }
    protected function createProducer(array $extra = []): User       { return $this->createUser('producer', $extra); }

    // ─── Navigation helpers ──────────────────────────────────────────────────

    protected function dashboardUrl(User $user): string
    {
        return match ($user->role) {
            'winery'        => '/winery/dashboard',
            'producer'      => '/producer/dashboard',
            'viticulturist' => '/viticulturist/dashboard',
            'supervisor'    => '/supervisor/dashboard',
            default         => '/',
        };
    }

    /**
     * Login via real Livewire form.
     * Use only in LoginTest — other tests should use loginAs().
     */
    protected function loginViaForm($browser, User $user): void
    {
        $browser->visit('/login')
            ->waitFor('[wire\\:model="email"]', 5)
            ->type('[wire\\:model="email"]', $user->email)
            ->type('[wire\\:model="password"]', 'password')
            ->pause(500)
            ->press('Iniciar Sesión')
            ->waitForLocation($this->dashboardUrl($user), 15);
    }

    /**
     * Fast session-based login. Use for all tests that aren't testing auth itself.
     */
    protected function loginAs($browser, User $user): void
    {
        $browser->loginAs($user)->visit($this->dashboardUrl($user))->waitFor('body', 5);
    }

    /**
     * Check if a browser instance is active (for tearDown).
     */
    private function hasBrowser(): bool
    {
        return !empty(static::$browsers) && static::$browsers->isNotEmpty();
    }
}
