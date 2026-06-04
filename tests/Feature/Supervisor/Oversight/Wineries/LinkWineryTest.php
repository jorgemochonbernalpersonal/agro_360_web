<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Index;
use App\Models\SupervisorWinery;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class LinkWineryTest extends SupervisorTestCase
{
    // ── linkWinery ────────────────────────────────────────────────────────────

    public function test_supervisor_can_link_a_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkWineryId', $winery->id)
            ->call('linkWinery')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);
    }

    public function test_supervisor_can_link_a_producer(): void
    {
        $supervisor = $this->makeSupervisor();
        $producer = User::factory()->create([
            'role' => User::ROLE_PRODUCER,
            'email_verified_at' => now(),
            'can_login' => true,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkWineryId', $producer->id)
            ->call('linkWinery')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $producer->id,
        ]);
    }

    public function test_link_winery_requires_winery_id(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkWineryId', null)
            ->call('linkWinery')
            ->assertHasErrors(['linkWineryId']);
    }

    public function test_link_winery_requires_existing_user(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkWineryId', 99999)
            ->call('linkWinery')
            ->assertHasErrors(['linkWineryId']);
    }

    public function test_linking_already_linked_winery_shows_error_toast(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkWineryId', $winery->id)
            ->call('linkWinery')
            ->assertDispatched('toast');

        $this->assertDatabaseCount('supervisor_winery', 1);
    }

    public function test_link_closes_modal_on_success(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->set('linkWineryId', $winery->id)
            ->call('linkWinery')
            ->assertSet('showLinkModal', false);
    }

    public function test_link_candidates_excludes_already_linked_wineries(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['name' => 'Bodega Ya Adscrita']);

        $other = $this->makeWinery();
        $other->update(['name' => 'Bodega Disponible']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->set('linkSearch', 'Bodega')
            ->assertViewHas('linkCandidates', fn ($c) => $c->contains('id', $other->id))
            ->assertViewHas('linkCandidates', fn ($c) => $c->doesntContain('id', $winery->id));
    }

    public function test_link_candidates_only_appear_with_two_or_more_characters(): void
    {
        $supervisor = $this->makeSupervisor();
        $this->makeWinery()->update(['name' => 'Bodega Test']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->set('linkSearch', 'B')
            ->assertViewHas('linkCandidates', fn ($c) => $c->isEmpty());
    }

    // ── unlinkWinery ──────────────────────────────────────────────────────────

    public function test_supervisor_can_unlink_a_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('unlinkWinery', $winery->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
        ]);
    }

    public function test_supervisor_cannot_unlink_winery_from_another_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        SupervisorWinery::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $otherSupervisor->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('unlinkWinery', $winery->id);
    }

    public function test_unlinked_winery_disappears_from_list(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $winery->update(['name' => 'Bodega Para Desvincular']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('Bodega Para Desvincular')
            ->call('unlinkWinery', $winery->id)
            ->assertDontSee('Bodega Para Desvincular');
    }
}
