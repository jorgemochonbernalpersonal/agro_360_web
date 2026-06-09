<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\NotebookAccessRequest;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ViticulturistController extends BaseApiController
{
    // ─── GET /winery/viticulturists ──────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $query = WineryViticulturist::where('winery_id', $winery->id)
            ->with('viticulturist');

        // ?with_active_plantings=1 — only viticulturists that have active plots with active plantings
        if ($request->boolean('with_active_plantings')) {
            $query->whereHas('viticulturist.plots', fn ($q) => $q->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
            );
        }

        // ?notebook_access=1 — only viticulturists with notebook access (valid for campaign creation)
        if ($request->boolean('notebook_access')) {
            $query->where('notebook_access', true);
        }

        $relations = $query->get();

        $data = $relations
            ->filter(fn ($r) => $r->viticulturist !== null)
            ->map(fn ($r) => $this->format($r->viticulturist, $r))
            ->sortBy('name')
            ->values();

        return $this->success($data);
    }

    // ─── POST /winery/viticulturists ─────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => __('El nombre es obligatorio.'),
            'email.unique' => __('Ya existe un usuario con este email.'),
        ]);

        $vit = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? ('viticultores.'.Str::uuid().'@noemail.agro365.es'),
            'role' => 'viticulturist',
            'can_login' => false,
            'password' => Hash::make(Str::random(40)),
        ]);

        $rel = WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $vit->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
            'notes' => $validated['notes'] ?? null,
            'notebook_access' => true,
            'notebook_granted_at' => now(),
        ]);

        return $this->created($this->format($vit, $rel));
    }

    // ─── GET /winery/viticulturists/search ───────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $request->validate(['q' => ['required', 'string', 'min:3', 'max:100']]);

        $alreadyLinked = WineryViticulturist::where('winery_id', $winery->id)
            ->pluck('viticulturist_id');

        $term = '%'.mb_strtolower(trim($request->q)).'%';

        $results = User::where('role', 'viticulturist')
            ->where('can_login', true)
            ->whereNotIn('id', $alreadyLinked)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            })
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]);

        return $this->success($results);
    }

    // ─── POST /winery/viticulturists/link ────────────────────────────────────

    public function link(Request $request): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $validated = $request->validate([
            'viticulturist_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $vit = User::where('role', 'viticulturist')
            ->where('can_login', true)
            ->findOrFail($validated['viticulturist_id']);

        if (WineryViticulturist::where('winery_id', $winery->id)->where('viticulturist_id', $vit->id)->exists()) {
            return response()->json(['message' => __('Este viticultor ya está vinculado a tu bodega.')], 422);
        }

        $selfRecord = WineryViticulturist::where('viticulturist_id', $vit->id)
            ->whereNull('winery_id')
            ->first();

        if ($selfRecord) {
            $rel = $selfRecord;
            $selfRecord->update([
                'winery_id'   => $winery->id,
                'source'      => WineryViticulturist::SOURCE_OWN,
                'assigned_by' => $winery->id,
            ]);
        } else {
            $rel = WineryViticulturist::create([
                'winery_id'        => $winery->id,
                'viticulturist_id' => $vit->id,
                'source'           => WineryViticulturist::SOURCE_OWN,
                'assigned_by'      => $winery->id,
                'notebook_access'  => false,
            ]);
        }

        // notebook_access requires the viticulturist's explicit consent — create a pending request
        $alreadyPending = NotebookAccessRequest::where('winery_id', $winery->id)
            ->where('viticulturist_id', $vit->id)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->exists();

        if (! $alreadyPending) {
            NotebookAccessRequest::create([
                'winery_id'        => $winery->id,
                'viticulturist_id' => $vit->id,
                'status'           => NotebookAccessRequest::STATUS_PENDING,
                'requested_at'     => now(),
            ]);
        }

        $rel->refresh();

        return response()->json([
            'message' => "{$vit->name} ha sido vinculado a tu bodega.",
            'data' => $this->format($vit, $rel),
        ], 201);
    }

    // ─── GET /winery/viticulturists/{id} ─────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $rel = WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $id)
            ->with('viticulturist')
            ->firstOrFail();

        $vit = $rel->viticulturist;
        $data = $this->format($vit, $rel);

        $batchIds = GrapeReceptionBatch::where('winery_id', $winery->id)
            ->where('viticulturist_id', $id)
            ->pluck('id');

        $stats = Harvest::whereIn('batch_id', $batchIds)
            ->selectRaw('COUNT(*) as total_receptions, COALESCE(SUM(total_weight), 0) as total_kg, MAX(harvest_start_date) as last_reception_date')
            ->first();

        $data['stats'] = [
            'total_receptions' => (int) ($stats->total_receptions ?? 0),
            'total_kg' => round((float) ($stats->total_kg ?? 0), 2),
            'last_reception_date' => $stats->last_reception_date,
        ];

        return $this->success($data);
    }

    // ─── PUT /winery/viticulturists/{id} ─────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $rel = WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $id)
            ->where('source', WineryViticulturist::SOURCE_OWN)
            ->firstOrFail();

        $vit = $rel->viticulturist;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => __('El nombre es obligatorio.'),
            'email.unique' => __('Ya existe un usuario con este email.'),
        ]);

        $rawEmail = $vit->email ?? '';
        $newEmail = $validated['email'] ?? null;
        if ($newEmail === null) {
            $newEmail = $rawEmail; // preserve placeholder (NOT NULL)
        }

        $vit->update(['name' => $validated['name'], 'email' => $newEmail]);
        $rel->update(['notes' => $validated['notes'] ?? null]);
        $vit->refresh();

        return $this->success($this->format($vit, $rel));
    }

    // ─── POST /winery/viticulturists/{id}/invite ─────────────────────────────

    public function invite(Request $request, int $id): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $rel = WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $id)
            ->with('viticulturist')
            ->firstOrFail();

        $vit = $rel->viticulturist;

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => __('Introduce el email del viticultor.'),
            'email.email' => __('El email no es válido.'),
        ]);

        if ($vit->invitation_sent_at && $vit->invitation_sent_at->isAfter(now()->subHour())) {
            return response()->json([
                'message' => __('Invitación enviada hace menos de 1 hora. Espera antes de reenviar.'),
            ], 429);
        }

        if (User::where('email', $validated['email'])->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => __('Este email ya está registrado en el sistema.')], 422);
        }

        $plainToken = Str::random(64);
        $updates = [
            'invitation_token' => hash('sha256', $plainToken),
            'invitation_sent_at' => now(),
            'invitation_expires_at' => now()->addDays(7),
        ];

        if (str_starts_with($vit->email ?? '', 'viticultores.')) {
            $updates['email'] = $validated['email'];
        }

        $vit->update($updates);
        $vit->refresh();

        $vit->notify(new ViticulturistInvitationNotification($winery, $plainToken));

        return response()->json([
            'message' => "Invitación enviada a {$validated['email']}.",
            'data' => $this->format($vit, $rel),
        ]);
    }

    // ─── DELETE /winery/viticulturists/{id}/invite ───────────────────────────

    public function revokeInvite(Request $request, int $id): JsonResponse
    {
        $winery = $request->user();
        abort_unless($winery->hasWineryAccess(), 403);

        $rel = WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $id)
            ->with('viticulturist')
            ->firstOrFail();

        $vit = $rel->viticulturist;

        $vit->update([
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'invitation_sent_at' => null,
        ]);
        $vit->refresh();

        return response()->json([
            'message' => __('Invitación revocada.'),
            'data' => $this->format($vit, $rel),
        ]);
    }
    // ─── Helper ───────────────────────────────────────────────────────────────

    private function format(User $vit, WineryViticulturist $rel): array
    {
        $rawEmail = $vit->email ?? '';
        $displayEmail = str_starts_with($rawEmail, 'viticultores.') ? null : $rawEmail;

        $hasPending = $vit->invitation_token !== null
            && ($vit->invitation_expires_at === null || $vit->invitation_expires_at->isFuture());

        return [
            'id' => $vit->id,
            'name' => $vit->name,
            'email' => $displayEmail,
            'can_login' => (bool) $vit->can_login,
            'source' => $rel->source,
            'notes' => $rel->notes,
            'notebook_access' => (bool) $rel->notebook_access,
            'has_pending_invitation' => $hasPending,
            'invitation_sent_at' => $vit->invitation_sent_at?->toIso8601String(),
            'invitation_expires_at' => $vit->invitation_expires_at?->toIso8601String(),
        ];
    }
}
