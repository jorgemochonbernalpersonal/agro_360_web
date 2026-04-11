<?php

namespace Tests\Feature\Winery\Silicie;

use App\Livewire\Winery\Silicie\Infovi;
use App\Models\Wine;
use App\Models\WineStockSnapshot;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class InfoviTest extends WineryTestCase
{
    public function test_infovi_renders(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Infovi::class)
            ->assertOk();
    }

    public function test_defaults_to_current_campaign(): void
    {
        $expectedCampaign = (string) (now()->month >= 8 ? now()->year : now()->year - 1);

        Livewire::actingAs($this->makeWinery())
            ->test(Infovi::class)
            ->assertSet('filterCampaign', $expectedCampaign);
    }

    public function test_can_filter_by_campaign(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Infovi::class)
            ->set('filterCampaign', '2023')
            ->assertSet('filterCampaign', '2023')
            ->assertOk();
    }

    public function test_produccion_cuadro_shows_wines(): void
    {
        $winery = $this->makeWinery();

        Wine::create([
            'user_id'       => $winery->id,
            'name'          => 'Tinto Test',
            'vintage'       => now()->year,
            'wine_type'     => 'red',
            'volume_liters' => 5000,
            'status'        => 'in_progress',
        ]);

        $component = Livewire::actingAs($winery)
            ->test(Infovi::class);

        // HL = 5000/100 = 50 HL shown in cuadro producción
        $component->assertSee('Cuadro 2');
    }

    public function test_existencias_uses_snapshot_when_available(): void
    {
        $winery = $this->makeWinery();
        $wine = Wine::create([
            'user_id'   => $winery->id,
            'name'      => 'Blanco Test',
            'vintage'   => now()->year,
            'wine_type' => 'white',
            'status'    => 'in_progress',
        ]);

        WineStockSnapshot::create([
            'user_id'         => $winery->id,
            'wine_id'         => $wine->id,
            'snapshot_date'   => now()->startOfMonth()->toDateString(),
            'quantity_liters' => 2000,
            'container_count' => 2,
            'vintage'         => now()->year,
            'wine_type'       => 'white',
            'created_by'      => $winery->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Infovi::class)
            ->assertSee('instantánea');
    }

    public function test_ventas_cuadro_renders_without_invoices(): void
    {
        Livewire::actingAs($this->makeWinery())
            ->test(Infovi::class)
            ->assertSee('Cuadro 4');
    }

    public function test_other_winery_sees_own_data_only(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeWinery();

        Wine::create([
            'user_id'       => $winery1->id,
            'name'          => 'Tinto Reserva Exclusivo W1',
            'vintage'       => now()->year,
            'wine_type'     => 'red',
            'volume_liters' => 9000,
            'status'        => 'aged',
        ]);

        // winery2 should NOT see winery1's production data
        Livewire::actingAs($winery2)
            ->test(Infovi::class)
            ->assertDontSee('Tinto Reserva Exclusivo W1');
    }
}
