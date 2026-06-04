<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\User;
use App\Models\WaterConcession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaterConcessionTest extends TestCase
{
    use RefreshDatabase;

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function test_concession_type_label_returns_correct_label(): void
    {
        $model = new WaterConcession(['concession_type' => 'superficial']);
        $this->assertEquals('Aguas superficiales', $model->concession_type_label);
    }

    public function test_concession_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'superficial' => 'Aguas superficiales',
            'subterranea' => 'Aguas subterráneas',
            'comunidad_regantes' => 'Comunidad de regantes',
            'otro' => 'Otro',
        ];

        foreach ($expected as $key => $label) {
            $model = new WaterConcession(['concession_type' => $key]);
            $this->assertEquals($label, $model->concession_type_label, "Failed for type: {$key}");
        }
    }

    public function test_concession_type_label_returns_raw_value_for_unknown_type(): void
    {
        $model = new WaterConcession(['concession_type' => 'tipo_raro']);
        $this->assertEquals('tipo_raro', $model->concession_type_label);
    }

    // ── is_expired ─────────────────────────────────────────────────────────────

    public function test_is_expired_returns_true_when_expiry_date_is_past(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->subYear()]);
        $this->assertTrue($model->is_expired);
    }

    public function test_is_expired_returns_false_when_expiry_date_is_future(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->addYear()]);
        $this->assertFalse($model->is_expired);
    }

    public function test_is_expired_returns_false_when_no_expiry_date(): void
    {
        $model = new WaterConcession(['expiry_date' => null]);
        $this->assertFalse($model->is_expired);
    }

    // ── is_expiring_soon (90-day window) ──────────────────────────────────────

    public function test_is_expiring_soon_returns_true_when_within_90_days(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->addDays(45)]);
        $this->assertTrue($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_true_on_boundary_of_90_days(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->addDays(90)]);
        $this->assertTrue($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_beyond_90_days(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->addDays(120)]);
        $this->assertFalse($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_already_expired(): void
    {
        $model = new WaterConcession(['expiry_date' => now()->subDay()]);
        $this->assertFalse($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_no_expiry_date(): void
    {
        $model = new WaterConcession(['expiry_date' => null]);
        $this->assertFalse($model->is_expiring_soon);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function test_water_concession_belongs_to_viticulturist(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $concession = WaterConcession::create([
            'viticulturist_id' => $user->id,
            'concession_type' => 'superficial',
            'concession_number' => 'CON-001',
            'water_body' => 'Río Test',
            'authority' => 'CHE',
            'concession_date' => now()->subYear(),
            'expiry_date' => now()->addYears(5),
            'max_volume_m3' => 5000,
            'active' => true,
        ]);

        $this->assertEquals($user->id, $concession->viticulturist->id);
    }

    public function test_water_concession_belongs_to_campaign(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $concession = WaterConcession::create([
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'concession_type' => 'subterranea',
            'concession_number' => 'CON-002',
            'water_body' => 'Acuífero Test',
            'authority' => 'CHJ',
            'concession_date' => now()->subYear(),
            'expiry_date' => now()->addYears(3),
            'max_volume_m3' => 3000,
            'active' => true,
        ]);

        $this->assertEquals($campaign->id, $concession->campaign->id);
    }
}
