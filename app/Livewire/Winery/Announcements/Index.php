<?php

namespace App\Livewire\Winery\Announcements;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineryAnnouncement;
use App\Models\WineryViticulturist;
use App\Notifications\WineryAnnouncementNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public bool $showCreateModal = false;

    // Form fields
    public string $title       = '';
    public string $body        = '';
    public string $type        = 'info';
    public string $target      = 'all';
    public ?string $expires_at = null;
    public array $selectedViticulturists = [];

    protected function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:150'],
            'body'       => ['required', 'string', 'max:2000'],
            'type'       => ['required', 'in:info,action_required,harvest_alert'],
            'target'     => ['required', 'in:all,specific'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'selectedViticulturists' => ['required_if:target,specific', 'array'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['title', 'body', 'type', 'target', 'expires_at', 'selectedViticulturists']);
        $this->showCreateModal = true;
    }

    public function publish(): void
    {
        $this->validate();

        $announcement = WineryAnnouncement::create([
            'winery_id'    => Auth::id(),
            'title'        => $this->title,
            'body'         => $this->body,
            'type'         => $this->type,
            'target'       => $this->target,
            'published_at' => now(),
            'expires_at'   => $this->expires_at ?: null,
        ]);

        // Determinar destinatarios
        if ($this->target === 'specific' && !empty($this->selectedViticulturists)) {
            $recipientIds = $this->selectedViticulturists;
            foreach ($recipientIds as $vitId) {
                $announcement->viticulturists()->attach($vitId);
            }
        } else {
            $recipientIds = WineryViticulturist::where('winery_id', Auth::id())
                ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))
                ->pluck('viticulturist_id')
                ->toArray();
        }

        // Notificar a viticulturists activos
        $recipients = \App\Models\User::whereIn('id', $recipientIds)
            ->where('can_login', true)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new WineryAnnouncementNotification($announcement));
        }

        $this->showCreateModal = false;
        $this->toastSuccess("Aviso publicado a {$recipients->count()} viticultor(es).");
    }

    public function expire(int $id): void
    {
        $announcement = WineryAnnouncement::where('winery_id', Auth::id())->findOrFail($id);
        $announcement->update(['expires_at' => now()]);
        $this->toastSuccess('Aviso expirado.');
    }

    public function delete(int $id): void
    {
        WineryAnnouncement::where('winery_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess('Aviso eliminado.');
    }

    public function render()
    {
        $wineryId = Auth::id();

        $announcements = WineryAnnouncement::where('winery_id', $wineryId)
            ->orderByDesc('published_at')
            ->paginate(15);

        $viticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->whereHas('viticulturist', fn ($q) => $q->where('can_login', true))
            ->with('viticulturist:id,name')
            ->get();

        return view('livewire.winery.announcements.index', [
            'announcements'  => $announcements,
            'viticulturists' => $viticulturists,
            'typeLabels'     => WineryAnnouncement::TYPE_LABELS,
        ])->layout('layouts.app');
    }
}
