<?php

namespace Tests\Feature\Personal\Viticulturist;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_viticulturist_can_create_another_viticulturist(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($creator);

        $component = Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'nuevo@example.com')
            ->call('save');
        
        // El componente redirige a la descarga del PDF, verificar que la redirección ocurrió
        $component->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'role' => 'viticulturist',
            'email_verified_at' => null,
        ]);
    }

    public function test_created_viticulturist_has_correct_winery_viticulturist_record(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($creator);

        Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'nuevo@example.com')
            ->call('save');

        $created = User::where('email', 'nuevo@example.com')->first();

        $this->assertDatabaseHas('winery_viticulturist', [
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);
    }

    public function test_created_viticulturist_needs_password_change(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($creator);

        Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'nuevo@example.com')
            ->call('save');

        $created = User::where('email', 'nuevo@example.com')->first();

        $this->assertTrue($created->wasCreatedByAnotherUser());
        $this->assertTrue($created->needsPasswordChange());
    }

    public function test_created_viticulturist_email_not_verified(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($creator);

        Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'nuevo@example.com')
            ->call('save');

        $created = User::where('email', 'nuevo@example.com')->first();

        $this->assertNull($created->email_verified_at);
    }

    public function test_validation_works_correctly(): void
    {
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($creator);

        Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', '')
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['name', 'email']);
    }

    public function test_viticulturist_can_create_viticulturist_with_winery(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $creator = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Asignar winery al creator
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $creator->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($creator);

        Livewire::test(\App\Livewire\Viticulturist\Viticulturists\Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'nuevo@example.com')
            ->set('winery_id', $winery->id)
            ->call('save');

        $created = User::where('email', 'nuevo@example.com')->first();

        $this->assertDatabaseHas('winery_viticulturist', [
            'viticulturist_id' => $created->id,
            'winery_id' => $winery->id,
            'parent_viticulturist_id' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);
    }
}

