<?php

namespace Tests\Feature\Viticulturist\Denomination;

use App\Livewire\Viticulturist\Denomination\Index;
use App\Models\DoDocument;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    // ── basic rendering ───────────────────────────────────────────────────────

    public function test_shows_empty_state_when_no_supervisor(): void
    {
        $viticulturist = $this->makeViticulturist();

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Sin denominación asignada');
    }

    public function test_shows_supervisor_name_and_email(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $supervisor->update(['name' => 'DO Rioja', 'email' => 'do@rioja.es']);

        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('DO Rioja')
            ->assertSee('do@rioja.es');
    }

    public function test_shows_supervisor_assigned_winery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        $winery = User::factory()->create(['role' => 'winery', 'name' => 'Bodega del Supervisor', 'can_login' => true]);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'supervisor_id' => $supervisor->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Bodega del Supervisor');
    }

    // ── notebook access states ────────────────────────────────────────────────

    public function test_shows_access_granted_when_notebook_access_true(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => true]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Acceso concedido')
            ->assertSee('Revocar acceso');
    }

    public function test_shows_locked_state_when_no_access_and_no_pending_request(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => false]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Sin acceso al cuaderno');
    }

    public function test_shows_pending_request_when_do_requested_access(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => false]);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Solicitud pendiente');
    }

    public function test_approved_request_not_shown_as_pending(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => true]);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Acceso concedido')
            ->assertDontSee('Solicitud pendiente');
    }

    // ── revokeNotebookAccess ──────────────────────────────────────────────────

    public function test_revoke_clears_notebook_access_flag(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $relation = $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => true]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revokeNotebookAccess')
            ->assertDispatched('toast');

        $this->assertFalse((bool) $relation->fresh()->notebook_access);
        $this->assertNotNull($relation->fresh()->notebook_revoked_at);
    }

    public function test_revoke_also_rejects_pending_access_request(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => true]);

        $request = NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revokeNotebookAccess');

        $this->assertEquals(NotebookAccessRequest::STATUS_REJECTED, $request->fresh()->status);
    }

    public function test_revoke_fails_gracefully_when_no_access_granted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => false]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revokeNotebookAccess')
            ->assertDispatched('toast');

        // notebook_access remains false
        $this->assertDatabaseHas('supervisor_viticulturist', [
            'viticulturist_id' => $viticulturist->id,
            'notebook_access' => false,
        ]);
    }

    public function test_revoke_hides_revoke_button_after_action(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor, ['notebook_access' => true]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revokeNotebookAccess')
            ->assertDontSee('Revocar acceso');
    }

    // ── DO documents ─────────────────────────────────────────────────────────

    public function test_shows_active_do_documents(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type' => DoDocument::TYPE_PLIEGO,
            'title' => 'Pliego de Condiciones 2024',
            'status' => DoDocument::STATUS_ACTIVE,
            'effective_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertSee('Pliego de Condiciones 2024');
    }

    public function test_does_not_show_draft_or_archived_documents(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type' => DoDocument::TYPE_REGLAMENTO,
            'title' => 'Reglamento Borrador',
            'status' => DoDocument::STATUS_DRAFT,
            'effective_date' => now()->toDateString(),
        ]);

        DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type' => DoDocument::TYPE_PLIEGO,
            'title' => 'Pliego Archivado',
            'status' => DoDocument::STATUS_ARCHIVED,
            'effective_date' => now()->subYear()->toDateString(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertDontSee('Reglamento Borrador')
            ->assertDontSee('Pliego Archivado');
    }

    public function test_documents_from_other_supervisor_not_shown(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();

        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        DoDocument::create([
            'supervisor_id' => $otherSupervisor->id,
            'type' => DoDocument::TYPE_PLIEGO,
            'title' => 'Documento Ajeno',
            'status' => DoDocument::STATUS_ACTIVE,
            'effective_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertDontSee('Documento Ajeno');
    }

    public function test_no_documents_section_shown_when_none_exist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor = $this->makeSupervisor();
        $this->linkViticulturistToSupervisor($viticulturist, $supervisor);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertDontSee('Documentos de la denominación');
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeSupervisor(): User
    {
        return User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'can_login' => true,
        ]);
    }

    private function linkViticulturistToSupervisor(User $viticulturist, User $supervisor, array $attrs = []): SupervisorViticulturist
    {
        return SupervisorViticulturist::create(array_merge([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
            'notebook_access' => false,
        ], $attrs));
    }
}
