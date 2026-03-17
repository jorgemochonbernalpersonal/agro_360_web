<?php

namespace App\Listeners;

use App\Models\WineryViticulturist;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class EnsureViticulturistSelfRecord
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user->hasViticulturistAccess()) {
            return;
        }

        $exists = WineryViticulturist::where('viticulturist_id', $user->id)->exists();

        if ($exists) {
            return;
        }

        WineryViticulturist::create([
            'winery_id'               => null,
            'viticulturist_id'        => $user->id,
            'source'                  => WineryViticulturist::SOURCE_SELF,
            'parent_viticulturist_id' => null,
            'assigned_by'             => $user->id,
        ]);

        Log::info('Auto-registered viticulturist self record on login', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }
}
