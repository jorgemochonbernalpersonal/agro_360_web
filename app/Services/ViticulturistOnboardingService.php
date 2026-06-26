<?php

namespace App\Services;

use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ViticulturistOnboardingService
{
    /**
     * Create a new ghost viticulturist under a winery, with optional beta inheritance
     * and invitation email if an address was provided.
     *
     * @param array{name: string, email: string, dni: string, phone: string, notes: string} $data
     */
    public function create(int $wineryId, User $actor, array $data): User
    {
        return DB::transaction(function () use ($wineryId, $actor, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?: ('viticultores.'.Str::uuid().'@noemail.agro365.es'),
                'dni' => $data['dni'] ?: null,
                'role' => 'viticulturist',
                'can_login' => false,
                'password' => Hash::make(Str::random(40)),
            ]);

            if ($data['phone']) {
                $user->profile()->create(['phone' => $data['phone']]);
            }

            WineryViticulturist::create([
                'winery_id' => $wineryId,
                'viticulturist_id' => $user->id,
                'source' => WineryViticulturist::SOURCE_OWN,
                'assigned_by' => $wineryId,
                'notes' => $data['notes'] ?: null,
            ]);

            $winery = User::find($wineryId);
            if ($winery) {
                $this->inheritBeta($winery, $user);
            }

            if ($data['email']) {
                $this->sendInvitation($user, $actor);
            }

            return $user;
        });
    }

    /**
     * Inherit the winery's beta access to a viticulturist if the winery is on active beta.
     * Called both on creation and when linking an existing viticulturist.
     */
    public function inheritBeta(User $winery, User $viticulturist): void
    {
        if ($winery->isBetaUser() && ! $winery->betaExpired() && ! $viticulturist->is_beta_user) {
            $viticulturist->grantBetaAccess(
                $winery->beta_ends_at ? Carbon::parse($winery->beta_ends_at) : null
            );
        }
    }

    private function sendInvitation(User $viticulturist, User $actor): void
    {
        $plainToken = Str::random(64);
        $viticulturist->update([
            'invitation_token' => hash('sha256', $plainToken),
            'invitation_sent_at' => now(),
            'invitation_expires_at' => now()->addDays(7),
        ]);
        $viticulturist->notify(new ViticulturistInvitationNotification($actor, $plainToken));
    }
}
