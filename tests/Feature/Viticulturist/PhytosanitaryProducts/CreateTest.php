<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryProducts;

use App\Livewire\Viticulturist\PhytosanitaryProducts\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_phytosanitary_product(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('name', 'Fungicida Test')
            ->set('registration_number', 'ES-12345678')
            ->set('registration_status', 'active')
            ->set('withdrawal_period_days', 7)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('phytosanitary_products', [
            'user_id' => $v->id,
            'name' => 'Fungicida Test',
            'registration_number' => 'ES-12345678',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('registration_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'registration_number']);
    }

    public function test_validates_registration_number_format(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('name', 'Producto')
            ->set('registration_number', '12345678')
            ->set('registration_status', 'active')
            ->set('withdrawal_period_days', 0)
            ->call('save')
            ->assertHasErrors(['registration_number']);
    }

    public function test_validates_registration_status_enum(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('name', 'Producto')
            ->set('registration_number', 'ES-12345678')
            ->set('registration_status', 'invalido')
            ->set('withdrawal_period_days', 0)
            ->call('save')
            ->assertHasErrors(['registration_status']);
    }

    public function test_validates_product_type_enum(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('name', 'Producto')
            ->set('registration_number', 'ES-12345678')
            ->set('registration_status', 'active')
            ->set('withdrawal_period_days', 0)
            ->set('type', 'tipo_invalido')
            ->call('save')
            ->assertHasErrors(['type']);
    }
}
