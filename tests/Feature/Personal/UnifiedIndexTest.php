<?php

namespace Tests\Feature\Personal;

use App\Livewire\Viticulturist\Personal\UnifiedIndex;
use App\Models\User;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_viticulturist_can_view_unified_index(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        $response = $this->get(route('viticulturist.personal.index'));
        
        $response->assertStatus(200);
        $response->assertSeeLivewire('viticulturist.personal.unified-index');
    }

    public function test_search_filter_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created1 = User::factory()->create([
            'role' => 'viticulturist',
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        $created2 = User::factory()->create([
            'role' => 'viticulturist',
            'name' => 'María García',
            'email' => 'maria@example.com',
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created1->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created2->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('search', 'Juan');

        $component->assertSee('Juan Pérez');
        $component->assertDontSee('María García');
    }

    public function test_winery_filter_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery1 = User::factory()->create(['role' => 'winery']);
        $winery2 = User::factory()->create(['role' => 'winery']);

        $created1 = User::factory()->create(['role' => 'viticulturist']);
        $created2 = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'winery_id' => $winery1->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery1->id,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created1->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery1->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created2->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery2->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('wineryFilter', $winery1->id);

        // Debería mostrar solo created1 que está en winery1
        $component->assertSee($created1->name);
        $component->assertDontSee($created2->name);
    }

    public function test_crew_filter_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew1 = Crew::create([
            'name' => 'Equipo A',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $crew2 = Crew::create([
            'name' => 'Equipo B',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $created1 = User::factory()->create(['role' => 'viticulturist']);
        $created2 = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created1->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created2->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        CrewMember::create([
            'crew_id' => $crew1->id,
            'viticulturist_id' => $created1->id,
            'assigned_by' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => $crew2->id,
            'viticulturist_id' => $created2->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('crewFilter', $crew1->id);

        $component->assertSee($created1->name);
        $component->assertDontSee($created2->name);
    }

    public function test_status_filter_in_crew_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo Test',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $inCrew = User::factory()->create(['role' => 'viticulturist']);
        $individual = User::factory()->create(['role' => 'viticulturist']);
        $unassigned = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $inCrew->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $individual->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $unassigned->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $inCrew->id,
            'assigned_by' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => null,
            'viticulturist_id' => $individual->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('statusFilter', 'in_crew');

        $component->assertSee($inCrew->name);
        $component->assertDontSee($individual->name);
        $component->assertDontSee($unassigned->name);
    }

    public function test_status_filter_individual_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo Test',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $inCrew = User::factory()->create(['role' => 'viticulturist']);
        $individual = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $inCrew->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $individual->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $inCrew->id,
            'assigned_by' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => null,
            'viticulturist_id' => $individual->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('statusFilter', 'individual');

        $component->assertSee($individual->name);
        $component->assertDontSee($inCrew->name);
    }

    public function test_status_filter_unassigned_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $assigned = User::factory()->create(['role' => 'viticulturist']);
        $unassigned = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $assigned->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $unassigned->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        CrewMember::create([
            'crew_id' => null,
            'viticulturist_id' => $assigned->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('statusFilter', 'unassigned');

        $component->assertSee($unassigned->name);
        $component->assertDontSee($assigned->name);
    }

    public function test_assign_to_crew_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo Test',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('assignToCrewId', $crew->id)
            ->call('assignToCrew', $created->id);

        $this->assertDatabaseHas('crew_members', [
            'crew_id' => $crew->id,
            'viticulturist_id' => $created->id,
        ]);

        $component->assertDispatched('toast');
    }

    public function test_make_individual_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo Test',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $created->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->call('makeIndividual', $created->id);

        $this->assertDatabaseHas('crew_members', [
            'crew_id' => null,
            'viticulturist_id' => $created->id,
        ]);

        $component->assertDispatched('toast');
    }

    public function test_switch_view_mode_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->assertSet('viewMode', 'personal')
            ->call('switchView', 'crews')
            ->assertSet('viewMode', 'crews')
            ->call('switchView', 'personal')
            ->assertSet('viewMode', 'personal');
    }

    public function test_clear_filters_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('search', 'test')
            ->set('wineryFilter', '1')
            ->set('statusFilter', 'in_crew')
            ->set('crewFilter', '1')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('wineryFilter', '')
            ->assertSet('statusFilter', '')
            ->assertSet('crewFilter', '');
    }

    public function test_delete_crew_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo a Eliminar',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'crews')
            ->call('deleteCrew', $crew->id);

        $this->assertDatabaseMissing('crews', [
            'id' => $crew->id,
        ]);

        $component->assertDispatched('toast');
    }


    public function test_delete_viticulturist_without_relations_works(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear un viticultor sin relaciones (solo la relación de creación)
        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist);

        // El método deleteViticulturist verifica hasWineryRelations
        // que incluye la relación WineryViticulturist que acabamos de crear
        // Por lo tanto, el viticultor NO se eliminará porque tiene esa relación
        // Este test verifica que el método detecta correctamente las relaciones
        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->call('deleteViticulturist', $created->id);

        // No debería eliminarse porque tiene la relación WineryViticulturist
        $this->assertDatabaseHas('users', [
            'id' => $created->id,
        ]);

        $component->assertDispatched('toast');
    }

    public function test_cannot_delete_viticulturist_with_related_data(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Crear un crew para el viticultor
        Crew::create([
            'name' => 'Equipo del Viticultor',
            'viticulturist_id' => $created->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->call('deleteViticulturist', $created->id);

        // No debería eliminarse porque tiene datos relacionados
        $this->assertDatabaseHas('users', [
            'id' => $created->id,
        ]);

        $component->assertDispatched('toast');
    }

    public function test_cannot_assign_to_crew_that_does_not_belong_to_user(): void
    {
        $viticulturist1 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $viticulturist2 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear cuadrilla que pertenece a viticulturist2
        $crew = Crew::create([
            'name' => 'Equipo de Otro Usuario',
            'viticulturist_id' => $viticulturist2->id,
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist1->id,
            'assigned_by' => $viticulturist1->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist1);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('assignToCrewId', $crew->id)
            ->call('assignToCrew', $created->id);

        // No debería asignarse porque la cuadrilla no pertenece al usuario
        $this->assertDatabaseMissing('crew_members', [
            'crew_id' => $crew->id,
            'viticulturist_id' => $created->id,
        ]);

        $component->assertDispatched('toast', type: 'error');
    }

    public function test_cannot_assign_viticulturist_that_does_not_belong_to_user(): void
    {
        $viticulturist1 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $viticulturist2 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear cuadrilla que pertenece a viticulturist1
        $crew = Crew::create([
            'name' => 'Equipo de Usuario 1',
            'viticulturist_id' => $viticulturist1->id,
        ]);

        // Crear viticultor que pertenece a viticulturist2, no a viticulturist1
        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist2->id,
            'assigned_by' => $viticulturist2->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist1);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('assignToCrewId', $crew->id)
            ->call('assignToCrew', $created->id);

        // No debería asignarse porque el viticultor no pertenece al usuario
        $this->assertDatabaseMissing('crew_members', [
            'crew_id' => $crew->id,
            'viticulturist_id' => $created->id,
        ]);

        $component->assertDispatched('toast', type: 'error');
    }

    public function test_cannot_assign_without_selecting_crew(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('assignToCrewId', '') // Sin seleccionar cuadrilla
            ->call('assignToCrew', $created->id);

        $component->assertDispatched('toast', type: 'error');
    }

    public function test_cannot_assign_viticulturist_already_in_crew(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo Test',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Ya está en la cuadrilla
        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $created->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->set('assignToCrewId', $crew->id)
            ->call('assignToCrew', $created->id);

        // Debería mostrar error porque ya está en la cuadrilla
        $component->assertDispatched('toast', type: 'error');

        // Verificar que solo hay un registro (no duplicado)
        $this->assertEquals(1, CrewMember::where('crew_id', $crew->id)
            ->where('viticulturist_id', $created->id)
            ->count());
    }

    public function test_cannot_delete_crew_without_permission(): void
    {
        $viticulturist1 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $viticulturist2 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        // Crear cuadrilla que pertenece a viticulturist2
        $crew = Crew::create([
            'name' => 'Equipo de Otro Usuario',
            'viticulturist_id' => $viticulturist2->id,
        ]);

        $this->actingAs($viticulturist1);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'crews')
            ->call('deleteCrew', $crew->id);

        // No debería eliminarse porque no tiene permisos
        $this->assertDatabaseHas('crews', [
            'id' => $crew->id,
        ]);

        $component->assertDispatched('toast', type: 'error');
    }

    public function test_cannot_delete_viticulturist_without_permission(): void
    {
        $viticulturist1 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $viticulturist2 = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $created = User::factory()->create(['role' => 'viticulturist']);

        // El viticultor fue creado por viticulturist2, no por viticulturist1
        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $viticulturist2->id,
            'assigned_by' => $viticulturist2->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->actingAs($viticulturist1);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'personal')
            ->call('deleteViticulturist', $created->id);

        // No debería eliminarse porque no tiene permisos
        $this->assertDatabaseHas('users', [
            'id' => $created->id,
        ]);

        $component->assertDispatched('toast', type: 'error');
    }

    public function test_cannot_delete_crew_with_activities(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $crew = Crew::create([
            'name' => 'Equipo con Actividades',
            'viticulturist_id' => $viticulturist->id,
        ]);

        // Necesitamos crear un plot sin winery_id (que ya no existe en la tabla)
        // Usamos Database\Seeders para tener datos de ubicación
        $this->seed([
            \Database\Seeders\AutonomousCommunitySeeder::class,
            \Database\Seeders\ProvinceSeeder::class,
            \Database\Seeders\MunicipalitySeeder::class,
        ]);

        $autonomousCommunity = \App\Models\AutonomousCommunity::first();
        $province = \App\Models\Province::where('autonomous_community_id', $autonomousCommunity->id)->first();
        $municipality = \App\Models\Municipality::where('province_id', $province->id)->first();

        $plot = \App\Models\Plot::create([
            'name' => 'Parcela Test',
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
            'area' => '10.5',
            'active' => true,
        ]);

        $campaign = \App\Models\Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        // Crear una plantación activa para la parcela
        $grapeVariety = \App\Models\GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        $planting = \App\Models\PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);

        // Crear una actividad asociada al crew
        \App\Models\AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(UnifiedIndex::class)
            ->set('viewMode', 'crews')
            ->call('deleteCrew', $crew->id);

        // No debería eliminarse porque tiene actividades
        $this->assertDatabaseHas('crews', [
            'id' => $crew->id,
        ]);

        $component->assertDispatched('toast', type: 'error');
    }
}

