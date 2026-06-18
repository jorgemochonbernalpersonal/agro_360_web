# Patrón oficial: vista de listado (index)

> Plantilla canónica para todas las vistas de listado de Agro365.
> **Referencia viva:** `resources/views/livewire/plots/index.blade.php`.
> Cuando dudes de cómo montar un listado, copia de Plots.

## Principios

1. **Una sola convención de nombres:** siempre notación punto → `x-agro.NAME`
   (nunca `x-agro-name` con guion; el alias de guion fue eliminado).
2. **No se crean componentes nuevos para esto:** todas las piezas ya existen
   bajo `resources/views/components/agro/`. Unificar = usarlas igual, en el
   mismo orden, en los mismos sitios.
3. **Consistencia por composición:** la estructura (orden, espaciados, loading,
   paginación, estado vacío) debe ser idéntica entre vistas.

## Esqueleto (orden fijo)

| # | Bloque | Componente | ¿Cuándo? |
|---|--------|-----------|----------|
| 1 | Cabecera | `x-agro.page-header` | **Siempre** (título + acción primaria) |
| 2 | Métricas | `x-agro.stats-section` + `x-agro.stat-card` | Si el recurso tiene agregados con sentido (totales, sumas) |
| 3 | Segmentación | `x-agro.tabs` | Si hay estados naturales (activas/inactivas, por fase) |
| 4 | Toolbar | `x-agro.search-input` + `x-agro.filter-button` | search si la lista es grande/buscable; filter-button si hay filtros avanzados |
| 5 | Filtros activos | `x-agro.filter-chip` | Si hay filtros aplicados (fila bajo el toolbar) |
| 6 | Loading | `x-agro.loading-grid` (preferido) **ó** `wire:loading` inline | Feedback de carga **siempre** que el contenido sea un grid reactivo (wire). `loading-grid` es el patrón de Plots; `wire:loading` inline es alternativa válida |
| 7 | Contenido | grid de `x-agro.card` **ó** `x-agro.data-table` | **Siempre** — cards para densidad media, tabla para densidad alta |
| 8 | Paginación | `x-agro.pagination` | **Siempre** que esté paginado |
| 9 | Estado vacío | `x-agro.empty-state` | **Siempre**. Nota: `x-agro.data-table` ya lo incluye internamente (props `empty-message`/`empty-description`); `@forelse/@empty` también es válido |
| 10 | Modal filtros | `x-agro.modal name="<recurso>-filters"` | Si existe filter-button |

Obligatorios mínimos en **cualquier** listado: **1, 7, 9** (header + contenido + empty-state),
y **8** si pagina. El resto se incluye *cuando aplica*, pero siempre en este orden.

## Esqueleto copiable

```blade
<div class="space-y-6 animate-fade-in">

    {{-- 1. Header --}}
    <x-agro.page-header
        :title="__('Gestión de Recursos')"
        :description="__('Descripción del recurso')"
    />

    {{-- 2. Stats (si hay métricas) --}}
    <x-agro.stats-section key="recurso">
        <x-agro.stat-card :label="__('Total')" :value="$stats['total']" icon="..." color="agro" />
        {{-- ...hasta 4 stat-card --}}
    </x-agro.stats-section>

    {{-- 3. Tabs (si hay estados) --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => __('Activas'),   'count' => $stats['active']],
        'inactive' => ['label' => __('Inactivas'), 'count' => $stats['inactive']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- 4. Toolbar: search + filtros + acción primaria --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar...')" />
        <x-agro.filter-button modal="recurso-filters" :count="$filterCount" />
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>
        @can('create', \App\Models\Recurso::class)
            <flux:button href="{{ roleRoute('recurso.create') }}" variant="primary" icon="plus">
                {{ __('Nueva') }}
            </flux:button>
        @endcan
    </div>

    {{-- 5. Chips de filtros activos (si hay filtros aplicados) --}}
    @if ($filtroX)
        <div class="flex flex-wrap items-center gap-2">
            <x-agro.filter-chip :label="$filtroX" wire:click="$set('filtroX', '')" />
        </div>
    @endif

    {{-- 6. Loading --}}
    <x-agro.loading-grid target="switchTab, search, nextPage, previousPage, gotoPage" />

    {{-- 7. Contenido + 8. Paginación + 9. Empty-state --}}
    @if ($items->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($items as $item)
                <x-agro.card>
                    <x-slot:header>
                        {{-- título + x-agro.status-badge --}}
                    </x-slot:header>

                    {{-- cuerpo: campos del item --}}

                    <x-slot:footer>
                        {{-- x-agro.action-button: view / edit / activate / deactivate ... --}}
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$items" />
    @else
        <x-agro.empty-state
            :title="__('No hay registros')"
            :description="__('...')"
        >
            <x-slot name="action">
                {{-- acción para crear el primero --}}
            </x-slot>
        </x-agro.empty-state>
    @endif

    {{-- 10. Modal de filtros (si hay filter-button) --}}
    <x-agro.modal name="recurso-filters" maxWidth="sm">
        {{-- selects/inputs de filtro con wire:model.live --}}
    </x-agro.modal>

</div>
```

## Checklist por vista

```
□ page-header con título + acción primaria
□ stats-section ANTES del contenido (si hay métricas)
□ tabs (si hay estados)
□ toolbar: search-input + filter-button (en este orden), si aplica
□ filter-chip para filtros activos
□ feedback de carga: loading-grid (preferido) o wire:loading inline
□ grid de card  Ó  data-table  (no ambos)
□ empty handling: empty-state / data-table / @forelse — alguno SIEMPRE
□ x-agro.pagination  (notación punto, nunca guion)
□ modal "<recurso>-filters" si hay filter-button
```

> **Nota de auditoría (2026-05-31):** los listados existentes ya cubren
> empty/loading/pagination en su mayoría, pero con patrones distintos para el
> mismo concepto. No conviene reescribir vistas que funcionan solo para
> homogeneizar el mecanismo (churn de alto riesgo y bajo valor). Aplica este
> patrón **a vistas nuevas** y a las genuinamente incompletas (sin ningún
> empty/loading), no como reescritura masiva.

## Estado de adopción (2026-05-31)

145 vistas `index.blade.php`. `page-header`/`card`/`empty-state`/`pagination`
casi universales; lo inconsistente es la composición (stats ~70%, search ~35%,
filtros ~45%, tabs ~20%). ~15 vistas con el patrón completo (plots, clients,
machinery, warehouse, harvest-byproducts, phytosanitary-alerts, planned-works,
container-returns, winery/clients, etc.).

**Migración por fases** (ver memoria `project-patron-vistas`):
- F1 ✅ notación unificada a punto + alias de guion eliminado.
- F1 (resto): añadir `empty-state`/`loading-grid` donde falten.
- F2 ✅ auditar conformidad por vista (2026-06-18): 148 vistas auditadas. Todos
  los mínimos (header + empty + paginación) se cumplen en todas las vistas.
  Nota: `x-agro.data-table` con props `empty-:message`/`empty-icon` llama
  `x-agro.empty-state` internamente — contar como cumplido.
- F3: normalizar composición por dominios (viticulturist → winery → supervisor → resto), 1 PR por dominio.
