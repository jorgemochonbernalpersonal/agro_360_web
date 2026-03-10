<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;

class GrantBetaAccessOnVerification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Solo otorgar beta si aún no lo tiene (evita sobreescribir)
        if (!$user->isBetaUser()) {
            $user->grantBetaAccess();
        }
    }
}
