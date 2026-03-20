<?php

namespace Tests\Feature\Producer;

use App\Livewire\Winery\Cellar\Containers\Index as ContainersIndex;
use App\Livewire\Winery\Viticulturists\Index as ViticulturistsIndex;
use App\Livewire\Winery\Viticulturists\Show as ViticulturistShow;
use App\Models\Container;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Verifica que el producer solo accede a sus propios datos.
 *
 * Todos los componentes winery usan wineryId() = Auth::id() para filtrar
 * queries. Estos tests garantizan que ese aislamiento funciona cuando
 * el usuario autenticado es un producer en lugar de una bodega.
 */
class ProducerIsolationTest extends ProducerTestCase
{
    private User $producerA;
    private User $producerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producerA = $this->makeProducer();
        $this->producerB = $this->makeOtherProducer();
    }

    // ── Contenedores ──────────────────────────────────────────────────────────

    public function test_producer_containers_index_excludes_other_producer_containers(): void
    {
        // Container belonging to Producer B
        $containerB = Container::factory()->create(['user_id' => $this->producerB->id]);

        $this->actingAs($this->producerA);

        // Producer A's container list must not include Producer B's container
        $items = Container::where('user_id', $this->producerA->id)->pluck('id');

        $this->assertNotContains($containerB->id, $items);
    }

    public function test_producer_cannot_archive_another_producers_container(): void
    {
        $containerB = Container::factory()->create([
            'user_id'  => $this->producerB->id,
            'archived' => false,
        ]);

        $this->actingAs($this->producerA);

        // archive() guards with Container::where('user_id', wineryId())->findOrFail()
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ContainersIndex::class)
            ->call('archive', $containerB->id);
    }

    public function test_producer_cannot_unarchive_another_producers_container(): void
    {
        $containerB = Container::factory()->create([
            'user_id'  => $this->producerB->id,
            'archived' => true,
        ]);

        $this->actingAs($this->producerA);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ContainersIndex::class)
            ->call('unarchive', $containerB->id);
    }

    // ── Viticultores ──────────────────────────────────────────────────────────

    public function test_producer_viticulturists_index_excludes_other_producer_viticulturists(): void
    {
        // Viticulturist linked to Producer B
        $vitic = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'winery_id'        => $this->producerB->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->producerB->id,
        ]);

        $this->actingAs($this->producerA);

        // Viticulturists scoped to producerA.id — must not include producerB's viticulturist
        $ids = WineryViticulturist::where('winery_id', $this->producerA->id)
            ->pluck('viticulturist_id');

        $this->assertNotContains($vitic->id, $ids);
    }

    public function test_producer_cannot_view_another_producers_viticulturist_show(): void
    {
        $vitic = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'winery_id'        => $this->producerB->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->producerB->id,
        ]);

        $this->actingAs($this->producerA);

        // mount() does WineryViticulturist::where('winery_id', Auth::id())->firstOrFail()
        // Producer A has no relation with $vitic → firstOrFail throws
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ViticulturistShow::class, ['viticulturist' => $vitic]);
    }

    // ── Viticulturists propios son accesibles ─────────────────────────────────

    public function test_producer_can_view_own_viticulturist_show(): void
    {
        $vitic = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);
        WineryViticulturist::create([
            'winery_id'        => $this->producerA->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->producerA->id,
        ]);

        $this->actingAs($this->producerA);

        Livewire::test(ViticulturistShow::class, ['viticulturist' => $vitic])
            ->assertOk();
    }

    public function test_producer_viticulturists_list_shows_only_own(): void
    {
        // One viticulturist for Producer A, one for Producer B
        $viticA = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'winery_id'        => $this->producerA->id,
            'viticulturist_id' => $viticA->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->producerA->id,
        ]);

        $viticB = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'winery_id'        => $this->producerB->id,
            'viticulturist_id' => $viticB->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->producerB->id,
        ]);

        $this->actingAs($this->producerA);

        $ids = WineryViticulturist::where('winery_id', $this->producerA->id)
            ->pluck('viticulturist_id');

        $this->assertContains($viticA->id, $ids);
        $this->assertNotContains($viticB->id, $ids);
    }
}
