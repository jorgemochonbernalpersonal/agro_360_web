<?php

namespace Tests\Feature\Tools;

use App\Http\Controllers\ToolsController;
use App\Livewire\Tools\HarvestYieldCalculator;
use App\Livewire\Tools\LeadCapture;
use App\Livewire\Tools\NotebookReadinessCheck;
use App\Livewire\Tools\SprayCalculator;
use App\Models\ToolLead;
use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PublicToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tools_index_is_public(): void
    {
        $response = $this->get('/herramientas');

        $response->assertOk();
        foreach (ToolsController::TOOLS as $slug => $tool) {
            $response->assertSee(route('tools.show', $slug), false);
        }
    }

    public function test_every_tool_page_is_public(): void
    {
        foreach (ToolsController::TOOLS as $slug => $tool) {
            $this->get("/herramientas/{$slug}")
                ->assertOk()
                ->assertSee('<link rel="canonical" href="'.url("/herramientas/{$slug}").'">', false);
        }
    }

    public function test_unknown_tool_is_404(): void
    {
        $this->get('/herramientas/no-existe')->assertNotFound();
    }

    public function test_tools_are_in_the_sitemap(): void
    {
        $locs = array_column(app(SitemapService::class)->getUrls(), 'loc');

        $this->assertContains(url('/herramientas'), $locs);
        foreach (ToolsController::slugs() as $slug) {
            $this->assertContains(url("/herramientas/{$slug}"), $locs);
        }
    }

    public function test_spray_calculator_computes_tanks(): void
    {
        Livewire::test(SprayCalculator::class)
            ->set('hectares', 3)
            ->set('litresPerHectare', 400)
            ->set('tankLitres', 1000)
            ->set('products.0.name', 'Azufre')
            ->set('products.0.dose', 4)
            ->assertHasNoErrors()
            ->assertSee('1.200 L')
            ->assertSee('Azufre');
    }

    public function test_spray_calculator_shows_no_result_with_invalid_input(): void
    {
        $component = Livewire::test(SprayCalculator::class)
            ->set('products.0.dose', 4)
            ->set('hectares', 0)
            ->assertHasErrors(['hectares']);

        $this->assertNull($component->instance()->result());
    }

    public function test_spray_calculator_add_and_remove_products(): void
    {
        $component = Livewire::test(SprayCalculator::class);

        for ($i = 0; $i < SprayCalculator::MAX_PRODUCTS + 2; $i++) {
            $component->call('addProduct');
        }
        $component->assertCount('products', SprayCalculator::MAX_PRODUCTS);

        $component->call('removeProduct', 0)
            ->assertCount('products', SprayCalculator::MAX_PRODUCTS - 1);
    }

    public function test_harvest_calculator_warns_over_limit(): void
    {
        Livewire::test(HarvestYieldCalculator::class)
            ->set('hectares', 1)
            ->set('rowSpacing', 2.5)
            ->set('vineSpacing', 1.2)
            ->set('missingPercent', 10)
            ->set('clustersPerVine', 12)
            ->set('clusterWeight', 200)
            ->set('limitKgPerHectare', 6000)
            ->assertHasNoErrors()
            ->assertSee('7.200 kg')
            ->assertSee('Superas el límite');
    }

    public function test_readiness_check_requires_all_answers(): void
    {
        Livewire::test(NotebookReadinessCheck::class)
            ->set('answers.digital', true)
            ->call('submit')
            ->assertHasErrors('answers')
            ->assertSet('submitted', false);
    }

    public function test_readiness_check_shows_score_and_lead_form(): void
    {
        $component = Livewire::test(NotebookReadinessCheck::class);
        foreach (array_keys($component->get('answers')) as $key) {
            $component->set("answers.{$key}", $key !== 'fertilization');
        }

        $component->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('88%')
            ->assertSee('Registras también las fertilizaciones')
            ->assertSeeLivewire(LeadCapture::class);
    }

    public function test_lead_capture_stores_lead_with_context(): void
    {
        Livewire::test(LeadCapture::class, ['tool' => 'test-cuaderno-digital-2027', 'context' => ['score' => 75]])
            ->set('email', ' Viticultor@Example.com ')
            ->set('profile', 'viticultor')
            ->set('consent', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $lead = ToolLead::sole();
        $this->assertSame('viticultor@example.com', $lead->email);
        $this->assertSame('test-cuaderno-digital-2027', $lead->tool);
        $this->assertSame(['score' => 75], $lead->context);
        $this->assertNotNull($lead->consented_at);
    }

    public function test_lead_capture_requires_consent_and_valid_email(): void
    {
        Livewire::test(LeadCapture::class, ['tool' => 'tools-index'])
            ->set('email', 'no-es-un-email')
            ->call('save')
            ->assertHasErrors(['email', 'consent']);

        $this->assertDatabaseCount('tool_leads', 0);
    }

    public function test_lead_capture_honeypot_discards_bots(): void
    {
        Livewire::test(LeadCapture::class, ['tool' => 'tools-index'])
            ->set('email', 'bot@example.com')
            ->set('consent', true)
            ->set('website', 'http://spam.example')
            ->call('save')
            ->assertSet('sent', true);

        $this->assertDatabaseCount('tool_leads', 0);
    }

    public function test_lead_capture_is_rate_limited(): void
    {
        RateLimiter::clear('tool-lead:127.0.0.1');

        for ($i = 0; $i < LeadCapture::MAX_ATTEMPTS_PER_HOUR; $i++) {
            Livewire::test(LeadCapture::class, ['tool' => 'tools-index'])
                ->set('email', "user{$i}@example.com")
                ->set('consent', true)
                ->call('save');
        }

        Livewire::test(LeadCapture::class, ['tool' => 'tools-index'])
            ->set('email', 'one-more@example.com')
            ->set('consent', true)
            ->call('save')
            ->assertHasErrors('email')
            ->assertSet('sent', false);

        $this->assertDatabaseCount('tool_leads', LeadCapture::MAX_ATTEMPTS_PER_HOUR);
    }
}
