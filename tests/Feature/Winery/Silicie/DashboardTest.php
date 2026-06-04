<?php

namespace Tests\Feature\Winery\Silicie;

use App\Livewire\Winery\Silicie\Dashboard;
use App\Models\Wine;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class DashboardTest extends WineryTestCase
{
    public function test_silicie_dashboard_renders(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Dashboard::class)
            ->assertOk();
    }

    public function test_dashboard_defaults_to_current_year(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Dashboard::class)
            ->assertSet('filterVintage', (string) now()->year);
    }

    public function test_dashboard_can_filter_by_vintage(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Dashboard::class)
            ->set('filterVintage', '2022')
            ->assertSet('filterVintage', '2022')
            ->assertOk();
    }

    public function test_dashboard_can_switch_tabs(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->assertSet('currentTab', 'entradas')
            ->call('switchTab', 'elaboracion')
            ->assertSet('currentTab', 'elaboracion')
            ->call('switchTab', 'existencias')
            ->assertSet('currentTab', 'existencias')
            ->call('switchTab', 'salidas')
            ->assertSet('currentTab', 'salidas');
    }

    public function test_take_snapshot_creates_stock_snapshot(): void
    {
        $winery = $this->makeWinery();

        // Create a container with wine stock
        $wine = Wine::create([
            'user_id' => $winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'vintage' => now()->year,
            'status' => 'in_progress',
        ]);
        $container = DB::table('containers')->insertGetId([
            'user_id' => $winery->id,
            'name' => 'Dep 1',
            'capacity' => 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('container_current_states')->insert([
            'container_id' => $container,
            'wine_id' => $wine->id,
            'current_quantity' => 3000,
            'updated_at' => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->call('takeSnapshot');

        $this->assertDatabaseHas('wine_stock_snapshots', [
            'user_id' => $winery->id,
            'wine_id' => $wine->id,
            'snapshot_date' => now()->toDateString(),
        ]);
    }

    public function test_export_csv_returns_download(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->call('exportCsv')
            ->assertFileDownloaded('SILICIE_'.$winery->id.'_'.now()->year.'.csv');
    }

    public function test_entradas_tab_shows_external_grapes_section(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->assertSee('A02/A04/A06');
    }
}
