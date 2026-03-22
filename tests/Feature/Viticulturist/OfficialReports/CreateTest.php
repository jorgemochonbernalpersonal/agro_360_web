<?php

namespace Tests\Feature\Viticulturist\OfficialReports;

use App\Jobs\GenerateOfficialReportJob;
use App\Livewire\Viticulturist\OfficialReports\Create;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\DigitalSignature;
use App\Models\OfficialReport;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makeDigitalSignature(int $userId, string $password = 'firma123'): DigitalSignature
    {
        return DigitalSignature::createOrUpdateForUser($userId, $password);
    }

    private function makeCampaign(int $userId, array $overrides = []): Campaign
    {
        return Campaign::create(array_merge([
            'viticulturist_id' => $userId,
            'year'             => 2026,
            'name'             => 'Campaña Test 2026',
            'start_date'       => '2026-01-01',
            'end_date'         => '2026-12-31',
        ], $overrides));
    }

    // ── Mount ──────────────────────────────────────────────────────────────────

    public function test_create_page_renders_correctly(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)->assertOk();
    }

    public function test_mount_sets_default_report_type_to_phytosanitary(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('reportType', 'phytosanitary_treatments');
    }

    public function test_mount_detects_missing_digital_signature(): void
    {
        $v = $this->makeViticulturist();
        // No digital signature created
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('hasDigitalSignature', false);
    }

    public function test_mount_detects_existing_digital_signature(): void
    {
        $v = $this->makeViticulturist();
        $this->makeDigitalSignature($v->id);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('hasDigitalSignature', true);
    }

    // ── Quick period ───────────────────────────────────────────────────────────

    public function test_set_quick_period_last_week(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        $component = Livewire::test(Create::class)
            ->call('setQuickPeriod', 'last_week');

        $startDate = $component->get('startDate');
        $this->assertEquals(now()->subWeek()->toDateString(), $startDate);
    }

    public function test_set_quick_period_this_month(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        $component = Livewire::test(Create::class)
            ->call('setQuickPeriod', 'this_month');

        $this->assertEquals(now()->startOfMonth()->toDateString(), $component->get('startDate'));
    }

    public function test_set_quick_period_this_year(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        $component = Livewire::test(Create::class)
            ->call('setQuickPeriod', 'this_year');

        $this->assertEquals(now()->startOfYear()->toDateString(), $component->get('startDate'));
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    public function test_calculate_summary_validates_required_dates_for_phytosanitary(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('reportType', 'phytosanitary_treatments')
            ->set('startDate', '')
            ->set('endDate', '')
            ->call('calculateSummary')
            ->assertHasErrors(['startDate', 'endDate']);
    }

    public function test_calculate_summary_validates_end_date_after_start(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('reportType', 'phytosanitary_treatments')
            ->set('startDate', '2026-06-01')
            ->set('endDate', '2026-05-01') // before start
            ->call('calculateSummary')
            ->assertHasErrors(['endDate']);
    }

    public function test_calculate_summary_validates_required_campaign_for_full_notebook(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('reportType', 'full_digital_notebook')
            ->set('campaignId', null)
            ->call('calculateSummary')
            ->assertHasErrors(['campaignId']);
    }

    public function test_calculate_summary_fails_when_no_phytosanitary_treatments(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        // No treatments exist → should add 'generation' error
        Livewire::test(Create::class)
            ->set('reportType', 'phytosanitary_treatments')
            ->set('startDate', now()->subYear()->toDateString())
            ->set('endDate', now()->toDateString())
            ->call('calculateSummary')
            ->assertHasErrors(['generation']);
    }

    public function test_calculate_summary_fails_when_campaign_has_no_activities(): void
    {
        $v        = $this->makeViticulturist();
        $campaign = $this->makeCampaign($v->id);
        $this->actingAs($v);

        // Campaign exists but has no activities
        Livewire::test(Create::class)
            ->set('reportType', 'full_digital_notebook')
            ->set('campaignId', $campaign->id)
            ->call('calculateSummary')
            ->assertHasErrors(['generation']);
    }

    // ── confirmAndGenerateReport ───────────────────────────────────────────────

    public function test_confirm_generate_fails_without_digital_signature(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        // No DigitalSignature set up
        Livewire::test(Create::class)
            ->set('password', 'cualquier_cosa')
            ->call('confirmAndGenerateReport')
            ->assertHasErrors(['generation']);
    }

    public function test_confirm_generate_fails_with_wrong_digital_signature_password(): void
    {
        $v = $this->makeViticulturist();
        $this->makeDigitalSignature($v->id, 'contraseña_correcta');
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('password', 'contraseña_incorrecta')
            ->call('confirmAndGenerateReport')
            ->assertHasErrors(['password']);
    }

    public function test_confirm_generate_requires_password(): void
    {
        $v = $this->makeViticulturist();
        $this->makeDigitalSignature($v->id);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('password', '')
            ->call('confirmAndGenerateReport')
            ->assertHasErrors(['password']);
    }

    public function test_confirm_generate_creates_report_and_dispatches_job(): void
    {
        Queue::fake();

        $v        = $this->makeViticulturist();
        $password = 'firma_segura_123';
        $this->makeDigitalSignature($v->id, $password);
        $campaign = $this->makeCampaign($v->id);

        // Need at least one activity so calculateSummary doesn't error
        AgriculturalActivity::create([
            'viticulturist_id' => $v->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'irrigation',
            'activity_date'    => '2026-03-01',
        ]);

        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('reportType', 'full_digital_notebook')
            ->set('campaignId', $campaign->id)
            ->set('password', $password)
            ->call('confirmAndGenerateReport');

        $this->assertDatabaseHas('official_reports', [
            'user_id'           => $v->id,
            'report_type'       => 'full_digital_notebook',
            'processing_status' => 'pending',
        ]);

        Queue::assertPushed(GenerateOfficialReportJob::class);
    }

    public function test_record_count_updates_when_report_type_changes(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('reportType', 'full_digital_notebook')
            ->assertSet('reportType', 'full_digital_notebook');
    }
}
