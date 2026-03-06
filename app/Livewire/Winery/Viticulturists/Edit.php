<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Winery\AbstractEdit;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public int $viticulturistId;

    public string $name  = '';
    public string $dni   = '';
    public string $email = '';
    public string $phone = '';
    public string $notes = '';

    public function mount(int $viticulturist): void
    {
        $wineryId = Auth::id();

        $relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $viticulturist)
            ->where('source', WineryViticulturist::SOURCE_OWN)
            ->firstOrFail();

        $user = User::findOrFail($relation->viticulturist_id);

        $this->viticulturistId = $user->id;
        $this->name            = $user->name;
        $this->dni             = $user->dni ?? '';
        $this->email           = $user->email ?? '';
        $this->phone           = $user->profile?->phone ?? '';
    }

    protected function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'dni'   => ['nullable', 'string', 'max:20', "unique:users,dni,{$this->viticulturistId}"],
            'email' => ['nullable', 'email', 'max:255', "unique:users,email,{$this->viticulturistId}"],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'dni.unique'    => 'Ya existe un usuario con este DNI.',
            'email.unique'  => 'Ya existe un usuario con este email.',
        ];
    }

    protected function performUpdate(): void
    {
        $user = User::findOrFail($this->viticulturistId);

        $user->update([
            'name'  => $this->name,
            'dni'   => $this->dni  ?: null,
            'email' => $this->email ?: null,
        ]);

        if ($this->phone) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['phone'   => $this->phone]
            );
        } elseif ($user->profile) {
            $user->profile()->update(['phone' => null]);
        }
    }

    protected function successMessage(): string
    {
        return 'Datos del viticultor actualizados.';
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
