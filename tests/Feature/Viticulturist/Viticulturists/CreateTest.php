<?php

namespace Tests\Feature\Viticulturist\Viticulturists;

use App\Livewire\Viticulturist\Viticulturists\Create;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    protected User $creator;

    protected User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        // makeViticulturist() crea el viticultor + una bodega vinculada (source=own)
        $this->creator = $this->makeViticulturist();

        $this->winery = WineryViticulturist::where('viticulturist_id', $this->creator->id)
            ->whereNotNull('winery_id')
            ->first()
            ->winery;

        $this->actingAs($this->creator);
    }

    // ── validación ────────────────────────────────────────────────────────────

    public function test_name_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('email', 'nuevo@test.com')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_email_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', '')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existente@test.com']);

        Livewire::test(Create::class)
            ->set('name', 'Nuevo Viticultor')
            ->set('email', 'existente@test.com')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_winery_id_must_belong_to_creator(): void
    {
        $otherWinery = User::factory()->create(['role' => 'winery']);

        Livewire::test(Create::class)
            ->set('name', 'Nuevo')
            ->set('email', 'nuevo@test.com')
            ->set('winery_id', $otherWinery->id)
            ->call('save')
            ->assertHasErrors(['winery_id']);
    }

    // ── creación correcta ─────────────────────────────────────────────────────

    public function test_saves_user_with_viticulturist_role_and_can_login_false(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Sub Viticultor')
            ->set('email', 'sub@test.com')
            ->set('winery_id', $this->winery->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Sub Viticultor',
            'email' => 'sub@test.com',
            'role' => 'viticulturist',
            'can_login' => false,
        ]);
    }

    public function test_creates_winery_viticulturist_with_source_viticulturist_and_parent(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Sub Viticultor')
            ->set('email', 'sub@test.com')
            ->set('winery_id', $this->winery->id)
            ->call('save')
            ->assertHasNoErrors();

        $sub = User::where('email', 'sub@test.com')->firstOrFail();

        $this->assertDatabaseHas('winery_viticulturist', [
            'viticulturist_id' => $sub->id,
            'winery_id' => $this->winery->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $this->creator->id,
            'assigned_by' => $this->creator->id,
        ]);
    }

    public function test_saves_without_winery_creates_record_with_null_winery_id(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Sin Bodega')
            ->set('email', 'sinbodega@test.com')
            ->set('winery_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $sub = User::where('email', 'sinbodega@test.com')->firstOrFail();

        $this->assertDatabaseHas('winery_viticulturist', [
            'viticulturist_id' => $sub->id,
            'winery_id' => null,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $this->creator->id,
        ]);
    }

    public function test_creator_auto_selects_winery_when_has_only_one(): void
    {
        // makeViticulturist() asigna exactamente una bodega → mount() la preselecciona
        $component = Livewire::test(Create::class);

        $this->assertEquals($this->winery->id, $component->get('winery_id'));
    }
}
