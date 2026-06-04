<?php

namespace App\Livewire\Admin\Announcements;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AdminAnnouncement;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithReadOnlyGuard, WithToastNotifications;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $message = '';

    public string $type = 'info';

    public string $expires_at = '';

    public bool $is_active = true;

    public function openCreate(): void
    {
        $this->reset(['title', 'message', 'expires_at', 'editingId']);
        $this->type = 'info';
        $this->is_active = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $ann = AdminAnnouncement::findOrFail($id);
        $this->editingId = $id;
        $this->title = $ann->title;
        $this->message = $ann->message;
        $this->type = $ann->type;
        $this->is_active = $ann->is_active;
        $this->expires_at = $ann->expires_at?->format('Y-m-d') ?? '';
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $this->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,danger',
            'expires_at' => 'nullable|date|after:today',
        ], [
            'title.required' => __('El título es obligatorio.'),
            'message.required' => __('El mensaje es obligatorio.'),
            'expires_at.after' => __('La fecha de expiración debe ser futura.'),
        ]);

        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'expires_at' => $this->expires_at ?: null,
            'admin_id' => Auth::id(),
        ];

        if ($this->editingId) {
            AdminAnnouncement::findOrFail($this->editingId)->update($data);
            $this->toastSuccess(__('Anuncio actualizado.'));
        } else {
            AdminAnnouncement::create($data);
            SecurityLogger::logSecurityEvent('announcement_created', ['admin_id' => Auth::id(), 'title' => $this->title]);
            $this->toastSuccess(__('Anuncio publicado.'));
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $ann = AdminAnnouncement::findOrFail($id);
        $ann->update(['is_active' => ! $ann->is_active]);
        $this->toastSuccess($ann->is_active ? __('Anuncio activado.') : __('Anuncio desactivado.'));
    }

    public function delete(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        AdminAnnouncement::findOrFail($id)->delete();
        $this->toastSuccess(__('Anuncio eliminado.'));
    }

    public function render()
    {
        $announcements = AdminAnnouncement::with('admin:id,name')
            ->orderByDesc('created_at')
            ->get();

        $activeCount = AdminAnnouncement::visible()->count();

        return view('livewire.admin.announcements.index', compact('announcements', 'activeCount'))
            ->layout('layouts.app', [
                'title' => __('Anuncios - Admin - Agro365'),
                'description' => __('Gestiona los banners de anuncio visibles para los usuarios'),
            ]);
    }
}
