<?php

namespace Tests\Unit\Models;

use App\Models\AgriculturalActivity;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestTest extends TestCase
{
    use RefreshDatabase;

    // ── Date casts ────────────────────────────────────────────────────────────

    public function test_harvest_start_date_is_cast_to_date(): void
    {
        $harvest = $this->makeHarvest(['harvest_start_date' => '2026-09-01']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $harvest->harvest_start_date);
        $this->assertEquals('2026-09-01', $harvest->harvest_start_date->format('Y-m-d'));
    }

    public function test_harvest_end_date_nullable(): void
    {
        $harvest = $this->makeHarvest(['harvest_end_date' => null]);

        $this->assertNull($harvest->harvest_end_date);
    }

    // ── Decimal casts ─────────────────────────────────────────────────────────

    public function test_total_weight_cast_decimal3(): void
    {
        $harvest = $this->makeHarvest(['total_weight' => 1234.5]);

        $this->assertIsString($harvest->total_weight);
        $this->assertEquals('1234.500', $harvest->total_weight);
    }

    public function test_brix_degree_cast_decimal3(): void
    {
        $harvest = $this->makeHarvest(['brix_degree' => 22.5]);

        $this->assertIsString($harvest->brix_degree);
        $this->assertEquals('22.500', $harvest->brix_degree);
    }

    public function test_potential_alcohol_cast_decimal2(): void
    {
        $harvest = $this->makeHarvest(['potential_alcohol' => 13.5]);

        $this->assertIsString($harvest->potential_alcohol);
        $this->assertEquals('13.50', $harvest->potential_alcohol);
    }

    public function test_sanitary_state_cast_decimal2(): void
    {
        $harvest = $this->makeHarvest([
            'sanitary_state_grapes' => 85.5,
            'sanitary_state_botrytis' => 5.25,
        ]);

        $this->assertEquals('85.50', $harvest->sanitary_state_grapes);
        $this->assertEquals('5.25', $harvest->sanitary_state_botrytis);
    }

    // ── Boolean cast ──────────────────────────────────────────────────────────

    public function test_disqualified_cast_boolean(): void
    {
        $harvest = $this->makeHarvest(['disqualified' => false]);

        $this->assertIsBool($harvest->disqualified);
        $this->assertFalse($harvest->disqualified);
    }

    // ── wasEdited ─────────────────────────────────────────────────────────────

    public function test_was_edited_false_when_no_edited_at(): void
    {
        $harvest = $this->makeHarvest(['edited_at' => null]);

        $this->assertFalse($harvest->wasEdited());
    }

    public function test_was_edited_true_when_edited_at_set(): void
    {
        $harvest = $this->makeHarvest(['edited_at' => now()]);

        $this->assertTrue($harvest->wasEdited());
    }

    // ── isCancelled ───────────────────────────────────────────────────────────

    public function test_is_cancelled_false_for_active(): void
    {
        $harvest = $this->makeHarvest(['status' => 'active']);

        $this->assertFalse($harvest->isCancelled());
    }

    public function test_is_cancelled_true_for_cancelled(): void
    {
        $harvest = $this->makeHarvest(['status' => 'cancelled']);

        $this->assertTrue($harvest->isCancelled());
    }

    // ── isViticulturistRecord / isWineryReception ─────────────────────────────

    public function test_is_viticulturist_record_when_activity_id_set(): void
    {
        $harvest = $this->makeHarvest();

        $this->assertTrue($harvest->isViticulturistRecord());
        $this->assertFalse($harvest->isWineryReception());
    }

    // ── hasContainer ──────────────────────────────────────────────────────────

    public function test_has_container_false_when_no_container(): void
    {
        $harvest = $this->makeHarvest(['container_id' => null]);

        $this->assertFalse($harvest->hasContainer());
    }

    // ── scopeActive ───────────────────────────────────────────────────────────

    public function test_scope_active_filters_cancelled(): void
    {
        $this->makeHarvest(['status' => 'active']);
        $this->makeHarvest(['status' => 'cancelled']);

        $results = Harvest::active()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('active', $results->first()->status);
    }

    // ── PAC trazabilidad fields ───────────────────────────────────────────────

    public function test_transport_document_number_stored_correctly(): void
    {
        $harvest = $this->makeHarvest(['transport_document_number' => 'GUIA-2026-001']);

        $this->assertEquals('GUIA-2026-001', $harvest->transport_document_number);
    }

    public function test_destination_rega_code_stored_correctly(): void
    {
        $harvest = $this->makeHarvest(['destination_rega_code' => 'ES000000000001']);

        $this->assertEquals('ES000000000001', $harvest->destination_rega_code);
    }

    public function test_trazabilidad_fields_nullable(): void
    {
        $harvest = $this->makeHarvest([
            'transport_document_number' => null,
            'destination_rega_code' => null,
            'vehicle_plate' => null,
        ]);

        $this->assertNull($harvest->transport_document_number);
        $this->assertNull($harvest->destination_rega_code);
        $this->assertNull($harvest->vehicle_plate);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makeHarvest(array $overrides = []): Harvest
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'harvest',
            'activity_date' => now()->format('Y-m-d'),
        ]);

        return Harvest::withoutEvents(fn () => Harvest::create(array_merge([
            'activity_id' => $activity->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'total_weight' => 1000.00,
            'status' => 'active',
        ], $overrides)));
    }
}
