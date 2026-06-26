<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Services\ViticulturistOnboardingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Invite extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public string $search = '';

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    public ?int $confirmingId = null;

    public function updatedSearch(): void
    {
        $this->confirmingId = null;

        if (mb_strlen(trim($this->search)) < 3) {
            $this->results = [];

            return;
        }

        $wineryId = Auth::id();
        $alreadyLinked = WineryViticulturist::where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        $term = '%'.mb_strtolower(trim($this->search)).'%';

        $this->results = User::where('role', 'viticulturist')
            ->where('can_login', true)
            ->whereNotIn('id', $alreadyLinked)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(dni, \'\')) LIKE ?', [$term]);
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'dni'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'dni' => $u->dni,
            ])
            ->toArray();
    }

    public function confirm(int $userId): void
    {
        $this->confirmingId = $userId;
    }

    public function cancelConfirmation(): void
    {
        $this->confirmingId = null;
    }

    public function link(int $userId): mixed
    {
        $wineryId = Auth::id();

        // Verificar si ya está vinculado a ESTA bodega
        $alreadyLinked = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $userId)
            ->exists();

        if ($alreadyLinked) {
            $this->toastError(__('Este viticultor ya está vinculado a tu bodega.'));
            $this->confirmingId = null;

            return null;
        }

        // Bloquear: viticultor no puede ser ghost de múltiples bodegas
        $alreadyLinkedOtherWinery = WineryViticulturist::where('viticulturist_id', $userId)
            ->where('winery_id', '!=', $wineryId)
            ->exists();

        if ($alreadyLinkedOtherWinery) {
            $this->toastError(__('Este viticultor ya está vinculado a otra bodega.'));
            $this->confirmingId = null;

            return null;
        }

        $user = User::where('role', 'viticulturist')
            ->where('can_login', true)
            ->findOrFail($userId);

        // Si existe un registro self-registered sin winery, actualizarlo en vez de duplicar
        $selfRecord = WineryViticulturist::where('viticulturist_id', $user->id)
            ->whereNull('winery_id')
            ->first();

        if ($selfRecord) {
            $selfRecord->update([
                'winery_id' => $wineryId,
                'source' => WineryViticulturist::SOURCE_OWN,
                'assigned_by' => $wineryId,
            ]);
        } else {
            WineryViticulturist::create([
                'winery_id' => $wineryId,
                'viticulturist_id' => $user->id,
                'source' => WineryViticulturist::SOURCE_OWN,
                'assigned_by' => $wineryId,
            ]);
        }

        app(ViticulturistOnboardingService::class)->inheritBeta(Auth::user(), $user);

        $this->toastSuccess("{$user->name} ha sido vinculado a tu bodega.");

        return $this->roleRedirect('viticulturists.index');
    }

    public function render()
    {
        return view('livewire.winery.viticulturists.invite')
            ->layout('layouts.app');
    }
}
