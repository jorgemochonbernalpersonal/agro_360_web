<?php

namespace App\Livewire\Admin\Notifications;

use App\Mail\AdminBroadcastMail;
use App\Models\NotificationLog;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications, WithReadOnlyGuard;

    public $subject          = '';
    public $message          = '';
    public $audienceRole     = 'all';
    public $audienceVerified = '1';
    public $audienceActive   = '1';

    // Preview modal
    public bool $showPreviewModal = false;

    public function getRecipientsProperty(): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query();

        if ($this->audienceRole !== 'all') {
            $query->where('role', $this->audienceRole);
        }

        if ($this->audienceVerified !== '') {
            if ($this->audienceVerified === '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($this->audienceActive !== '') {
            $query->where('can_login', $this->audienceActive === '1');
        }

        return $query;
    }

    public function getRecipientCountProperty(): int
    {
        return $this->recipients->count();
    }

    public function openPreview(): void
    {
        $this->validate([
            'subject' => 'required|string|min:3|max:200',
            'message' => 'required|string|min:10',
        ], [
            'subject.required' => 'El asunto es obligatorio.',
            'subject.min'      => 'El asunto debe tener al menos 3 caracteres.',
            'message.required' => 'El mensaje es obligatorio.',
            'message.min'      => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
    }

    public function send()
    {
        if ($this->isReadOnly()) return;

        $this->validate([
            'subject' => 'required|string|min:3|max:200',
            'message' => 'required|string|min:10',
        ], [
            'subject.required' => 'El asunto es obligatorio.',
            'subject.min'      => 'El asunto debe tener al menos 3 caracteres.',
            'message.required' => 'El mensaje es obligatorio.',
            'message.min'      => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        $users = $this->recipients->get(['id', 'name', 'email']);
        $count = $users->count();

        if ($count === 0) {
            $this->toastError('No hay destinatarios con los filtros seleccionados.');
            return;
        }

        $subject = $this->subject;
        $message = $this->message;

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new AdminBroadcastMail($subject, $message));
        }

        NotificationLog::create([
            'admin_id'          => Auth::id(),
            'subject'           => $subject,
            'message'           => $message,
            'audience_role'     => $this->audienceRole,
            'audience_verified' => $this->audienceVerified !== '' ? (bool) $this->audienceVerified : null,
            'audience_active'   => $this->audienceActive !== '' ? (bool) $this->audienceActive : null,
            'recipient_count'   => $count,
        ]);

        SecurityLogger::logSecurityEvent('admin_broadcast_sent', [
            'admin_id'        => Auth::id(),
            'recipient_count' => $count,
            'audience_role'   => $this->audienceRole,
            'subject'         => $subject,
        ]);

        $this->showPreviewModal = false;
        $this->reset(['subject', 'message']);
        $this->toastSuccess("{$count} email(s) en cola de envío.");
    }

    public function render()
    {
        $previewUsers = $this->recipients->limit(5)->get(['name', 'email']);
        $history      = NotificationLog::with('admin:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.admin.notifications.index', [
            'recipientCount' => $this->recipientCount,
            'previewUsers'   => $previewUsers,
            'history'        => $history,
        ])->layout('layouts.app', [
            'title'       => 'Notificaciones - Admin - Agro365',
            'description' => 'Envía comunicaciones a los usuarios del sistema',
        ]);
    }
}
