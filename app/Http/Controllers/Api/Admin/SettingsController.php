<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AppSetting;
use App\Services\SecurityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends BaseApiController
{
    /** Claves de configuración gestionadas por la app móvil admin. */
    private const MANAGED_KEYS = [
        'registration_open',
        'maintenance_mode',
        'support_email',
        'beta_end_date',
        'password_min_length',
        'require_strong_password',
        'module_silicie',
        'module_pac',
    ];

    // ─── GET /admin/settings ──────────────────────────────────────────────────

    public function show(Request $request): JsonResponse
    {
        $admin = $request->user();

        $settings = [];
        foreach (self::MANAGED_KEYS as $key) {
            $settings[$key] = AppSetting::get($key);
        }

        return $this->success($settings);
    }

    // ─── PUT /admin/settings ──────────────────────────────────────────────────

    public function update(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_if($admin->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $validated = $request->validate([
            'registration_open' => 'sometimes|boolean',
            'maintenance_mode' => 'sometimes|boolean',
            'support_email' => 'sometimes|email|max:255',
            'beta_end_date' => 'sometimes|nullable|date|after:today',
            'password_min_length' => 'sometimes|integer|min:6|max:32',
            'require_strong_password' => 'sometimes|boolean',
            'module_silicie' => 'sometimes|boolean',
            'module_pac' => 'sometimes|boolean',
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value === null ? '' : (string) $value);
        }

        SecurityLogger::logSecurityEvent('admin_settings_updated', [
            'admin_id' => $admin->id,
            'keys' => array_keys($validated),
        ]);

        // Devolver estado actualizado
        $settings = [];
        foreach (self::MANAGED_KEYS as $key) {
            $settings[$key] = AppSetting::get($key);
        }

        return $this->success($settings);
    }
}
