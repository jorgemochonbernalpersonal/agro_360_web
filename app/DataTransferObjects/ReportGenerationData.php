<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class ReportGenerationData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $reportType,
        public readonly Carbon $periodStart,
        public readonly Carbon $periodEnd,
        public readonly string $password,
        public readonly ?int $campaignId = null,
        public readonly ?array $metadata = null
    ) {}

    /**
     * Crear para informe fitosanitario
     */
    public static function forPhytosanitary(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        string $password
    ): self {
        return new self(
            userId: $userId,
            reportType: 'phytosanitary_treatments',
            periodStart: $startDate,
            periodEnd: $endDate,
            password: $password
        );
    }

    /**
     * Crear para cuaderno completo
     */
    public static function forFullNotebook(
        int $userId,
        int $campaignId,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $password
    ): self {
        return new self(
            userId: $userId,
            reportType: 'full_digital_notebook',
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            password: $password,
            campaignId: $campaignId
        );
    }

    /**
     * Verificar si es informe fitosanitario
     */
    public function isPhytosanitary(): bool
    {
        return $this->reportType === 'phytosanitary_treatments';
    }

    /**
     * Verificar si es cuaderno completo
     */
    public function isFullNotebook(): bool
    {
        return $this->reportType === 'full_digital_notebook';
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'report_type' => $this->reportType,
            'period_start' => $this->periodStart->toDateString(),
            'period_end' => $this->periodEnd->toDateString(),
            'campaign_id' => $this->campaignId,
            'metadata' => $this->metadata,
        ];
    }
}
