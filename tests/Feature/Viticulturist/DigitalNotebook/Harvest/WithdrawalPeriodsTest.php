<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Harvest;

use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

/**
 * Tests para plazos de seguridad (withdrawal periods) en CreateHarvest:
 * - Tratamiento con plazo activo → warning
 * - Tratamiento con plazo vencido → sin warning
 * - Múltiples tratamientos, mezcla activo/vencido
 * - Validación de acknowledged + reason cuando hay plazo activo
 */
class WithdrawalPeriodsTest extends ViticulturistTestCase
{
    private User $viticulturist;
    private Campaign $campaign;
    private Plot $plot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);

        $this->campaign = Campaign::factory()->active()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'year'             => now()->year,
        ]);

        $ac   = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        $this->plot = Plot::factory()->create([
            'viticulturist_id'        => $this->viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id'             => $prov->id,
            'municipality_id'         => $muni->id,
            'active'                  => true,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePlanting(): PlotPlanting
    {
        return PlotPlanting::create([
            'plot_id'      => $this->plot->id,
            'area_planted' => 2.0,
            'status'       => 'active',
        ]);
    }

    /**
     * Crea un tratamiento fitosanitario en la parcela con producto y plazo de seguridad.
     */
    private function createTreatment(int $withdrawalDays, Carbon $applicationDate): void
    {
        $product = PhytosanitaryProduct::create([
            'user_id'               => $this->viticulturist->id,
            'name'                  => "Producto test {$withdrawalDays}d",
            'registration_number'   => 'ES-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
            'withdrawal_period_days' => $withdrawalDays,
            'active'                => true,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id'          => $this->plot->id,
            'viticulturist_id' => $this->viticulturist->id,
            'campaign_id'      => $this->campaign->id,
            'activity_type'    => 'phytosanitary',
            'activity_date'    => $applicationDate->format('Y-m-d'),
        ]);

        PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id'  => $product->id,
        ]);
    }

    // ── Tratamiento activo → warning ─────────────────────────────────────────

    public function test_active_withdrawal_shows_warning(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        // Tratamiento aplicado hace 5 días con plazo de 15 días → 10 días restantes
        $this->createTreatment(15, now()->subDays(5));

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        $component->assertSet('hasActiveWithdrawal', true);

        $treatments = $component->get('activeWithdrawalTreatments');
        $this->assertCount(1, $treatments);
        $this->assertStringContainsString('Producto test 15d', $treatments[0]['product_name']);
        $this->assertEquals(15, $treatments[0]['withdrawal_days']);
        $this->assertGreaterThan(0, $treatments[0]['days_remaining']);
    }

    // ── Tratamiento vencido → sin warning ────────────────────────────────────

    public function test_expired_withdrawal_no_warning(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        // Tratamiento aplicado hace 30 días con plazo de 15 → ya seguro
        $this->createTreatment(15, now()->subDays(30));

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        $component->assertSet('hasActiveWithdrawal', false);
        $this->assertEmpty($component->get('activeWithdrawalTreatments'));
    }

    // ── Múltiples tratamientos (mezcla) ──────────────────────────────────────

    public function test_multiple_treatments_only_active_ones_shown(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        // Tratamiento 1: aplicado hace 5 días, plazo 21 días → ACTIVO (16 días restantes)
        $this->createTreatment(21, now()->subDays(5));

        // Tratamiento 2: aplicado hace 60 días, plazo 15 días → VENCIDO
        $this->createTreatment(15, now()->subDays(60));

        // Tratamiento 3: aplicado ayer, plazo 7 días → ACTIVO (6 días restantes)
        $this->createTreatment(7, now()->subDays(1));

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        $component->assertSet('hasActiveWithdrawal', true);

        $treatments = $component->get('activeWithdrawalTreatments');
        $this->assertCount(2, $treatments); // Solo los 2 activos

        $productNames = array_column($treatments, 'product_name');
        $this->assertContains('Producto test 21d', $productNames);
        $this->assertContains('Producto test 7d', $productNames);
    }

    // ── Warning se resetea al cambiar de parcela ─────────────────────────────

    public function test_warning_resets_when_plot_changes(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        // Tratamiento activo en parcela original
        $this->createTreatment(15, now()->subDays(3));

        // Crear otra parcela sin tratamientos
        $ac   = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        $otherPlot = Plot::factory()->create([
            'viticulturist_id'        => $this->viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id'             => $prov->id,
            'municipality_id'         => $muni->id,
            'active'                  => true,
        ]);
        PlotPlanting::create(['plot_id' => $otherPlot->id, 'area_planted' => 1.0, 'status' => 'active']);
        PlotPlanting::create(['plot_id' => $otherPlot->id, 'area_planted' => 1.0, 'status' => 'active']);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        $component->assertSet('hasActiveWithdrawal', true);

        // Cambiar a otra parcela
        $component->set('plot_id', $otherPlot->id);
        $component->assertSet('hasActiveWithdrawal', false);
        $this->assertEmpty($component->get('activeWithdrawalTreatments'));
    }

    // ── Warning se resetea al deseleccionar parcela ──────────────────────────

    public function test_warning_resets_when_plot_cleared(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        $this->createTreatment(15, now()->subDays(3));

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        $component->assertSet('hasActiveWithdrawal', true);

        $component->set('plot_id', '');
        $component->assertSet('hasActiveWithdrawal', false);
    }

    // ── Validación: acknowledged + reason requeridos ─────────────────────────

    public function test_save_fails_without_acknowledged_when_withdrawal_active(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $this->createTreatment(15, now()->subDays(3));

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $this->viticulturist->id)
            // No acknowledged ni reason
            ->call('save')
            ->assertHasErrors(['withdrawalAcknowledged']);
    }

    public function test_save_fails_without_reason_when_withdrawal_active(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $this->createTreatment(15, now()->subDays(3));

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $this->viticulturist->id)
            ->set('withdrawalAcknowledged', true)
            ->set('withdrawalReason', 'corto') // min:20
            ->call('save')
            ->assertHasErrors(['withdrawalReason']);
    }

    public function test_save_succeeds_with_acknowledged_and_valid_reason(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $this->createTreatment(15, now()->subDays(3));

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $this->viticulturist->id)
            ->set('withdrawalAcknowledged', true)
            ->set('withdrawalReason', 'Cosecha de emergencia por condiciones meteorológicas adversas previstas')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'total_weight' => 1000,
            'status'       => 'active',
        ]);
    }

    // ── Sin plazo activo: no requiere acknowledged ───────────────────────────

    public function test_save_without_withdrawal_does_not_require_acknowledged(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        // Sin tratamientos → sin plazo activo
        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 2000)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $this->viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    // ── Tratamiento aplicado hoy con 0 días de carencia ──────────────────────

    public function test_zero_withdrawal_period_no_warning(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        // Producto sin plazo de seguridad (0 días)
        $this->createTreatment(0, now());

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id);

        // 0 días → safe_date = hoy → isFuture() = false → sin warning
        $component->assertSet('hasActiveWithdrawal', false);
    }
}
