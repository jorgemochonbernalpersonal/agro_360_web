<?php

namespace Tests\Feature\Supervisor\Documents;

use App\Livewire\Supervisor\Documents\Index;
use App\Models\DoDocument;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_documents_page(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.documents.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_documents_page(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.documents.index'))
            ->assertForbidden();
    }

    // ── create draft ──────────────────────────────────────────────────────────

    public function test_create_draft_document(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openCreate')
            ->set('formType', DoDocument::TYPE_PLIEGO)
            ->set('formTitle', 'Pliego de Condiciones 2026')
            ->set('formVersion', 'v1.0')
            ->set('formDate', '2026-01-01')
            ->set('formContent', 'Contenido del pliego.')
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Pliego de Condiciones 2026',
            'version'       => 'v1.0',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);
    }

    public function test_create_document_requires_title(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openCreate')
            ->set('formTitle', '')
            ->call('save')
            ->assertHasErrors(['formTitle']);
    }

    // ── edit draft ────────────────────────────────────────────────────────────

    public function test_edit_draft_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Original',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $doc->id)
            ->assertSet('editingId', $doc->id)
            ->assertSet('formTitle', 'Original')
            ->set('formTitle', 'Título Actualizado')
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'    => $doc->id,
            'title' => 'Título Actualizado',
        ]);
    }

    public function test_cannot_edit_active_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Documento Activo',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $doc->id)
            ->assertSet('showModal', false)
            ->assertDispatched('toast');
    }

    // ── publish ───────────────────────────────────────────────────────────────

    public function test_publish_draft_sets_status_to_active(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Draft Pliego',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('publish', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'     => $doc->id,
            'status' => DoDocument::STATUS_ACTIVE,
        ]);
    }

    public function test_publish_auto_archives_previous_active_of_same_type(): void
    {
        $supervisor = $this->makeSupervisor();

        $existing = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Pliego Anterior',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        $newDraft = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Pliego Nuevo',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('publish', $newDraft->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'     => $existing->id,
            'status' => DoDocument::STATUS_ARCHIVED,
        ]);

        $this->assertDatabaseHas('do_documents', [
            'id'     => $newDraft->id,
            'status' => DoDocument::STATUS_ACTIVE,
        ]);
    }

    public function test_publish_does_not_archive_different_type(): void
    {
        $supervisor = $this->makeSupervisor();

        $activeReglamento = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_REGLAMENTO,
            'title'         => 'Reglamento Vigente',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        $newPliego = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Pliego Nuevo',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('publish', $newPliego->id);

        $this->assertDatabaseHas('do_documents', [
            'id'     => $activeReglamento->id,
            'status' => DoDocument::STATUS_ACTIVE,
        ]);
    }

    public function test_cannot_publish_active_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Ya Activo',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('publish', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'     => $doc->id,
            'status' => DoDocument::STATUS_ACTIVE,
        ]);
    }

    // ── archive ───────────────────────────────────────────────────────────────

    public function test_archive_active_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_REGLAMENTO,
            'title'         => 'Reglamento 2024',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('archive', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'     => $doc->id,
            'status' => DoDocument::STATUS_ARCHIVED,
        ]);
    }

    public function test_cannot_archive_draft_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_REGLAMENTO,
            'title'         => 'Solo Borrador',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('archive', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', [
            'id'     => $doc->id,
            'status' => DoDocument::STATUS_DRAFT,
        ]);
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function test_delete_draft_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Borrador Eliminable',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('delete', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('do_documents', ['id' => $doc->id]);
    }

    public function test_cannot_delete_active_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $doc = DoDocument::create([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Activo No Borrable',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('delete', $doc->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_documents', ['id' => $doc->id]);
    }

    // ── tab filtering ─────────────────────────────────────────────────────────

    public function test_active_tab_shows_only_active_documents(): void
    {
        $supervisor = $this->makeSupervisor();

        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO, 'title' => 'Vigente', 'status' => DoDocument::STATUS_ACTIVE]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO, 'title' => 'Borrador', 'status' => DoDocument::STATUS_DRAFT]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO, 'title' => 'Archivado', 'status' => DoDocument::STATUS_ARCHIVED]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSet('activeTab', 'active')
            ->assertViewHas('documents', fn($d) => $d->count() === 1 && $d->first()->title === 'Vigente');
    }

    public function test_switching_to_draft_tab_shows_only_drafts(): void
    {
        $supervisor = $this->makeSupervisor();

        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO, 'title' => 'Vigente', 'status' => DoDocument::STATUS_ACTIVE]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO, 'title' => 'Borrador', 'status' => DoDocument::STATUS_DRAFT]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setTab', 'draft')
            ->assertSet('activeTab', 'draft')
            ->assertViewHas('documents', fn($d) => $d->count() === 1 && $d->first()->title === 'Borrador');
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_counts_reflect_document_statuses(): void
    {
        $supervisor = $this->makeSupervisor();

        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO,     'title' => 'A1', 'status' => DoDocument::STATUS_ACTIVE]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_REGLAMENTO, 'title' => 'D1', 'status' => DoDocument::STATUS_DRAFT]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_REGLAMENTO, 'title' => 'D2', 'status' => DoDocument::STATUS_DRAFT]);
        DoDocument::create(['supervisor_id' => $supervisor->id, 'type' => DoDocument::TYPE_PLIEGO,     'title' => 'Ar1', 'status' => DoDocument::STATUS_ARCHIVED]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('counts', fn($c) => $c['active'] === 1 && $c['draft'] === 2 && $c['archived'] === 1);
    }

    // ── isolation ─────────────────────────────────────────────────────────────

    public function test_documents_isolated_from_other_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();
        $other      = $this->makeSupervisor();

        DoDocument::create([
            'supervisor_id' => $other->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Documento Ajeno',
            'status'        => DoDocument::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('documents', fn($d) => $d->isEmpty())
            ->assertViewHas('counts', fn($c) => $c['active'] === 0);
    }

    public function test_supervisor_cannot_edit_another_supervisors_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $other      = $this->makeSupervisor();

        $doc = DoDocument::create([
            'supervisor_id' => $other->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Ajeno',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openEdit', $doc->id);
    }

    public function test_supervisor_cannot_delete_another_supervisors_document(): void
    {
        $supervisor = $this->makeSupervisor();
        $other      = $this->makeSupervisor();

        $doc = DoDocument::create([
            'supervisor_id' => $other->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Ajeno',
            'status'        => DoDocument::STATUS_DRAFT,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('delete', $doc->id);
    }
}
