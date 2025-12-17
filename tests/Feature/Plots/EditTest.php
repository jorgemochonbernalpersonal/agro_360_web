<?php

namespace Tests\Feature\Plots;

use App\Models\User;
use App\Models\Plot;
use App\Models\WineryViticulturist;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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

    public function test_viticulturist_can_edit_own_plot(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

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

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('name', 'Parcela Actualizada')
            ->set('description', 'Nueva descripción')
            ->set('area', '15.75')
            ->call('update')
            ->assertRedirect(route('plots.index'));

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'name' => 'Parcela Actualizada',
            'description' => 'Nueva descripción',
            'area' => '15.750',
        ]);
    }

    public function test_viticulturist_can_edit_plot_of_created_viticulturist(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

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

        $plot = Plot::factory()->create([
            'viticulturist_id' => $childViticulturist->id,
            'winery_id' => $winery->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('name', 'Parcela Editada')
            ->call('update')
            ->assertRedirect(route('plots.index'));

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'name' => 'Parcela Editada',
        ]);
    }

    public function test_viticulturist_cannot_edit_plot_of_non_created_viticulturist(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $otherViticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $otherViticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $plot = Plot::factory()->create([
            'viticulturist_id' => $otherViticulturist->id,
            'winery_id' => $winery->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $this->actingAs($viticulturist);

        // Intentar acceder a la página de edición debería dar 403
        $response = $this->get(route('plots.edit', $plot));
        
        $response->assertStatus(403);
    }

    public function test_winery_can_edit_plot_of_own_viticulturist(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        // Crear SIGPAC para la parcela (requerido para winery)
        $sigpacUse = \App\Models\SigpacUse::create([
            'code' => 'VI',
            'description' => 'Viñedo',
        ]);
        $sigpacCode = \App\Models\SigpacCode::create([
            'code' => 'TEST001',
            'description' => 'Código de prueba',
        ]);

        $plot = Plot::factory()->create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        // Sincronizar SIGPAC
        $plot->sigpacUses()->sync([$sigpacUse->id]);
        $plot->sigpacCodes()->sync([$sigpacCode->id]);

        $this->actingAs($winery);

        $component = Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('name', 'Parcela Winery Editada')
            ->call('update');

        $component->assertRedirect(route('plots.index'));

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'name' => 'Parcela Winery Editada',
        ]);
    }

    public function test_winery_cannot_edit_plot_of_non_own_viticulturist(): void
    {
        $winery1 = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $winery2 = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Asignar viticultor a winery2
        WineryViticulturist::create([
            'winery_id' => $winery2->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery2->id,
        ]);

        $autonomousCommunity = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $plot = Plot::factory()->create([
            'winery_id' => $winery2->id,
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $this->actingAs($winery1);

        // Intentar acceder a la página de edición debería dar 403
        $response = $this->get(route('plots.edit', $plot));
        
        $response->assertStatus(403);
    }

    public function test_validation_works_correctly_on_edit(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

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

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('name', '')
            ->set('area', 'invalid')
            ->call('update')
            ->assertHasErrors(['name', 'area']);
    }

    public function test_location_fields_can_be_updated(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $autonomousCommunity1 = AutonomousCommunity::first();
        $province1 = Province::where('autonomous_community_id', $autonomousCommunity1->id)->first();
        $municipality1 = Municipality::where('province_id', $province1->id)->first();

        $autonomousCommunity2 = AutonomousCommunity::skip(1)->first();
        $province2 = Province::where('autonomous_community_id', $autonomousCommunity2->id)->first();
        $municipality2 = Municipality::where('province_id', $province2->id)->first();

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery->id,
            'autonomous_community_id' => $autonomousCommunity1->id,
            'province_id' => $province1->id,
            'municipality_id' => $municipality1->id,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('autonomous_community_id', $autonomousCommunity2->id)
            ->set('province_id', $province2->id)
            ->set('municipality_id', $municipality2->id)
            ->call('update')
            ->assertRedirect(route('plots.index'));

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'autonomous_community_id' => $autonomousCommunity2->id,
            'province_id' => $province2->id,
            'municipality_id' => $municipality2->id,
        ]);
    }
}

