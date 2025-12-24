<?php

namespace Tests\Unit\Models;

use App\Models\Harvest;
use App\Models\AgriculturalActivity;
use App\Models\PlotPlanting;
use App\Models\Container;
use App\Models\HarvestStock;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Plot;
use App\Models\Campaign;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_harvest_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
        ]);

        $this->assertEquals($activity->id, $harvest->activity->id);
    }

    public function test_harvest_belongs_to_plot_planting(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.5,
            'status' => 'active',
        ]);

        $harvest = Harvest::factory()->create([
            'plot_planting_id' => $planting->id,
        ]);

        $this->assertEquals($planting->id, $harvest->plotPlanting->id);
    }

    public function test_harvest_belongs_to_container(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $container = Container::factory()->create(['user_id' => $viticulturist->id]);

        $harvest = Harvest::factory()->create([
            'container_id' => $container->id,
        ]);

        $this->assertEquals($container->id, $harvest->container->id);
    }

    public function test_harvest_belongs_to_editor(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $editor = User::factory()->create(['role' => 'viticulturist']);

        $harvest = Harvest::factory()->create([
            'edited_by' => $editor->id,
            'edited_at' => now(),
        ]);

        $this->assertEquals($editor->id, $harvest->editor->id);
    }

    public function test_harvest_calculates_yield_per_hectare_automatically(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 2.0, // 2 hectáreas
            'status' => 'active',
        ]);

        $harvest = Harvest::factory()->create([
            'plot_planting_id' => $planting->id,
            'total_weight' => 10000.0, // 10,000 kg
        ]);

        // Debe calcular: 10000 / 2 = 5000 kg/hectárea
        $this->assertEquals(5000.0, $harvest->yield_per_hectare);
    }

    public function test_harvest_calculates_total_value_automatically(): void
    {
        $harvest = Harvest::factory()->create([
            'total_weight' => 1000.0, // 1000 kg
            'price_per_kg' => 2.50, // 2.50 €/kg
        ]);

        // Debe calcular: 1000 * 2.50 = 2500 €
        $this->assertEquals(2500.0, $harvest->total_value);
    }

    public function test_harvest_calculates_both_yield_and_value(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0, // 1 hectárea
            'status' => 'active',
        ]);

        $harvest = Harvest::factory()->create([
            'plot_planting_id' => $planting->id,
            'total_weight' => 5000.0, // 5000 kg
            'price_per_kg' => 3.00, // 3.00 €/kg
        ]);

        $this->assertEquals(5000.0, $harvest->yield_per_hectare);
        $this->assertEquals(15000.0, $harvest->total_value);
    }

    public function test_scope_active_filters_active_harvests(): void
    {
        $activeHarvest = Harvest::factory()->create(['status' => 'active']);
        $cancelledHarvest = Harvest::factory()->create(['status' => 'cancelled']);

        $results = Harvest::active()->get();

        $this->assertTrue($results->contains('id', $activeHarvest->id));
        $this->assertFalse($results->contains('id', $cancelledHarvest->id));
    }

    public function test_scope_for_planting_filters_by_planting(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $planting1 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $planting2 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $harvest1 = Harvest::factory()->create(['plot_planting_id' => $planting1->id]);
        $harvest2 = Harvest::factory()->create(['plot_planting_id' => $planting2->id]);

        $results = Harvest::forPlanting($planting1->id)->get();

        $this->assertTrue($results->contains('id', $harvest1->id));
        $this->assertFalse($results->contains('id', $harvest2->id));
    }

    public function test_scope_for_campaign_filters_by_campaign(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity1 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $activity2 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign2->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest1 = Harvest::factory()->create(['activity_id' => $activity1->id]);
        $harvest2 = Harvest::factory()->create(['activity_id' => $activity2->id]);

        $results = Harvest::forCampaign($campaign1->id)->get();

        $this->assertTrue($results->contains('id', $harvest1->id));
        $this->assertFalse($results->contains('id', $harvest2->id));
    }

    public function test_was_edited_returns_true_when_edited(): void
    {
        $harvest = Harvest::factory()->create([
            'edited_at' => now(),
            'edited_by' => User::factory()->create()->id,
        ]);

        $this->assertTrue($harvest->wasEdited());
    }

    public function test_was_edited_returns_false_when_not_edited(): void
    {
        $harvest = Harvest::factory()->create([
            'edited_at' => null,
            'edited_by' => null,
        ]);

        $this->assertFalse($harvest->wasEdited());
    }

    public function test_is_cancelled_returns_true_when_status_is_cancelled(): void
    {
        $harvest = Harvest::factory()->create(['status' => 'cancelled']);

        $this->assertTrue($harvest->isCancelled());
    }

    public function test_is_cancelled_returns_false_when_status_is_not_cancelled(): void
    {
        $harvest = Harvest::factory()->create(['status' => 'active']);

        $this->assertFalse($harvest->isCancelled());
    }

    public function test_get_container_weight_returns_used_capacity_when_has_container(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $container = Container::factory()->create([
            'user_id' => $viticulturist->id,
            'capacity' => 1000.0,
            'used_capacity' => 25.5,
        ]);

        $harvest = Harvest::factory()->create([
            'container_id' => $container->id,
            'total_weight' => 25.5,
        ]);

        // El HarvestObserver actualiza used_capacity automáticamente
        $container->refresh();
        $this->assertEquals(25.5, $harvest->getContainerWeight());
    }

    public function test_get_container_weight_returns_null_when_no_container(): void
    {
        $harvest = Harvest::factory()->create([
            'container_id' => null,
        ]);

        $this->assertNull($harvest->getContainerWeight());
    }

    public function test_has_container_returns_true_when_has_container(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $container = Container::factory()->create(['user_id' => $viticulturist->id]);

        $harvest = Harvest::factory()->create([
            'container_id' => $container->id,
        ]);

        $this->assertTrue($harvest->hasContainer());
    }

    public function test_has_container_returns_false_when_no_container(): void
    {
        $harvest = Harvest::factory()->create([
            'container_id' => null,
        ]);

        $this->assertFalse($harvest->hasContainer());
    }

    public function test_harvest_has_many_invoice_items(): void
    {
        $harvest = Harvest::factory()->create();

        $item1 = InvoiceItem::factory()->create(['harvest_id' => $harvest->id]);
        $item2 = InvoiceItem::factory()->create(['harvest_id' => $harvest->id]);

        $this->assertCount(2, $harvest->invoiceItems);
    }

    public function test_is_invoiced_returns_true_when_has_invoice_items(): void
    {
        $harvest = Harvest::factory()->create();
        InvoiceItem::factory()->create(['harvest_id' => $harvest->id]);

        $this->assertTrue($harvest->isInvoiced());
    }

    public function test_is_invoiced_returns_false_when_no_invoice_items(): void
    {
        $harvest = Harvest::factory()->create();

        $this->assertFalse($harvest->isInvoiced());
    }

    public function test_get_current_stock_returns_default_when_no_movements(): void
    {
        $harvest = Harvest::factory()->create();

        $stock = $harvest->getCurrentStock();

        $this->assertEquals(0, $stock['total']);
        $this->assertEquals(0, $stock['available']);
        $this->assertEquals(0, $stock['reserved']);
        $this->assertEquals(0, $stock['sold']);
    }

    public function test_get_current_stock_returns_latest_movement_values(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 800,
            'reserved_qty' => 100,
            'sold_qty' => 100,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $stock = $harvest->getCurrentStock();

        $this->assertEquals(1000, $stock['total']);
        $this->assertEquals(800, $stock['available']);
        $this->assertEquals(100, $stock['reserved']);
        $this->assertEquals(100, $stock['sold']);
    }

    public function test_has_available_stock_returns_true_when_has_stock(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 500,
            'reserved_qty' => 0,
            'sold_qty' => 0,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertTrue($harvest->hasAvailableStock());
    }

    public function test_has_available_stock_checks_quantity_when_specified(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 500,
            'reserved_qty' => 0,
            'sold_qty' => 0,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertTrue($harvest->hasAvailableStock(300));
        $this->assertTrue($harvest->hasAvailableStock(500));
        $this->assertFalse($harvest->hasAvailableStock(501));
    }

    public function test_get_available_quantity_returns_available_stock(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 750,
            'reserved_qty' => 0,
            'sold_qty' => 0,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertEquals(750, $harvest->getAvailableQuantity());
    }

    public function test_get_reserved_quantity_returns_reserved_stock(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 700,
            'reserved_qty' => 200,
            'sold_qty' => 100,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertEquals(200, $harvest->getReservedQuantity());
    }

    public function test_get_sold_quantity_returns_sold_stock(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 600,
            'reserved_qty' => 200,
            'sold_qty' => 200,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertEquals(200, $harvest->getSoldQuantity());
    }

    public function test_is_fully_sold_returns_true_when_no_available_or_reserved(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 0,
            'reserved_qty' => 0,
            'sold_qty' => 1000,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertTrue($harvest->isFullySold());
    }

    public function test_is_fully_sold_returns_false_when_has_available_stock(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 100,
            'reserved_qty' => 0,
            'sold_qty' => 900,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertFalse($harvest->isFullySold());
    }

    public function test_get_sold_percentage_returns_correct_percentage(): void
    {
        $harvest = Harvest::factory()->create();

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'quantity_before' => 0,
            'quantity_after' => 1000,
            'available_qty' => 300,
            'reserved_qty' => 0,
            'sold_qty' => 700,
            'gifted_qty' => 0,
            'lost_qty' => 0,
            'movement_type' => 'initial',
        ]);

        $this->assertEquals(70.0, $harvest->getSoldPercentage());
    }

    public function test_get_sold_percentage_returns_zero_when_total_is_zero(): void
    {
        $harvest = Harvest::factory()->create();

        $this->assertEquals(0, $harvest->getSoldPercentage());
    }
}
