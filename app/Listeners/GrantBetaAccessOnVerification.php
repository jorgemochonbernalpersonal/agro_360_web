<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\WelcomeToAgro365;
use Illuminate\Auth\Events\Verified;

class GrantBetaAccessOnVerification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        // Solo otorgar beta si aún no lo tiene (evita sobreescribir)
        if (! $user->isBetaUser()) {
            $user->grantBetaAccess();
        }

        // Enviar email de bienvenida tras verificación
        $user->notify(new WelcomeToAgro365);
    }
}
