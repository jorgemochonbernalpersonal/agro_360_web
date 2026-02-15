<?php

namespace App\DataTransferObjects;

class ActivityFiltersData
{
    public function __construct(
        public readonly ?int $campaignId = null,
        public readonly ?int $plotId = null,
        public readonly ?string $activityType = null,
        public readonly ?string $search = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?int $productFilter = null
    ) {}

    /**
     * Crear desde request
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            campaignId: $data['campaign_id'] ?? null,
            plotId: $data['plot_id'] ?? null,
            activityType: $data['activity_type'] ?? null,
            search: $data['search'] ?? null,
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            productFilter: $data['product_filter'] ?? null
        );
    }

    /**
     * Convertir a array para query
     */
    public function toArray(): array
    {
        return array_filter([
            'campaign_id' => $this->campaignId,
            'plot_id' => $this->plotId,
            'activity_type' => $this->activityType,
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'product_filter' => $this->productFilter,
        ], fn($value) => !is_null($value));
    }

    /**
     * Verificar si hay filtros activos
     */
    public function hasActiveFilters(): bool
    {
        return $this->plotId !== null
            || $this->activityType !== null
            || $this->search !== null
            || $this->dateFrom !== null
            || $this->dateTo !== null
            || $this->productFilter !== null;
    }

    /**
     * Obtener descripción de filtros
     */
    public function getDescription(): string
    {
        $parts = [];

        if ($this->plotId) {
            $parts[] = "Parcela #{$this->plotId}";
        }

        if ($this->activityType) {
            $parts[] = "Tipo: {$this->activityType}";
        }

        if ($this->search) {
            $parts[] = "Búsqueda: {$this->search}";
        }

        if ($this->dateFrom || $this->dateTo) {
            $parts[] = "Periodo: " . ($this->dateFrom ?? '...') . " - " . ($this->dateTo ?? '...');
        }

        return empty($parts) ? 'Sin filtros' : implode(', ', $parts);
    }
}
