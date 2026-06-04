<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\HarvestByproduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestByproductTest extends TestCase
{
    use RefreshDatabase;

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function test_byproduct_type_label_returns_correct_label(): void
    {
        $model = new HarvestByproduct(['byproduct_type' => 'orujo']);
        $this->assertEquals('Orujo / Hollejo', $model->byproduct_type_label);
    }

    public function test_byproduct_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'orujo' => 'Orujo / Hollejo',
            'raspon' => 'Raspón / Escobajo',
            'lia' => 'Lías',
            'otro' => 'Otro',
        ];

        foreach ($expected as $key => $label) {
            $model = new HarvestByproduct(['byproduct_type' => $key]);
            $this->assertEquals($label, $model->byproduct_type_label, "Failed for type: {$key}");
        }
    }

    public function test_byproduct_type_label_returns_raw_value_for_unknown_type(): void
    {
        $model = new HarvestByproduct(['byproduct_type' => 'desconocido']);
        $this->assertEquals('desconocido', $model->byproduct_type_label);
    }

    public function test_destination_type_label_returns_correct_label(): void
    {
        $model = new HarvestByproduct(['destination_type' => 'destileria']);
        $this->assertEquals('Destilería / Alcoholera', $model->destination_type_label);
    }

    public function test_destination_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'cooperativa' => 'Cooperativa vinícola',
            'bodega' => 'Bodega',
            'destileria' => 'Destilería / Alcoholera',
            'compostaje' => 'Planta de compostaje',
            'vertedero_autorizado' => 'Vertedero autorizado',
            'otro' => 'Otro destino',
        ];

        foreach ($expected as $key => $label) {
            $model = new HarvestByproduct(['destination_type' => $key]);
            $this->assertEquals($label, $model->destination_type_label, "Failed for destination: {$key}");
        }
    }

    public function test_destination_type_label_returns_raw_value_for_unknown_type(): void
    {
        $model = new HarvestByproduct(['destination_type' => 'tipo_raro']);
        $this->assertEquals('tipo_raro', $model->destination_type_label);
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public function test_byproduct_types_constant_contains_all_expected_keys(): void
    {
        $this->assertArrayHasKey('orujo', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('raspon', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('lia', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('otro', HarvestByproduct::BYPRODUCT_TYPES);
    }

    public function test_destination_types_constant_contains_all_expected_keys(): void
    {
        $this->assertArrayHasKey('cooperativa', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('bodega', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('destileria', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('compostaje', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('vertedero_autorizado', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('otro', HarvestByproduct::DESTINATION_TYPES);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function test_harvest_byproduct_belongs_to_viticulturist(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $byproduct = HarvestByproduct::create([
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'date' => now(),
            'byproduct_type' => 'orujo',
            'quantity_kg' => 500,
            'destination_type' => 'cooperativa',
            'destination_name' => 'Coop Test',
            'active' => true,
        ]);

        $this->assertEquals($user->id, $byproduct->viticulturist->id);
    }

    public function test_harvest_byproduct_belongs_to_campaign(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $byproduct = HarvestByproduct::create([
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'date' => now(),
            'byproduct_type' => 'lia',
            'quantity_kg' => 200,
            'destination_type' => 'destileria',
            'destination_name' => 'Destilería Test',
            'active' => true,
        ]);

        $this->assertEquals($campaign->id, $byproduct->campaign->id);
    }
}
