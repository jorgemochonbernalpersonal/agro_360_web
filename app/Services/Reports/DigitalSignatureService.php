<?php

namespace App\Services\Reports;

use App\Models\OfficialReport;
use Illuminate\Support\Facades\Log;

class DigitalSignatureService
{
    /**
     * Generar firma digital para un informe
     */
    public function generateSignature(array $signatureData): array
    {
        return OfficialReport::generateSignatureHash($signatureData);
    }

    /**
     * Preparar datos para firma
     */
    public function prepareSignatureData(
        string $reportType,
        int $userId,
        array $dateRange,
        array $itemIds,
        array $stats,
        string $pdfHash
    ): array {
        return [
            'type' => $reportType,
            'user_id' => $userId,
            'period_start' => $dateRange['start']->toDateString(),
            'period_end' => $dateRange['end']->toDateString(),
            'item_ids' => $itemIds,
            'stats' => $stats,
            'pdf_hash' => $pdfHash,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Preparar metadatos de firma
     */
    public function prepareSignatureMetadata(
        \App\Models\User $user,
        string $pdfHash,
        array $signatureResult
    ): array {
        $deviceInfo = $this->getDeviceInfo();

        return [
            'user_agent' => request()->userAgent(),
            'password_verified' => true,
            'signed_by_name' => $user->name,
            'signed_by_email' => $user->email,
            'device_info' => $deviceInfo,
            'timestamp_authority' => 'Agro365 Internal TSA',
            'timestamp_format' => 'ISO8601',
            'timezone' => config('app.timezone'),
            'signature_algorithm' => 'SHA-256',
            'signature_version' => $signatureResult['version'],
            'nonce' => $signatureResult['nonce'],
            'pdf_hash' => $pdfHash,
        ];
    }

    /**
     * Obtener información del dispositivo
     */
    protected function getDeviceInfo(): array
    {
        $userAgent = request()->userAgent();

        // Detectar navegador
        $browser = __('Unknown');
        if (str_contains($userAgent, 'Chrome')) $browser = __('Chrome');
        elseif (str_contains($userAgent, 'Firefox')) $browser = __('Firefox');
        elseif (str_contains($userAgent, 'Safari')) $browser = __('Safari');
        elseif (str_contains($userAgent, 'Edge')) $browser = __('Edge');

        // Detectar SO
        $os = __('Unknown');
        if (str_contains($userAgent, 'Windows')) $os = __('Windows');
        elseif (str_contains($userAgent, 'Mac')) $os = 'macOS';
        elseif (str_contains($userAgent, 'Linux')) $os = __('Linux');
        elseif (str_contains($userAgent, 'Android')) $os = __('Android');
        elseif (str_contains($userAgent, 'iOS')) $os = 'iOS';

        return [
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $userAgent,
        ];
    }
}
