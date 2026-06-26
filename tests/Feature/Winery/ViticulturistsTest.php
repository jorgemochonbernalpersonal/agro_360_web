<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Viticulturists\Create;
use App\Livewire\Winery\Viticulturists\Edit;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ViticulturistsTest extends WineryTestCase
{
    protected User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.viticulturists.index'))
            ->assertOk();
    }

    public function test_create_validates_required_name(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_saves_viticulturist_with_winery_link(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'José García')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'José García',
            'role' => 'viticulturist',
        ]);

        $viticulturist = User::where('name', 'José García')->first();

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
        ]);

        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);

        Livewire::test(Edit::class, ['viticulturist' => $viticulturist])
            ->set('name', 'Nuevo Nombre')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $viticulturist->id,
            'name' => 'Nuevo Nombre',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
        ]);

        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.viticulturists.edit', $viticulturist))
            ->assertNotFound();
    }

    public function test_cannot_tamper_viticulturist_id_to_update_foreign_viticulturist(): void
    {
        $ownViticulturist = User::factory()->create(['role' => 'viticulturist', 'name' => 'Propio']);
        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $ownViticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);

        $otherWinery = $this->makeOtherWinery();
        $foreignViticulturist = User::factory()->create(['role' => 'viticulturist', 'name' => 'Ajeno Original']);
        WineryViticulturist::create([
            'winery_id' => $otherWinery->id,
            'viticulturist_id' => $foreignViticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $otherWinery->id,
        ]);

        try {
            Livewire::test(Edit::class, ['viticulturist' => $ownViticulturist])
                ->set('viticulturistId', $foreignViticulturist->id)
                ->set('name', 'Nombre Atacante')
                ->call('save');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Expected: ownership check blocked the tampered ID
        }

        // Either way, the foreign viticulturist's name must not have been changed
        $this->assertDatabaseHas('users', [
            'id' => $foreignViticulturist->id,
            'name' => 'Ajeno Original',
        ]);
    }
}
