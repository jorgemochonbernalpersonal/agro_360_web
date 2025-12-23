<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SignaturePasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetData;

    /**
     * Crear nueva instancia del mensaje
     */
    public function __construct(User $user, array $resetData)
    {
        $this->user = $user;
        $this->resetData = $resetData;
    }

    /**
     * Construir el mensaje
     */
    public function build()
    {
        return $this
            ->subject('🔐 Tu contraseña de firma digital ha sido reseteada - Agro365')
            ->markdown('emails.signature-password-reset')
            ->with([
                'userName' => $this->user->name,
                'resetDate' => $this->resetData['reset_at'],
                'ipAddress' => $this->resetData['ip_address'],
                'browser' => $this->resetData['browser'],
                'device' => $this->resetData['device'],
            ]);
    }
}
