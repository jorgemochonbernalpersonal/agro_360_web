<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Winery\AbstractEdit;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public int $viticulturistId;

    public string $name = '';

    public string $dni = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    public function mount(User $viticulturist): void
    {
        $wineryId = Auth::id();

        $relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $viticulturist->id)
            ->where('source', WineryViticulturist::SOURCE_OWN)
            ->firstOrFail();

        $user = $viticulturist;

        $this->viticulturistId = $user->id;
        $this->name = $user->name;
        $this->dni = $user->dni ?? '';
        $rawEmail = $user->email ?? '';
        $this->email = str_starts_with($rawEmail, 'viticultores.') ? '' : $rawEmail;
        $this->phone = $user->profile?->phone ?? '';
        $this->notes = $relation->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', "unique:users,dni,{$this->viticulturistId}"],
            'email' => ['nullable', 'email', 'max:255', "unique:users,email,{$this->viticulturistId}"],
            'phone' => ['nullable', 'string', 'max:20'],
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

    protected function performUpdate(): void
    {
        $wineryId = Auth::id();
        $user = User::findOrFail($this->viticulturistId);

        $emailValue = $this->email ?: null;

        // If clearing email, keep the existing placeholder (NOT NULL constraint)
        if ($emailValue === null && str_starts_with($user->email ?? '', 'viticultores.')) {
            $emailValue = $user->email;
        } elseif ($emailValue === null) {
            $emailValue = 'viticultores.'.\Illuminate\Support\Str::uuid().'@noemail.agro365.es';
        }

        $user->update([
            'name' => $this->name,
            'dni' => $this->dni ?: null,
            'email' => $emailValue,
        ]);

        if ($this->phone) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['phone' => $this->phone]
            );
        } elseif ($user->profile) {
            $user->profile()->update(['phone' => null]);
        }

        WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $user->id)
            ->update(['notes' => $this->notes ?: null]);
    }

    protected function successMessage(): string
    {
        return __('Datos del viticultor actualizados.');
    }

    protected function indexRoute(): string
    {
        return 'winery.viticulturists.index';
    }

    protected function viewData(): array
    {
        return ['viticulturistId' => $this->viticulturistId];
    }
}
