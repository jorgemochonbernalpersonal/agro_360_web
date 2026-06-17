<?php

use App\Models\Ability;

/*
 * Catálogo de planes y sus abilities.
 *
 * Un plan = lista de ability codes.
 * El prefijo '@nombre' hereda todas las abilities de ese plan.
 * Fuente de verdad del motor de gating — price.md §5–6.
 */
return [

    // ── Free (permanente, todos los roles) ────────────────────────────────────
    // Operación completa: cumplimiento 2027 + gestión diaria. Sin compliance premium.
    'free' => [
        Ability::DIGITAL_NOTEBOOK,
        Ability::SIGPAC_PLOTS,
        Ability::MAPS,
        Ability::PLANTINGS,
        Ability::PHENOLOGY,
        Ability::PEST_MANAGEMENT,
        Ability::HARVESTS_VIEW,
        Ability::CELLAR_MANAGEMENT,   // depósitos — free
        Ability::HARVEST_RECEPTION,   // vendimias — free
        Ability::WINE_PROCESS,        // elaboración — free
        Ability::INVOICING,           // facturación sin sello — free
    ],

    // ── Viticultor Pro / Productor (Capa A: Compliance + Inteligencia) ────────
    // 9€/mes (invitado) · 14€/mes (independiente) · 19€/mes (productor)
    'vit_pro' => [
        '@free',
        Ability::REMOTE_SENSING,
        Ability::VERIFAKTU,
        Ability::PAC,
        Ability::HARVEST_OPERATIONS,
        Ability::FINANCIAL_STATS,
        Ability::ROPO_APPLICATORS,
        Ability::NOTEBOOK_ACCESS,
    ],

    // ── Bodega (Capa A + Capa B: Red) ────────────────────────────────────────
    // 19€/mes (0 vit.) · 29€ (1-10) · 49€ (11-30) · 79€ (31-60) · 119€ (61-100)
    'winery' => [
        '@vit_pro',
        Ability::YIELD_FORECASTS,
        Ability::QUALITY_ANALYSIS,
        Ability::PRODUCT_SALES,
        Ability::GRAPE_PURCHASE_INV,
        Ability::LABEL_BATCHES,
        Ability::VITICULTURIST_PANEL,
        Ability::CONSOLIDATED_REPORTS,
    ],

    // ── Denominación de Origen (Capa B supervisión) ───────────────────────────
    // desde 149€/mes escalado por nº de bodegas adscritas
    'do' => [
        Ability::DO_OVERSIGHT,
        Ability::VITICULTURIST_PANEL,
        Ability::CONSOLIDATED_REPORTS,
    ],

];
