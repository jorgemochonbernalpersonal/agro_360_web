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
}
