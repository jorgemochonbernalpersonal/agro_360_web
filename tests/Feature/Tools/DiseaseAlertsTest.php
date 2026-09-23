<?php

namespace Tests\Feature\Tools;

use App\Livewire\Tools\DiseaseAlerts;
use App\Models\DiseaseAlertSubscription;
use App\Models\Municipality;
use App\Models\ToolLead;
use App\Notifications\DiseaseRiskAlertNotification;
use App\Notifications\NewToolLeadNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class DiseaseAlertsTest extends TestCase
{
    use RefreshDatabase;

    private Municipality $municipality;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-10 06:00', 'Europe/Madrid'));
        Cache::flush();
        RateLimiter::clear('disease-alerts:127.0.0.1');
        Notification::fake();

        $this->municipality = Municipality::create([
            'code' => '99999',
            'name' => 'Villaprueba de la Vid',
            'province_id' => \App\Models\Province::value('id'),
            'lat' => 38.76,
            'lng' => -3.38,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_page_is_public(): void
    {
        $this->get('/herramientas/alertas-mildiu-oidio')->assertOk()->assertSee('Alertas de mildiu y oídio');
    }

    public function test_search_suggests_municipalities_with_coordinates(): void
    {
        Municipality::create(['code' => '99998', 'name' => 'Villaprueba Sin Coordenadas', 'province_id' => $this->municipality->province_id]);

        Livewire::test(DiseaseAlerts::class)
            ->set('search', 'villaprueba')
            ->assertSee('Villaprueba de la Vid')
            ->assertDontSee('Villaprueba Sin Coordenadas');
    }

    public function test_shows_forecast_for_selected_municipality(): void
    {
        $this->fakeOpenMeteo(dailyRain: 6);

        $this->selected()
            ->assertSet('municipalityId', $this->municipality->id)
            ->assertSee('Riesgo en Villaprueba de la Vid')
            ->assertSee('Hoy')
            ->assertSee('Alto'); // 12 mm en 48 h a 15 °C → mildiu alto
    }

    public function test_weather_api_failure_shows_error_and_no_invented_data(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response('error', 500)]);

        $this->selected()
            ->assertSee('No hemos podido obtener la previsión')
            ->assertDontSee('Hoy');
    }

    public function test_subscribe_creates_subscription_and_lead(): void
    {
        $this->fakeOpenMeteo(dailyRain: 0);

        $this->selected()
            ->set('email', ' Viti@Example.com ')
            ->set('consent', true)
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('subscribed', true);

        $subscription = DiseaseAlertSubscription::sole();
        $this->assertSame('viti@example.com', $subscription->email);
        $this->assertSame($this->municipality->id, $subscription->municipality_id);
        $this->assertSame(48, strlen($subscription->token));

        $lead = ToolLead::sole();
        $this->assertSame(DiseaseAlerts::TOOL, $lead->tool);
        $this->assertSame('Villaprueba de la Vid', $lead->context['municipality']);
        Notification::assertSentTo(new AnonymousNotifiable, NewToolLeadNotification::class);
    }

    public function test_subscribe_requires_municipality_and_consent(): void
    {
        Livewire::test(DiseaseAlerts::class)
            ->set('email', 'viti@example.com')
            ->call('subscribe')
            ->assertHasErrors(['municipalityId', 'consent']);

        $this->assertDatabaseCount('disease_alert_subscriptions', 0);
    }

    public function test_resubscribing_reactivates_without_duplicates(): void
    {
        $this->fakeOpenMeteo(dailyRain: 0);
        $existing = DiseaseAlertSubscription::create([
            'email' => 'viti@example.com', 'municipality_id' => $this->municipality->id,
            'lat' => 38.76, 'lng' => -3.38, 'unsubscribed_at' => now(),
        ]);

        $this->selected()->set('email', 'viti@example.com')->set('consent', true)->call('subscribe');

        $this->assertDatabaseCount('disease_alert_subscriptions', 1);
        $this->assertNull($existing->fresh()->unsubscribed_at);
    }

    public function test_command_sends_alert_when_risk_is_high_and_respects_cooldown(): void
    {
        $this->fakeOpenMeteo(dailyRain: 6);
        $subscription = DiseaseAlertSubscription::create([
            'email' => 'viti@example.com', 'municipality_id' => $this->municipality->id, 'lat' => 38.76, 'lng' => -3.38,
        ]);

        $this->artisan('tools:send-disease-alerts')->assertSuccessful();

        Notification::assertSentOnDemand(DiseaseRiskAlertNotification::class, function ($notification, $channels, $notifiable) use ($subscription) {
            $mail = $notification->toMail($notifiable);

            return $notifiable->routes['mail'] === 'viti@example.com'
                && str_contains($mail->subject, 'mildiu')
                && str_contains(implode(' ', $mail->outroLines), $subscription->unsubscribeUrl());
        });
        $this->assertNotNull($subscription->fresh()->last_mildew_alert_at);

        // Al día siguiente no se repite (cooldown de 4 días).
        Carbon::setTestNow(now()->addDay());
        $this->artisan('tools:send-disease-alerts')->assertSuccessful();
        Notification::assertSentOnDemandTimes(DiseaseRiskAlertNotification::class, 1);
    }

    public function test_command_skips_low_risk(): void
    {
        $this->fakeOpenMeteo(dailyRain: 0);
        DiseaseAlertSubscription::create(['email' => 'a@example.com', 'municipality_id' => $this->municipality->id, 'lat' => 38.76, 'lng' => -3.38]);

        $this->artisan('tools:send-disease-alerts')->expectsOutputToContain('Alertas enviadas: 0')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_command_skips_unsubscribed_and_off_season(): void
    {
        $this->fakeOpenMeteo(dailyRain: 6);
        DiseaseAlertSubscription::create([
            'email' => 'a@example.com', 'municipality_id' => $this->municipality->id, 'lat' => 38.76, 'lng' => -3.38,
            'unsubscribed_at' => now(),
        ]);

        $this->artisan('tools:send-disease-alerts')->expectsOutputToContain('Alertas enviadas: 0')->assertSuccessful();

        DiseaseAlertSubscription::query()->update(['unsubscribed_at' => null]);
        Carbon::setTestNow(Carbon::parse('2026-12-10 06:00', 'Europe/Madrid'));
        $this->artisan('tools:send-disease-alerts')->expectsOutputToContain('Fuera de campaña')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_command_does_not_alert_when_weather_unavailable(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response('error', 500)]);
        DiseaseAlertSubscription::create(['email' => 'a@example.com', 'municipality_id' => $this->municipality->id, 'lat' => 38.76, 'lng' => -3.38]);

        $this->artisan('tools:send-disease-alerts')->expectsOutputToContain('sin datos meteo: 1')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_unsubscribe_asks_confirmation_then_unsubscribes(): void
    {
        $subscription = DiseaseAlertSubscription::create([
            'email' => 'viti@example.com', 'municipality_id' => $this->municipality->id, 'lat' => 38.76, 'lng' => -3.38,
        ]);

        // El GET (p. ej. un antivirus que abre el enlace) no da de baja.
        $this->get($subscription->unsubscribeUrl())->assertOk()->assertSee('Sí, darme de baja');
        $this->assertNull($subscription->fresh()->unsubscribed_at);

        $this->post(route('tools.disease-alerts.unsubscribe.confirm', $subscription->token))
            ->assertRedirect($subscription->unsubscribeUrl());
        $this->assertNotNull($subscription->fresh()->unsubscribed_at);

        $this->get($subscription->unsubscribeUrl())->assertSee('Te has dado de baja');
    }

    public function test_unknown_unsubscribe_token(): void
    {
        $this->get(route('tools.disease-alerts.unsubscribe', 'no-existe'))->assertOk()->assertSee('Enlace no válido');
    }

    /** Respuesta de Open-Meteo: 7 días de histórico + 6 de previsión, todos iguales. */
    private function fakeOpenMeteo(float $dailyRain, float $temp = 15): void
    {
        $time = $temps = $rh = $rain = [];
        $start = Carbon::parse('2026-06-03');
        for ($d = 0; $d < 13; $d++) {
            for ($h = 0; $h < 24; $h++) {
                $time[] = $start->copy()->addDays($d)->format('Y-m-d').sprintf('T%02d:00', $h);
                $temps[] = $temp;
                $rh[] = 60;
                $rain[] = $dailyRain / 24;
            }
        }

        Http::fake(['api.open-meteo.com/*' => Http::response(['hourly' => [
            'time' => $time,
            'temperature_2m' => $temps,
            'relative_humidity_2m' => $rh,
            'precipitation' => $rain,
        ]])]);
    }

    private function selected()
    {
        return Livewire::test(DiseaseAlerts::class)->call('selectMunicipality', $this->municipality->id);
    }
}
