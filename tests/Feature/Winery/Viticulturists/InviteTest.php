<?php

namespace Tests\Feature\Winery\Viticulturists;

use App\Livewire\Winery\Viticulturists\Invite;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class InviteTest extends WineryTestCase
{
    protected User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    // ── búsqueda ──────────────────────────────────────────────────────────────

    public function test_search_shorter_than_3_chars_returns_empty(): void
    {
        User::factory()->create(['role' => 'viticulturist', 'can_login' => true, 'name' => 'Alberto']);

        $component = Livewire::test(Invite::class)
            ->set('search', 'Al');

        $this->assertEmpty($component->get('results'));
    }

    public function test_search_finds_matching_viticulturists_by_name(): void
    {
        User::factory()->create(['role' => 'viticulturist', 'can_login' => true, 'name' => 'Pedro Jiménez']);
        User::factory()->create(['role' => 'viticulturist', 'can_login' => true, 'name' => 'Ana García']);

        $component = Livewire::test(Invite::class)
            ->set('search', 'Pedro');

        $results = $component->get('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Pedro Jiménez', $results[0]['name']);
    }

    public function test_search_finds_by_email(): void
    {
        User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'email'     => 'viticultor@example.com',
        ]);

        $component = Livewire::test(Invite::class)
            ->set('search', 'viticultor@example');

        $this->assertCount(1, $component->get('results'));
    }

    public function test_search_excludes_already_linked_viticulturists(): void
    {
        $linked = User::factory()->create(['role' => 'viticulturist', 'can_login' => true, 'name' => 'Luis Linked']);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $linked->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $component = Livewire::test(Invite::class)
            ->set('search', 'Luis');

        $this->assertEmpty($component->get('results'));
    }

    public function test_search_excludes_internal_viticulturists_with_can_login_false(): void
    {
        User::factory()->create(['role' => 'viticulturist', 'can_login' => false, 'name' => 'Interno Nologin']);

        $component = Livewire::test(Invite::class)
            ->set('search', 'Interno');

        $this->assertEmpty($component->get('results'));
    }

    public function test_search_excludes_non_viticulturist_roles(): void
    {
        User::factory()->create(['role' => 'winery', 'can_login' => true, 'name' => 'Otra Bodega']);

        $component = Livewire::test(Invite::class)
            ->set('search', 'Otra Bodega');

        $this->assertEmpty($component->get('results'));
    }

    // ── vinculación ───────────────────────────────────────────────────────────

    public function test_link_creates_winery_viticulturist_with_source_own(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        Livewire::test(Invite::class)
            ->call('link', $viticulturist->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);
    }

    public function test_link_already_linked_does_not_create_duplicate(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        Livewire::test(Invite::class)
            ->call('link', $viticulturist->id);

        $this->assertDatabaseCount('winery_viticulturist', 1);
    }

    public function test_link_upgrades_self_registered_record_instead_of_creating_new(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        // Registro self-registered sin bodega
        $selfRecord = WineryViticulturist::create([
            'winery_id'        => null,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SELF,
            'assigned_by'      => null,
        ]);

        Livewire::test(Invite::class)
            ->call('link', $viticulturist->id);

        // Debe actualizar el registro existente, no crear uno nuevo
        $this->assertDatabaseCount('winery_viticulturist', 1);

        $selfRecord->refresh();
        $this->assertEquals($this->winery->id, $selfRecord->winery_id);
        $this->assertEquals(WineryViticulturist::SOURCE_OWN, $selfRecord->source);
        $this->assertEquals($this->winery->id, $selfRecord->assigned_by);
    }

    public function test_link_rejects_viticulturist_with_can_login_false(): void
    {
        $internal = User::factory()->create(['role' => 'viticulturist', 'can_login' => false]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Invite::class)
            ->call('link', $internal->id);
    }

    // ── confirmación ─────────────────────────────────────────────────────────

    public function test_confirm_sets_confirming_id(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        $component = Livewire::test(Invite::class)
            ->call('confirm', $viticulturist->id);

        $this->assertEquals($viticulturist->id, $component->get('confirmingId'));
    }

    public function test_cancel_confirmation_clears_confirming_id(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        Livewire::test(Invite::class)
            ->call('confirm', $viticulturist->id)
            ->call('cancelConfirmation')
            ->assertSet('confirmingId', null);
    }

    public function test_updating_search_resets_confirming_id(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        Livewire::test(Invite::class)
            ->call('confirm', $viticulturist->id)
            ->set('search', 'nuevo')
            ->assertSet('confirmingId', null);
    }
}
