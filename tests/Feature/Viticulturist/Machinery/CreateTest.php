<?php

namespace Tests\Feature\Viticulturist\Machinery;

use App\Livewire\Viticulturist\Machinery\Create;
use App\Models\Machinery;
use App\Models\MachineryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_viticulturist_can_create_machinery_via_livewire(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $type = MachineryType::create(['name' => 'Tractor', 'active' => true]);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Tractor Test')
            ->set('machinery_type_id', $type->id)
            ->set('brand', 'Marca X')
            ->set('model', 'Modelo Y')
            ->set('year', now()->year)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.machinery.index'));

        $this->assertDatabaseHas('machinery', [
            'name'             => 'Tractor Test',
            'type'             => 'Tractor',
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_validation_fails_with_invalid_data(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('machinery_type_id', '')
            ->set('year', 1800) // menor que 1900
            ->call('save')
            ->assertHasErrors(['name', 'machinery_type_id', 'year']);

        $this->assertSame(0, Machinery::count());
    }
}
