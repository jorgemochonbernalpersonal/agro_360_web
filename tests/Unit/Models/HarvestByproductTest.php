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
        $model = new HarvestByproduct(['byproduct_type' => 'pomace']);
        $this->assertEquals('Orujo / Hollejo', $model->byproduct_type_label);
    }

    public function test_byproduct_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'pomace' => 'Orujo / Hollejo',
            'stem' => 'Raspón / Escobajo',
            'lees' => 'Lías',
            'other' => 'Otro',
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
        $model = new HarvestByproduct(['destination_type' => 'distillery']);
        $this->assertEquals('Destilería / Alcoholera', $model->destination_type_label);
    }

    public function test_destination_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'cooperative' => 'Cooperativa vinícola',
            'winery' => 'Bodega',
            'distillery' => 'Destilería / Alcoholera',
            'composting' => 'Planta de compostaje',
            'authorized_landfill' => 'Vertedero autorizado',
            'other' => 'Otro destino',
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
        $this->assertArrayHasKey('pomace', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('stem', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('lees', HarvestByproduct::BYPRODUCT_TYPES);
        $this->assertArrayHasKey('other', HarvestByproduct::BYPRODUCT_TYPES);
    }

    public function test_destination_types_constant_contains_all_expected_keys(): void
    {
        $this->assertArrayHasKey('cooperative', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('winery', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('distillery', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('composting', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('authorized_landfill', HarvestByproduct::DESTINATION_TYPES);
        $this->assertArrayHasKey('other', HarvestByproduct::DESTINATION_TYPES);
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
            'byproduct_type' => 'pomace',
            'quantity_kg' => 500,
            'destination_type' => 'cooperative',
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
            'byproduct_type' => 'lees',
            'quantity_kg' => 200,
            'destination_type' => 'distillery',
            'destination_name' => 'Destilería Test',
            'active' => true,
        ]);

        $this->assertEquals($campaign->id, $byproduct->campaign->id);
    }
}
