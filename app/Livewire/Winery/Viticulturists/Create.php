<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Winery\AbstractCreate;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $dni = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->where('can_login', true)],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio.'),
            'dni.unique' => __('Ya existe un usuario con este DNI.'),
            'email.unique' => __('Ya existe un usuario con este email.'),
        ];
    }

    protected function performCreate(): void
    {
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email ?: ('viticultores.'.Str::uuid().'@noemail.agro365.es'),
            'dni' => $this->dni ?: null,
            'role' => 'viticulturist',
            'can_login' => false,
            'password' => Hash::make(Str::random(40)),
        ]);

        if ($this->phone) {
            $user->profile()->create(['phone' => $this->phone]);
        }

        WineryViticulturist::create([
            'winery_id' => $this->wineryId(),
            'viticulturist_id' => $user->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->wineryId(),
            'notes' => $this->notes ?: null,
        ]);

        // Inherit beta from winery if active (same end date)
        $winery = User::find($this->wineryId());
        if ($winery?->isBetaUser() && ! $winery->betaExpired() && ! $user->is_beta_user) {
            $user->grantBetaAccess($winery->beta_ends_at ? Carbon::parse($winery->beta_ends_at) : null);
        }

        // Auto-send invitation if a real email was provided
        if ($this->email) {
            $plainToken = Str::random(64);
            $user->update([
                'invitation_token' => hash('sha256', $plainToken),
                'invitation_sent_at' => now(),
                'invitation_expires_at' => now()->addDays(7),
            ]);
            $user->notify(new ViticulturistInvitationNotification(Auth::user(), $plainToken));
        }
    }

    protected function successMessage(): string
    {
        return $this->email
            ? __('Viticultor creado e invitación enviada correctamente.')
            : __('Viticultor creado. Recuerda enviarle una invitación desde su perfil cuando tengas su email.');
    }

    protected function indexRoute(): string
    {
        return 'winery.viticulturists.index';
    }
}
