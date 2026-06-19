<?php

namespace App\Livewire\Concerns;

use App\Models\SupervisorWinery;
use App\Models\SupervisorWineryNote;
use Illuminate\Support\Facades\Auth;

trait WithWineryNotes
{
    public bool $showNoteForm = false;

    public string $noteType = 'note';

    public string $noteDate = '';

    public string $noteContent = '';

    public ?int $editNoteId = null;

    public string $editNoteType = 'note';

    public string $editNoteDate = '';

    public string $editNoteContent = '';

    public function openNoteForm(): void
    {
        $this->noteType = 'note';
        $this->noteDate = now()->format('Y-m-d');
        $this->noteContent = '';
        $this->showNoteForm = true;
        $this->resetValidation();
    }

    public function closeNoteForm(): void
    {
        $this->showNoteForm = false;
        $this->resetValidation();
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteType' => 'required|in:'.implode(',', array_keys(SupervisorWineryNote::TYPE_LABELS)),
            'noteDate' => 'required|date',
            'noteContent' => 'required|string|max:2000',
        ]);

        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->firstOrFail();

        SupervisorWineryNote::create([
            'supervisor_id' => Auth::id(),
            'winery_id' => $this->winery->id,
            'type' => $this->noteType,
            'note_date' => $this->noteDate,
            'content' => $this->noteContent,
        ]);

        $this->showNoteForm = false;
        $this->dispatch('toast', message: __('Nota añadida al cuaderno.'), type: 'success');
    }

    public function openEditNote(int $noteId): void
    {
        $note = SupervisorWineryNote::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->findOrFail($noteId);

        $this->editNoteId = $noteId;
        $this->editNoteType = $note->type;
        $this->editNoteDate = $note->note_date->format('Y-m-d');
        $this->editNoteContent = $note->content;
        $this->resetValidation();
    }

    public function closeEditNote(): void
    {
        $this->editNoteId = null;
        $this->resetValidation();
    }

    public function updateNote(): void
    {
        $this->validate([
            'editNoteType' => 'required|in:'.implode(',', array_keys(SupervisorWineryNote::TYPE_LABELS)),
            'editNoteDate' => 'required|date',
            'editNoteContent' => 'required|string|max:2000',
        ]);

        SupervisorWineryNote::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->findOrFail($this->editNoteId)
            ->update([
                'type' => $this->editNoteType,
                'note_date' => $this->editNoteDate,
                'content' => $this->editNoteContent,
            ]);

        $this->editNoteId = null;
        $this->dispatch('toast', message: __('Nota actualizada.'), type: 'success');
    }

    public function deleteNote(int $noteId): void
    {
        SupervisorWineryNote::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->winery->id)
            ->findOrFail($noteId)
            ->delete();

        $this->dispatch('toast', message: __('Nota eliminada.'), type: 'success');
    }
}
