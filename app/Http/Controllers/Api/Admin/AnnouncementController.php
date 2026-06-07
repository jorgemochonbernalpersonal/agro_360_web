<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\Admin\AdminAnnouncementResource;
use App\Models\AdminAnnouncement;
use App\Services\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends BaseApiController
{
    // ─── GET /admin/announcements ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin(), 403);

        $perPage = $this->resolvePerPage($request, 30, 100);

        $query = AdminAnnouncement::with('admin:id,name')->latest();

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $items = $query->paginate($perPage);

        return $this->paginated($items, AdminAnnouncementResource::collection($items));
    }

    // ─── POST /admin/announcements ────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin(), 403);
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:1000',
            'type' => ['required', Rule::in(['info', 'warning', 'success', 'danger'])],
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $announcement = AdminAnnouncement::create(array_merge($validated, [
            'admin_id' => $admin->id,
        ]));

        SecurityLogger::logSecurityEvent('admin_announcement_created', [
            'admin_id' => $admin->id,
            'announcement_id' => $announcement->id,
        ]);

        return $this->created(new AdminAnnouncementResource($announcement->load('admin:id,name')));
    }

    // ─── PUT /admin/announcements/{id} ────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin(), 403);
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $announcement = AdminAnnouncement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:120',
            'message' => 'sometimes|string|max:1000',
            'type' => ['sometimes', Rule::in(['info', 'warning', 'success', 'danger'])],
            'is_active' => 'sometimes|boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $announcement->update($validated);

        return $this->success(new AdminAnnouncementResource($announcement->fresh()->load('admin:id,name')));
    }

    // ─── DELETE /admin/announcements/{id} ────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin(), 403);
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $announcement = AdminAnnouncement::findOrFail($id);
        $announcement->delete();

        SecurityLogger::logSecurityEvent('admin_announcement_deleted', [
            'admin_id' => $admin->id,
            'announcement_id' => $id,
        ]);

        return response()->json(null, 204);
    }
}
