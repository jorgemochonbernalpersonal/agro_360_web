<?php

namespace Tests\Unit\Models;

use App\Models\HarvestStock;
use App\Models\Harvest;
use App\Models\Container;
use App\Models\User;
use App\Models\InvoiceItem;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Campaign;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestStockTest extends TestCase
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

    public function test_harvest_stock_belongs_to_harvest(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $this->assertEquals($harvest->id, $stock->harvest->id);
    }

    public function test_harvest_stock_belongs_to_container(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $container = Container::factory()->create([
            'user_id' => $user->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'container_id' => $container->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $this->assertEquals($container->id, $stock->container->id);
    }

    public function test_harvest_stock_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $this->assertEquals($user->id, $stock->user->id);
    }

    public function test_harvest_stock_belongs_to_invoice_item(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $invoice = \App\Models\Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'harvest_id' => $harvest->id,
            'name' => 'Test Item',
            'quantity' => 50.00,
            'unit_price' => 10.00,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'invoice_item_id' => $invoiceItem->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $this->assertEquals($invoiceItem->id, $stock->invoiceItem->id);
    }

    public function test_scope_of_type_filters_by_movement_type(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $initialStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $results = HarvestStock::ofType('initial')->get();

        $this->assertTrue($results->contains('id', $initialStock->id));
        $this->assertFalse($results->contains('id', $saleStock->id));
    }

    public function test_scope_initial_filters_initial_movements(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $initialStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $results = HarvestStock::initial()->get();

        $this->assertTrue($results->contains('id', $initialStock->id));
        $this->assertFalse($results->contains('id', $saleStock->id));
    }

    public function test_scope_sales_filters_sale_movements(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $initialStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $results = HarvestStock::sales()->get();

        $this->assertTrue($results->contains('id', $saleStock->id));
        $this->assertFalse($results->contains('id', $initialStock->id));
    }

    public function test_scope_reservations_filters_reserve_movements(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $reserveStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0.00,
            'quantity_after' => 100.00,
            'available_qty' => 50.00,
            'reserved_qty' => 50.00,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $results = HarvestStock::reservations()->get();

        $this->assertTrue($results->contains('id', $reserveStock->id));
        $this->assertFalse($results->contains('id', $saleStock->id));
    }

    public function test_scope_adjustments_filters_adjustment_movements(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $adjustmentStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'adjustment',
            'quantity_change' => 10.00,
            'quantity_after' => 110.00,
            'available_qty' => 110.00,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $results = HarvestStock::adjustments()->get();

        $this->assertTrue($results->contains('id', $adjustmentStock->id));
        $this->assertFalse($results->contains('id', $saleStock->id));
    }

    public function test_is_inbound_returns_true_for_initial_movement(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $this->assertTrue($stock->isInbound());
    }

    public function test_is_inbound_returns_true_for_positive_adjustment(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'adjustment',
            'quantity_change' => 10.00,
            'quantity_after' => 110.00,
            'available_qty' => 110.00,
        ]);

        $this->assertTrue($stock->isInbound());
    }

    public function test_is_inbound_returns_false_for_sale_movement(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $this->assertFalse($stock->isInbound());
    }

    public function test_is_outbound_returns_true_for_sale_movement(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $this->assertTrue($stock->isOutbound());
    }

    public function test_is_outbound_returns_true_for_negative_adjustment(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'adjustment',
            'quantity_change' => -10.00,
            'quantity_after' => 90.00,
            'available_qty' => 90.00,
        ]);

        $this->assertTrue($stock->isOutbound());
    }

    public function test_is_state_change_returns_true_for_reserve_movement(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0.00,
            'quantity_after' => 100.00,
            'available_qty' => 50.00,
            'reserved_qty' => 50.00,
        ]);

        $this->assertTrue($stock->isStateChange());
    }

    public function test_get_movement_description_returns_correct_descriptions(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $initialStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.00,
            'quantity_after' => 100.00,
            'available_qty' => 100.00,
        ]);

        $saleStock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'sale',
            'quantity_change' => -50.00,
            'quantity_after' => 50.00,
            'sold_qty' => 50.00,
        ]);

        $positiveAdjustment = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'adjustment',
            'quantity_change' => 10.00,
            'quantity_after' => 110.00,
            'available_qty' => 110.00,
        ]);

        $negativeAdjustment = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'adjustment',
            'quantity_change' => -10.00,
            'quantity_after' => 90.00,
            'available_qty' => 90.00,
        ]);

        $this->assertEquals('Registro inicial de cosecha', $initialStock->getMovementDescription());
        $this->assertEquals('Venta confirmada', $saleStock->getMovementDescription());
        $this->assertEquals('Ajuste positivo (+)', $positiveAdjustment->getMovementDescription());
        $this->assertEquals('Ajuste negativo (-)', $negativeAdjustment->getMovementDescription());
    }

    public function test_quantity_fields_are_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = Harvest::factory()->create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
        ]);

        $stock = HarvestStock::create([
            'harvest_id' => $harvest->id,
            'user_id' => $user->id,
            'movement_type' => 'initial',
            'quantity_change' => 100.50,
            'quantity_after' => 100.50,
            'available_qty' => 100.50,
            'reserved_qty' => 0.00,
            'sold_qty' => 0.00,
            'gifted_qty' => 0.00,
            'lost_qty' => 0.00,
        ]);

        // Laravel's decimal cast returns a string, not a float
        $this->assertIsNumeric($stock->quantity_change);
        $this->assertIsNumeric($stock->quantity_after);
        $this->assertIsNumeric($stock->available_qty);
    }
}

