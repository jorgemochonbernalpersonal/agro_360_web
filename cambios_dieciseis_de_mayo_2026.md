# Cambios 16 de mayo de 2026 — Fase 5 UI Unification (web)

## Resumen

Sesión de refactorización UI del proyecto Laravel Agro365. Adopción sistemática de componentes compartidos `x-agro.*` en todas las vistas index de todos los roles.

---

## Commits realizados

| Hash | Descripción | Ficheros |
|---|---|---|
| `40308af3` | Crear `card-item-header` y `metric-cell`; adoptar en 25 vistas viticulturist | 25 |
| `349dc67e` | Migrar stat cards manuales a `x-agro.stat-card` en harvest y winery dashboard | — |
| `6371e1a6` | Adoptar `card-item-header` en 27 vistas winery | 27 |
| `641f81af` | Adoptar `card-item-header` en 14 vistas supervisor | 14 |
| `5bc8d647` | Adoptar `x-agro.action-button` en ~90 vistas + rediseñar componente | 92 |

---

## Componentes creados / rediseñados

### `x-agro.card-item-header`
Encabezado estándar de card de grid. Props:
```blade
<x-agro.card-item-header
    icon="user"
    :title="$item->name"
    :subtitle="$item->email"          {{-- nullable --}}
    iconBg="bg-agro-100"
    iconColor="text-agro-600"
    size="md"                          {{-- sm = w-9h-9/size-4 | md = w-10h-10/size-5 --}}
    radius="xl"                        {{-- full = rounded-full | xl = rounded-xl --}}
>
    <flux:badge color="green" size="sm">Activo</flux:badge>  {{-- slot: trailing badge --}}
</x-agro.card-item-header>
```

**Reglas de skip** (no se convierte):
- Título con `<a>` link
- Título con `<span>` con estilo independiente
- Icono es letra/inicial o emoji
- Subtitle es `<div>` con contenido condicional complejo
- Variables de color no separables en bg + text

### `x-agro.action-button`
Botón icon-only para footers de cards. Renderiza `<a>` si tiene `href`, `<button>` si no. Todos los atributos `wire:*` fluyen via `$attributes`.

```blade
{{-- Variantes semánticas (auto-icono + auto-color) --}}
<x-agro.action-button variant="view"       href="{{ route(...) }}" wire:navigate title="Ver" />
<x-agro.action-button variant="edit"       href="{{ route(...) }}" wire:navigate title="Editar" />
<x-agro.action-button variant="delete"     wire:click="delete({{ $id }})" wire:confirm="..." title="Eliminar" />
<x-agro.action-button variant="archive"    wire:click="archive({{ $id }})" title="Archivar" />
<x-agro.action-button variant="restore"    wire:click="restore({{ $id }})" title="Restaurar" />
<x-agro.action-button variant="activate"   wire:click="activate({{ $id }})" title="Activar" />
<x-agro.action-button variant="deactivate" wire:click="deactivate({{ $id }})" title="Desactivar" />

{{-- Icono explícito + color variant --}}
<x-agro.action-button icon="envelope"  variant="primary" wire:click="invite({{ $id }})" title="Invitar" />
<x-agro.action-button icon="x-mark"    variant="danger"  wire:click="revoke({{ $id }})" title="Revocar" />
```

**Mapa semántico completo:**
| variant | icon | color hover |
|---|---|---|
| view | eye | zinc |
| edit | pencil-square | zinc |
| delete | trash | red |
| archive | archive-box | amber |
| restore | arrow-path | agro |
| activate | check-circle | green |
| deactivate | no-symbol | red |
| map | map | zinc |
| info | information-circle | zinc |
| generate | sparkles | agro |
| history | clock | zinc |
| planting | book-open | zinc |
| send | paper-airplane | agro |
| download | arrow-down-tray | zinc |

**Color variants puros** (con `icon` explícito):
`default` / `danger` / `warning` / `primary` / `success`

---

## Vistas saltadas (no convertibles)

### card-item-header — winery
- `fermentation-controls` — título tiene `<a>` link al vino
- `wine-analysis` — título tiene `<a>` + span de añada
- `wine-transfers` — título condicional + span
- `subproducts` — título condicional + span
- `harvest/quality-analysis` — avatar de letra
- `harvest/reception` — columna derecha con year+badge flex-col
- `harvest/summary` — avatar de iniciales `$initials`
- `harvest/viticulturist-estimates` — avatar de letra
- `viticulturists/index` — avatar de letra
- `cellar/containers/index` — subtitle es `<div>` con badge inline
- `denomination/requests` — subtitle con `<span>` coloreados (Tú/DO)
- `denomination/index` — no es grid de cards

### card-item-header — supervisor
- `oversight/plots/index` — subtitle es `<div>` condicional + badge ECO
- `oversight/growers/index` — título es `<a>`
- `oversight/wineries/index` — título es `<a>`
- `census/index` (card winery) — título es `<a>`
- `statistics/index` — dashboard de resumen, no grid estándar

---

## Pendiente de la fase 5

### Componentes existentes sin adoptar aún

| Componente | Descripción | Prioridad |
|---|---|---|
| `x-agro.filter-chip` | Chips de filtro por estado — ~20 vistas con patrón manual | Media |
| `x-agro.metric-cell` | Mini-stat cell — algunas vistas aún usan `div` manual | Baja |
| `x-agro.table-*` | `table`, `table-row`, `table-cell`, `th` — vistas con tablas manuales | Baja |
| `x-agro.filter-modal` | Componente nuevo, sin uso todavía | Baja |

### Patrón `filter-chip` típico a sustituir
Buscar vistas con filtros de estado tipo chip:
```bash
grep -rn "filter-chip\|filterStatus\|filterTab" resources/views/livewire --include="*.blade.php" -l
```

### Cambios backend pendientes de commitear (no son UI)
24 ficheros sin commitear de otra persona/sesión:
- `app/Livewire/Viticulturist/DigitalNotebook/` — Create/Edit forms
- `app/Models/CulturalWork.php`, `Irrigation.php`, `PhytosanitaryTreatment.php`
- `cypress/e2e/viticulturist/observations.cy.js`
- Tests Feature de cuaderno de campo y pest management

---

## Pendiente fuera de fase 5

### Mobile (agro365_mobile)
- GPS tracking mejorado
- Notificaciones push
- Modo offline
- Maestro E2E bloque 2 viticulturist (fertilización, post-vendimia, etc.)
- Suites Maestro supervisor y producer

### Referencia de componentes disponibles
```
resources/views/components/agro/
├── action-button.blade.php      ✅ rediseñado hoy
├── card-item-header.blade.php   ✅ creado en fase 5
├── card.blade.php
├── empty-state.blade.php
├── filter-bar.blade.php
├── filter-chip.blade.php        ← pendiente adoptar
├── filter-input.blade.php
├── filter-modal.blade.php       ← pendiente adoptar
├── filter-select.blade.php      ✅ mejorado hoy (label + placeholder props)
├── loading-grid.blade.php
├── metric-cell.blade.php        ← pendiente adoptar
├── page-header.blade.php
├── pagination.blade.php
├── progress-bar.blade.php
├── search-input.blade.php
├── stat-card.blade.php
├── stats-section.blade.php
├── status-badge.blade.php
├── table*.blade.php             ← pendiente adoptar
├── tabs.blade.php
└── toast.blade.php
```
