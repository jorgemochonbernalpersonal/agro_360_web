<?php

namespace Tests\Feature\Winery\Oenologists;

use App\Livewire\Winery\Oenologists\Create;
use App\Livewire\Winery\Oenologists\Edit;
use App\Models\Oenologist;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class OenologistsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.oenologists.index'))
            ->assertOk();
    }

    public function test_create_validates_required_name(): void
    {
        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_saves_oenologist(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Dr. García')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('oenologists', [
            'user_id' => $this->winery->id,
            'name'    => 'Dr. García',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $oenologist = Oenologist::create([
            'user_id' => $this->winery->id,
            'name'    => 'Pedro',
            'active'  => true,
        ]);

        Livewire::test(Edit::class, ['oenologist' => $oenologist])
            ->set('name', 'Pedro Actualizado')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('oenologists', [
            'id'   => $oenologist->id,
            'name' => 'Pedro Actualizado',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $oenologist = Oenologist::create([
            'user_id' => $this->winery->id,
            'name'    => 'Test Enólogo',
            'active'  => true,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.oenologists.edit', $oenologist))
            ->assertForbidden();
    }
}
