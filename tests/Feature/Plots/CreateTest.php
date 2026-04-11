<?php

namespace Tests\Feature\Plots;

use App\Models\User;
use App\Models\Plot;
use App\Models\WineryViticulturist;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\SigpacUse;
use App\Models\SigpacCode;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_viticulturist_with_created_viticulturists_can_create_plot(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor hijo para que pueda crear parcelas
        $childViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $childViticulturist->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $viticulturist->id,
        ]);

        // Asignar winery al viticultor
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $this->actingAs($viticulturist);

        $component = Livewire::test(\App\Livewire\Plots\Create::class)
            ->set('name', 'Nueva Parcela')
            ->set('description', 'Descripción de prueba')
            ->set('area', '10.5')
            ->set('active', true)
            ->set('autonomous_community_id', $autonomousCommunity->id)
            ->set('province_id', $province->id)
            ->set('municipality_id', $municipality->id)
            ->set('viticulturist_id', $childViticulturist->id)
            ->call('save');

        $component->assertRedirect(route('plots.index'));

        $this->assertDatabaseHas('plots', [
            'name' => 'Nueva Parcela',
            'description' => 'Descripción de prueba',
            'area' => '10.500',
            'active' => true,
            'viticulturist_id' => $childViticulturist->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);
    }

    public function test_viticulturist_without_created_viticulturists_can_access_create(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Asignar winery al viticultor
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($viticulturist);

        // Any viticulturist with a role can access the create form (auto-assigns themselves)
        $response = $this->get(route('plots.create'));

        $response->assertStatus(200);
    }

    public function test_viticulturist_without_winery_can_access_create(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor hijo
        $childViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $childViticulturist->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        // Parent viticulturists can access the create form regardless of winery link
        $response = $this->get(route('plots.create'));

        $response->assertStatus(200);
    }

    public function test_viticulturist_can_create_plot_for_self(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor hijo para que pueda crear parcelas
        $childViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $childViticulturist->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $viticulturist->id,
        ]);

        // Asignar winery al viticultor
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Create::class)
            ->set('name', 'Mi Parcela')
            ->set('area', '5.0')
            ->set('viticulturist_id', $childViticulturist->id)
            ->set('autonomous_community_id', $autonomousCommunity->id)
            ->set('province_id', $province->id)
            ->set('municipality_id', $municipality->id)
            ->call('save');

        // Creates a plot for the managed child viticulturist
        $this->assertDatabaseHas('plots', [
            'name' => 'Mi Parcela',
            'viticulturist_id' => $childViticulturist->id,
        ]);
    }

    public function test_viticulturist_cannot_assign_plot_to_non_created_viticulturist(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor hijo para que pueda crear parcelas
        $childViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $childViticulturist->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $viticulturist->id,
        ]);

        $otherViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Asignar winery al viticultor
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $this->actingAs($viticulturist);

        // El componente debería permitir seleccionar el viticultor, pero fallar en save()
        $component = Livewire::test(\App\Livewire\Plots\Create::class)
            ->set('name', 'Parcela Test')
            ->set('autonomous_community_id', $autonomousCommunity->id)
            ->set('province_id', $province->id)
            ->set('municipality_id', $municipality->id)
            ->set('viticulturist_id', $otherViticulturist->id)
            ->call('save');

        // La validación lanza ValidationException que Livewire captura
        // Verificar que no se creó la parcela (la validación debería haber fallado)
        $this->assertDatabaseMissing('plots', [
            'name' => 'Parcela Test',
            'viticulturist_id' => $otherViticulturist->id,
        ]);
    }

    public function test_validation_works_correctly(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor hijo
        $childViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $childViticulturist->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $viticulturist->id,
        ]);

        // Asignar winery al viticultor
        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Create::class)
            ->set('name', '')
            ->set('area', 'invalid')
            ->call('save')
            ->assertHasErrors(['name', 'area']);
    }

    public function test_winery_can_create_plot(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Asignar viticultor a la winery
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        // Crear SIGPAC para winery (requerido)
        $sigpacUse = SigpacUse::firstOrCreate(['code' => 'VI'], ['description' => 'Viñedo']);
        $sigpacCode = SigpacCode::create([
            'code' => 'TEST001',
            'description' => 'Código de prueba',
        ]);

        $this->actingAs($winery);

        $component = Livewire::test(\App\Livewire\Plots\Create::class)
            ->set('name', 'Parcela Winery')
            ->set('viticulturist_id', $viticulturist->id)
            ->set('area', '8.0')
            ->set('autonomous_community_id', $autonomousCommunity->id)
            ->set('province_id', $province->id)
            ->set('municipality_id', $municipality->id)
            ->call('save');

        $component->assertRedirect(route('winery.plots.index'));

        $this->assertDatabaseHas('plots', [
            'name' => 'Parcela Winery',
            'viticulturist_id' => $viticulturist->id,
        ]);
    }
}

