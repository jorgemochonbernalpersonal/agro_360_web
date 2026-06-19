<?php

namespace App\Livewire\Concerns;

use App\Models\SupervisorViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait WithGrowerLink
{
    public bool $showLinkModal = false;

    public string $linkQuery = '';

    public ?int $linkSelectedId = null;

    public function openLinkModal(): void
    {
        $this->reset(['linkQuery', 'linkSelectedId']);
        $this->resetErrorBag();
        $this->showLinkModal = true;
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
    }

    public function searchLinkCandidates(): array
    {
        $term = trim($this->linkQuery);
        if (strlen($term) < 2) {
            return [];
        }

        $doId = Auth::id();
        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)->pluck('viticulturist_id');
        $like = '%'.mb_strtolower($term).'%';

        return User::whereIn('role', [User::ROLE_VITICULTURIST, User::ROLE_PRODUCER])
            ->where('can_login', true)
            ->whereNotIn('id', $poolIds)
            ->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(dni,\'\')) LIKE ?', [$like]);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'dni'])
            ->toArray();
    }

    public function selectLinkCandidate(int $userId): void
    {
        $this->linkSelectedId = $userId;
    }

    public function linkExistingGrower(): void
    {
        $this->validate([
            'linkSelectedId' => ['required', 'integer'],
        ], [
            'linkSelectedId.required' => __('Selecciona un viticultor de los resultados.'),
        ]);

        $doId = Auth::id();

        $viticulturist = User::whereIn('role', [User::ROLE_VITICULTURIST, User::ROLE_PRODUCER])
            ->where('can_login', true)
            ->findOrFail($this->linkSelectedId);

        $alreadyInPool = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $viticulturist->id)
            ->exists();

        if ($alreadyInPool) {
            $this->toastError("{$viticulturist->name} ya pertenece al pool de la denominación.");

            return;
        }

        SupervisorViticulturist::create([
            'supervisor_id' => $doId,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $doId,
        ]);

        $this->showLinkModal = false;
        $this->toastSuccess("{$viticulturist->name} vinculado al pool de la denominación.");
    }
}
