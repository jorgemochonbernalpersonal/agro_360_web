<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryProducts;

use App\Livewire\Viticulturist\PhytosanitaryProducts\Index;
use App\Models\PhytosanitaryProduct;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_products(): void
    {
        $v = $this->makeViticulturist();
        $this->makeProduct($v, ['name' => 'Fungicida Visible', 'active' => true]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['active'] === 1 && $s['inactive'] === 0);
    }

    public function test_does_not_show_other_users_products(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $this->makeProduct($other);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['active'] === 0);
    }

    public function test_toggle_active_deactivates_product(): void
    {
        $v = $this->makeViticulturist();
        $product = $this->makeProduct($v, ['active' => true]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('toggleActive', $product->id);

        $this->assertDatabaseHas('phytosanitary_products', [
            'id' => $product->id,
            'active' => false,
        ]);
    }

    public function test_toggle_active_reactivates_product(): void
    {
        $v = $this->makeViticulturist();
        $product = $this->makeProduct($v, ['active' => false]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('toggleActive', $product->id);

        $this->assertDatabaseHas('phytosanitary_products', [
            'id' => $product->id,
            'active' => true,
        ]);
    }

    public function test_cannot_toggle_other_users_product(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $product = $this->makeProduct($other);
        $this->actingAs($v);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('toggleActive', $product->id);
    }

    public function test_inactive_tab_shows_inactive_products(): void
    {
        $v = $this->makeViticulturist();
        $this->makeProduct($v, ['active' => true]);
        $this->makeProduct($v, ['active' => false]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'inactive')
            ->assertViewHas('stats', fn ($s) => $s['inactive'] === 1);
    }

    private function makeProduct($viticulturist, array $attrs = []): PhytosanitaryProduct
    {
        return PhytosanitaryProduct::create(array_merge([
            'user_id' => $viticulturist->id,
            'name' => 'Producto '.uniqid(),
            'registration_number' => 'ES-'.str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
            'registration_status' => 'active',
            'withdrawal_period_days' => 0,
            'active' => true,
        ], $attrs));
    }
}
