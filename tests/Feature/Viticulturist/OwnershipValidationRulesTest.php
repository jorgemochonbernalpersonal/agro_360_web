<?php

namespace Tests\Feature\Viticulturist;

use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\GrapeVariety;
use App\Models\Machinery;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Verifica los ownership rules del WithViticulturistValidation trait:
 * plotOwnershipRule, plotPlantingOwnershipRule, campaignOwnershipRule,
 * crewOwnershipRule, machineryOwnershipRule.
 *
 * Testea las closures directamente, sin renderizar views complejas.
 */
class OwnershipValidationRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $other;

    private Campaign $ownCampaign;

    private Campaign $otherCampaign;

    private Plot $ownPlot;

    private Plot $otherPlot;

    private PlotPlanting $ownPlanting;

    private PlotPlanting $otherPlanting;

    private Crew $ownCrew;

    private Crew $otherCrew;

    private Machinery $ownMachinery;

    private Machinery $otherMachinery;

    /** Anonymous object that uses the trait — avoids spinning up full Livewire */
    private object $rules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        $this->other = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        $this->ownCampaign = Campaign::factory()->active()->create(['viticulturist_id' => $this->owner->id, 'year' => now()->year]);
        $this->otherCampaign = Campaign::factory()->active()->create(['viticulturist_id' => $this->other->id, 'year' => now()->year]);

        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TEST'], ['name' => 'Comunidad Test']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Provincia Test', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Municipio Test', 'province_id' => $prov->id]);
        $geo = ['autonomous_community_id' => $ac->id, 'province_id' => $prov->id, 'municipality_id' => $muni->id];

        $this->ownPlot = Plot::factory()->create(array_merge($geo, ['viticulturist_id' => $this->owner->id]));
        $this->otherPlot = Plot::factory()->create(array_merge($geo, ['viticulturist_id' => $this->other->id]));

        $grapeVariety = GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red']);
        $plantingData = ['grape_variety_id' => $grapeVariety->id, 'area_planted' => 1.0, 'planting_year' => now()->year - 3, 'status' => 'active'];

        $this->ownPlanting = PlotPlanting::create(array_merge($plantingData, ['plot_id' => $this->ownPlot->id]));
        $this->otherPlanting = PlotPlanting::create(array_merge($plantingData, ['plot_id' => $this->otherPlot->id]));

        $this->ownCrew = Crew::create(['name' => 'Cuadrilla propia', 'viticulturist_id' => $this->owner->id]);
        $this->otherCrew = Crew::create(['name' => 'Cuadrilla ajena',  'viticulturist_id' => $this->other->id]);

        $this->ownMachinery = Machinery::create(['name' => 'Tractor propio', 'viticulturist_id' => $this->owner->id, 'type' => 'tractor']);
        $this->otherMachinery = Machinery::create(['name' => 'Tractor ajeno',  'viticulturist_id' => $this->other->id, 'type' => 'tractor']);

        // Build a minimal object that exposes the trait methods while authenticated as $this->owner
        $this->rules = new class
        {
            use WithViticulturistValidation;
        };
    }

    // ── campaign_id ────────────────────────────────────────────────────────────

    public function test_campaign_ownership_rule_accepts_own_campaign(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['campaign_id' => $this->ownCampaign->id], ['campaign_id' => $this->rules->campaignOwnershipRule()]);
        $this->assertFalse($v->fails(), 'Own campaign should pass: '.$v->errors()->first('campaign_id'));
    }

    public function test_campaign_ownership_rule_rejects_other_viticulturist_campaign(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['campaign_id' => $this->otherCampaign->id], ['campaign_id' => $this->rules->campaignOwnershipRule()]);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->errors()->get('campaign_id'));
    }

    public function test_campaign_ownership_rule_required_rejects_empty(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['campaign_id' => null], ['campaign_id' => $this->rules->campaignOwnershipRule(required: true)]);
        $this->assertTrue($v->fails());
    }

    // ── plot_id ────────────────────────────────────────────────────────────────

    public function test_plot_ownership_rule_accepts_own_plot(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['plot_id' => $this->ownPlot->id], ['plot_id' => $this->rules->plotOwnershipRule()]);
        $this->assertFalse($v->fails(), 'Own plot should pass: '.$v->errors()->first('plot_id'));
    }

    public function test_plot_ownership_rule_rejects_other_viticulturist_plot(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['plot_id' => $this->otherPlot->id], ['plot_id' => $this->rules->plotOwnershipRule()]);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->errors()->get('plot_id'));
    }

    // ── plot_planting_id ───────────────────────────────────────────────────────

    public function test_plot_planting_ownership_rule_accepts_own_planting(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['plot_planting_id' => $this->ownPlanting->id], ['plot_planting_id' => $this->rules->plotPlantingOwnershipRule(required: true)]);
        $this->assertFalse($v->fails(), 'Own planting should pass: '.$v->errors()->first('plot_planting_id'));
    }

    public function test_plot_planting_ownership_rule_rejects_other_viticulturist_planting(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['plot_planting_id' => $this->otherPlanting->id], ['plot_planting_id' => $this->rules->plotPlantingOwnershipRule(required: true)]);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->errors()->get('plot_planting_id'));
    }

    // ── crew_id ────────────────────────────────────────────────────────────────

    public function test_crew_ownership_rule_accepts_own_crew(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['crew_id' => $this->ownCrew->id], ['crew_id' => $this->rules->crewOwnershipRule()]);
        $this->assertFalse($v->fails(), 'Own crew should pass: '.$v->errors()->first('crew_id'));
    }

    public function test_crew_ownership_rule_rejects_other_viticulturist_crew(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['crew_id' => $this->otherCrew->id], ['crew_id' => $this->rules->crewOwnershipRule()]);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->errors()->get('crew_id'));
    }

    public function test_crew_ownership_rule_accepts_null(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['crew_id' => null], ['crew_id' => $this->rules->crewOwnershipRule()]);
        $this->assertFalse($v->fails());
    }

    // ── machinery_id ───────────────────────────────────────────────────────────

    public function test_machinery_ownership_rule_accepts_own_machinery(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['machinery_id' => $this->ownMachinery->id], ['machinery_id' => $this->rules->machineryOwnershipRule()]);
        $this->assertFalse($v->fails(), 'Own machinery should pass: '.$v->errors()->first('machinery_id'));
    }

    public function test_machinery_ownership_rule_rejects_other_viticulturist_machinery(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['machinery_id' => $this->otherMachinery->id], ['machinery_id' => $this->rules->machineryOwnershipRule()]);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->errors()->get('machinery_id'));
    }

    public function test_machinery_ownership_rule_accepts_null(): void
    {
        $this->actingAs($this->owner);
        $v = $this->validate(['machinery_id' => null], ['machinery_id' => $this->rules->machineryOwnershipRule()]);
        $this->assertFalse($v->fails());
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    private function validate(array $data, array $rules): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $rules);
    }
}
