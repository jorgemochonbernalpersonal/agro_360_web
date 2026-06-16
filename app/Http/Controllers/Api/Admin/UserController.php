<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\Admin\AdminUserResource;
use App\Models\AdminNote;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends BaseApiController
{
    // ─── GET /admin/users ─────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        $perPage = $this->resolvePerPage($request, 30, 100);

        $query = User::with('profile')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('can_login')) {
            $query->where('can_login', $request->boolean('can_login'));
        }

        if ($request->filled('verified')) {
            $verified = $request->boolean('verified');
            $verified
                ? $query->whereNotNull('email_verified_at')
                : $query->whereNull('email_verified_at');
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($perPage);

        return $this->paginated($items, AdminUserResource::collection($items));
    }

    // ─── GET /admin/users/pending ─────────────────────────────────────────────

    public function pending(Request $request): JsonResponse
    {
        $admin = $request->user();

        $perPage = $this->resolvePerPage($request, 30, 100);

        $items = User::with('profile')
            ->where('can_login', false)
            ->where('role', '!=', User::ROLE_ADMIN)
            ->latest()
            ->paginate($perPage);

        return $this->paginated($items, AdminUserResource::collection($items));
    }

    // ─── GET /admin/users/{id} ────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        $user = User::with('profile')->findOrFail($id);

        $notes = AdminNote::where('user_id', $id)
            ->with('admin:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => new AdminUserResource($user),
            'notes' => $notes->map(fn ($note) => [
                'id' => $note->id,
                'note' => $note->note,
                'admin_name' => $note->admin?->name,
                'created_at' => $note->created_at->toIso8601String(),
            ])->values(),
        ]);
    }

    // ─── PUT /admin/users/{id} ────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $user = User::findOrFail($id);

        // No puede editar su propio rol ni a otros admins (sin ser superadmin)
        if ($user->isAdmin() && $user->id !== $admin->id) {
            abort(403, __('No puedes editar a otro administrador.'));
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes', 'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['sometimes', Rule::in([
                User::ROLE_ADMIN,
                User::ROLE_SUPERVISOR,
                User::ROLE_WINERY,
                User::ROLE_VITICULTURIST,
                User::ROLE_PRODUCER,
            ])],
        ]);

        // Si cambia email, revocar verificación
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        SecurityLogger::logSecurityEvent('admin_user_updated', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'fields' => array_keys($validated),
        ]);

        return $this->success(new AdminUserResource($user->fresh('profile')));
    }

    // ─── POST /admin/users/{id}/approve ──────────────────────────────────────

    public function approve(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $user = User::findOrFail($id);

        $user->update([
            'can_login' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        SecurityLogger::logSecurityEvent('admin_user_approved', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $this->success(new AdminUserResource($user->fresh('profile')));
    }

    // ─── POST /admin/users/{id}/activate ─────────────────────────────────────

    public function activate(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $user = User::findOrFail($id);
        $user->update(['can_login' => true]);

        SecurityLogger::logSecurityEvent('admin_user_activated', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $this->success(new AdminUserResource($user->fresh('profile')));
    }

    // ─── POST /admin/users/{id}/deactivate ───────────────────────────────────

    public function deactivate(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $user = User::findOrFail($id);

        if ($user->id === $admin->id) {
            abort(422, __('No puedes desactivarte a ti mismo.'));
        }

        $user->update(['can_login' => false]);
        // Revocar todos los tokens activos de la sesión
        $user->tokens()->delete();

        SecurityLogger::logSecurityEvent('admin_user_deactivated', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $this->success(new AdminUserResource($user->fresh('profile')));
    }

    // ─── POST /admin/users/{id}/notes ─────────────────────────────────────────

    public function addNote(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        User::findOrFail($id);

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $note = AdminNote::create([
            'user_id' => $id,
            'admin_id' => $admin->id,
            'note' => $validated['note'],
        ]);

        return $this->created([
            'id' => $note->id,
            'note' => $note->note,
            'admin_name' => $admin->name,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
