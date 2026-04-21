<?php

namespace Tests\Feature\Supervisor\Oversight\Wineries;

use App\Livewire\Supervisor\Oversight\Wineries\Show;
use App\Models\SupervisorWineryNote;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class WineryNotesTest extends SupervisorTestCase
{
    // ── saveNote ──────────────────────────────────────────────────────────────

    public function test_supervisor_can_save_a_note(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->set('noteType', 'note')
            ->set('noteDate', now()->format('Y-m-d'))
            ->set('noteContent', 'Visita realizada sin incidencias.')
            ->call('saveNote')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery_notes', [
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'content'       => 'Visita realizada sin incidencias.',
        ]);
    }

    public function test_save_note_closes_form(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->set('noteType', 'visit')
            ->set('noteDate', now()->format('Y-m-d'))
            ->set('noteContent', 'Visita de seguimiento.')
            ->call('saveNote')
            ->assertSet('showNoteForm', false);
    }

    public function test_save_note_requires_content(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->set('noteType', 'note')
            ->set('noteDate', now()->format('Y-m-d'))
            ->set('noteContent', '')
            ->call('saveNote')
            ->assertHasErrors(['noteContent']);
    }

    public function test_save_note_requires_valid_type(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->set('noteType', 'invalid_type')
            ->set('noteDate', now()->format('Y-m-d'))
            ->set('noteContent', 'Contenido de prueba.')
            ->call('saveNote')
            ->assertHasErrors(['noteType']);
    }

    public function test_save_note_requires_date(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->set('noteType', 'call')
            ->set('noteDate', '')
            ->set('noteContent', 'Llamada de seguimiento.')
            ->call('saveNote')
            ->assertHasErrors(['noteDate']);
    }

    public function test_all_note_types_are_accepted(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        foreach (array_keys(SupervisorWineryNote::TYPE_LABELS) as $type) {
            Livewire::actingAs($supervisor)
                ->test(Show::class, ['winery' => $winery])
                ->set('noteType', $type)
                ->set('noteDate', now()->format('Y-m-d'))
                ->set('noteContent', "Nota de tipo {$type}.")
                ->call('saveNote')
                ->assertHasNoErrors();
        }
    }

    public function test_saved_note_appears_in_view(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Nota visible en la vista.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertSee('Nota visible en la vista.');
    }

    public function test_notes_from_another_supervisor_are_not_visible(): void
    {
        [$supervisor, $winery]          = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        // Vinculamos también la misma bodega al otro supervisor para que pueda existir la nota
        \App\Models\SupervisorWinery::firstOrCreate([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
        ], ['assigned_by' => $otherSupervisor->id]);

        SupervisorWineryNote::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Nota de otro supervisor.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertViewHas('wineryNotes', fn ($notes) => $notes->isEmpty());
    }

    // ── updateNote ────────────────────────────────────────────────────────────

    public function test_supervisor_can_update_own_note(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Contenido original.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('openEditNote', $note->id)
            ->set('editNoteType', 'visit')
            ->set('editNoteDate', now()->format('Y-m-d'))
            ->set('editNoteContent', 'Contenido actualizado.')
            ->call('updateNote')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_winery_notes', [
            'id'      => $note->id,
            'type'    => 'visit',
            'content' => 'Contenido actualizado.',
        ]);
    }

    public function test_update_note_clears_edit_id_on_success(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'call',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Llamada.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('openEditNote', $note->id)
            ->set('editNoteContent', 'Actualizado.')
            ->call('updateNote')
            ->assertSet('editNoteId', null);
    }

    public function test_supervisor_cannot_update_note_from_another_supervisor(): void
    {
        [$supervisor, $winery]  = $this->makeSupervisorWithWinery();
        $otherSupervisor        = $this->makeSupervisor();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Nota ajena.',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('openEditNote', $note->id);
    }

    public function test_update_note_requires_content(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Contenido.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('openEditNote', $note->id)
            ->set('editNoteContent', '')
            ->call('updateNote')
            ->assertHasErrors(['editNoteContent']);
    }

    // ── deleteNote ────────────────────────────────────────────────────────────

    public function test_supervisor_can_delete_own_note(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'warning',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Aviso a eliminar.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('deleteNote', $note->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('supervisor_winery_notes', ['id' => $note->id]);
    }

    public function test_supervisor_cannot_delete_note_from_another_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor       = $this->makeSupervisor();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Nota ajena.',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->call('deleteNote', $note->id);
    }

    public function test_deleted_note_disappears_from_view(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $note = SupervisorWineryNote::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => 'note',
            'note_date'     => now()->format('Y-m-d'),
            'content'       => 'Nota que se va a borrar.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Show::class, ['winery' => $winery])
            ->assertSee('Nota que se va a borrar.')
            ->call('deleteNote', $note->id)
            ->assertDontSee('Nota que se va a borrar.');
    }
}
