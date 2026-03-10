<?php

namespace Tests\Feature\Viticulturist\Machinery;

use App\Livewire\Viticulturist\Machinery\Edit;
use App\Models\Machinery;
use App\Models\MachineryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_viticulturist_can_update_own_machinery_via_livewire(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $type = MachineryType::create(['name' => 'Tractor', 'active' => true]);

        $machinery = Machinery::create([
            'name'              => 'Tractor Original',
            'type'              => 'Tractor',
            'machinery_type_id' => $type->id,
            'viticulturist_id'  => $viticulturist->id,
            'active'            => true,
            'is_rented'         => false,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['machinery' => $machinery])
            ->set('name', 'Tractor Actualizado')
            ->set('machinery_type_id', $type->id)
            ->set('year', now()->year)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.machinery.index'));

        $this->assertDatabaseHas('machinery', [
            'id'   => $machinery->id,
            'name' => 'Tractor Actualizado',
            'year' => now()->year,
        ]);
    }

    public function test_validation_fails_when_updating_with_invalid_data(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $type = MachineryType::create(['name' => 'Tractor', 'active' => true]);

        $machinery = Machinery::create([
            'name'              => 'Tractor Original',
            'type'              => 'Tractor',
            'machinery_type_id' => $type->id,
            'viticulturist_id'  => $viticulturist->id,
            'active'            => true,
            'is_rented'         => false,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['machinery' => $machinery])
            ->set('name', '')
            ->set('machinery_type_id', '')
            ->set('year', 1800)
            ->call('save')
            ->assertHasErrors(['name', 'machinery_type_id', 'year']);

        // Los datos originales deben mantenerse
        $this->assertEquals('Tractor Original', $machinery->fresh()->name);
    }
}
