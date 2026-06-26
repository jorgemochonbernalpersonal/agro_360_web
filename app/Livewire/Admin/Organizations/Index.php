<?php

namespace App\Livewire\Admin\Organizations;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Organization;
use App\Models\Province;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithReadOnlyGuard, WithToastNotifications;

    // ── Filters ──────────────────────────────────────────────────────────────

    public string $search = '';

    public string $typeFilter = '';

    // ── Modal ─────────────────────────────────────────────────────────────────

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = Organization::TYPE_WINERY;

    public string $vat_number = '';

    public string $address = '';

    public string $city = '';

    public string $postal_code = '';

    public ?int $province_id = null;

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public bool $active = true;

    public ?int $owner_user_id = null;

    public ?int $parent_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => '', 'as' => 'type'],
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }
    // ── Modal helpers ─────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->reset(['name', 'vat_number', 'address', 'city', 'postal_code',
            'phone', 'email', 'website', 'owner_user_id', 'parent_id', 'province_id']);
        $this->type = Organization::TYPE_WINERY;
        $this->active = true;
        $this->editingId = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $org = Organization::findOrFail($id);
        $this->editingId = $org->id;
        $this->name = $org->name;
        $this->type = $org->type;
        $this->vat_number = $org->vat_number ?? '';
        $this->address = $org->address ?? '';
        $this->city = $org->city ?? '';
        $this->postal_code = $org->postal_code ?? '';
        $this->province_id = $org->province_id;
        $this->phone = $org->phone ?? '';
        $this->email = $org->email ?? '';
        $this->website = $org->website ?? '';
        $this->active = $org->active;
        $this->owner_user_id = $org->owner_user_id;
        $this->parent_id = $org->parent_id;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $validRoles = [User::ROLE_WINERY, User::ROLE_SUPERVISOR, User::ROLE_PRODUCER];

        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:winery,denomination_of_origin',
            'vat_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'owner_user_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($validRoles) {
                    if (! $value) {
                        return;
                    }
                    $owner = User::find($value);
                    if (! $owner) {
                        $fail(__('El usuario propietario no existe.'));

                        return;
                    }
                    if (! in_array($owner->role, $validRoles)) {
                        $fail(__('El propietario debe ser Bodega, Supervisor o Productor.'));
                    }
                },
            ],
        ], [
            'name.required' => __('El nombre es obligatorio.'),
            'type.required' => __('El tipo es obligatorio.'),
            'email.email' => __('El email no tiene un formato válido.'),
            'website.url' => __('La URL no tiene un formato válido (debe incluir https://).'),
        ]);

        $newOwnerId = $this->owner_user_id ?: null;

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'vat_number' => $this->vat_number ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postal_code ?: null,
            'province_id' => $this->province_id ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'active' => $this->active,
            'owner_user_id' => $newOwnerId,
            'parent_id' => $this->parent_id ?: null,
        ];

        if ($this->editingId) {
            $org = Organization::findOrFail($this->editingId);
            $prevOwnerId = $org->owner_user_id;
            $org->update($data);

            if ($org->wasChanged('name')) {
                $org->update(['slug' => Str::slug($this->name).'-'.$org->id]);
            }

            // Handle owner change: unlink previous, link new
            if ($prevOwnerId !== $newOwnerId) {
                if ($prevOwnerId) {
                    User::where('id', $prevOwnerId)
                        ->where('organization_id', $org->id)
                        ->update(['organization_id' => null]);
                }
                if ($newOwnerId) {
                    $newOwner = User::find($newOwnerId);
                    if ($newOwner && $newOwner->organization_id && $newOwner->organization_id !== $org->id) {
                        $this->toastWarning('El propietario ya pertenece a otra organización. El vínculo de organización no se ha actualizado.');
                    } else {
                        User::where('id', $newOwnerId)->update(['organization_id' => $org->id]);
                    }
                }
            }

            SecurityLogger::logSecurityEvent('organization_updated', [
                'admin_id' => Auth::id(),
                'organization_id' => $org->id,
                'org_name' => $org->name,
                'changes' => $org->getChanges(),
            ]);

            $this->toastSuccess(__('Organización actualizada correctamente.'));
        } else {
            $data['slug'] = $this->uniqueSlug($this->name);
            $org = Organization::create($data);

            if ($newOwnerId) {
                $owner = User::find($newOwnerId);
                if ($owner && $owner->organization_id) {
                    $this->toastWarning('El propietario ya pertenece a otra organización. El vínculo de organización no se ha actualizado.');
                } else {
                    User::where('id', $newOwnerId)->update(['organization_id' => $org->id]);
                }
            }

            SecurityLogger::logSecurityEvent('organization_created', [
                'admin_id' => Auth::id(),
                'organization_id' => $org->id,
                'org_name' => $org->name,
                'type' => $org->type,
            ]);

            $this->toastSuccess(__('Organización creada correctamente.'));
        }

        $this->closeModal();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $org = Organization::findOrFail($id);
        $orgName = $org->name;
        $memberCount = User::where('organization_id', $id)->count();

        // Unlink members before deleting
        User::where('organization_id', $id)->update(['organization_id' => null]);
        $org->delete();

        SecurityLogger::logSecurityEvent('organization_deleted', [
            'admin_id' => Auth::id(),
            'organization_id' => $id,
            'org_name' => $orgName,
            'members_unlinked' => $memberCount,
        ]);

        $this->toastSuccess(__('Organización eliminada.'));
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $baseQuery = Organization::query();

        $organizations = $baseQuery
            ->with(['ownerUser', 'province', 'parent'])
            ->withCount('members')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('vat_number', 'like', '%'.$this->search.'%')
                ->orWhere('city', 'like', '%'.$this->search.'%')
            ))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total' => Organization::count(),
            'wineries' => Organization::wineries()->count(),
            'denominations' => Organization::denominations()->count(),
            'orphans' => User::whereIn('role', [User::ROLE_WINERY, User::ROLE_SUPERVISOR, User::ROLE_PRODUCER])
                ->whereNull('organization_id')
                ->count(),
        ];

        $ownerUsers = User::whereIn('role', [User::ROLE_WINERY, User::ROLE_SUPERVISOR, User::ROLE_PRODUCER])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $denominations = Organization::denominations()->active()->orderBy('name')->get(['id', 'name']);
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.organizations.index',
            compact('organizations', 'stats', 'ownerUsers', 'denominations', 'provinces')
        )->layout('layouts.app', [
            'title' => __('Organizaciones - Agro365'),
            'description' => __('Gestiona las organizaciones del sistema (bodegas y DOs).'),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
