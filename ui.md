# Auditoría UI — Agro365

## Estado actual

La base de componentes está bien pensada pero la adopción es incompleta.
Muchas vistas escriben HTML/Tailwind inline en lugar de usar los componentes existentes.
**Diagnóstico: caos controlable, no refactor profundo.**

---

## Progreso

### ✅ Hecho (2026-06-12)

- **Regla de color documentada** en `resources/css/app.css` (derivada del
  rediseño de la landing). Cierra la **Fase 1**.
- **Componente nuevo `agro/filter-modal`** — encapsula header (icono+título+X),
  cuerpo y footer (limpiar+aplicar) del modal de filtros.
- **Migración completa de los modales de filtros**: 38 modales en 37 vistas
  pasados a `agro/filter-modal` (viticulturist, winery, clientes, SIGPAC,
  plantaciones). Ya **no queda ningún modal de filtros inline** en la app.
  Balance: −830 líneas netas. Labels crudos → `agro/field-label`.
  Normalización: header `agro-50/700`, footer plano, icono unificado a verde.

### ✅ Hecho (2026-06-17) — Fase 3, lotes 7–12

- **Nuevos componentes**: `<x-agro.alert-chip>` (7 colores, href opcional), `<x-agro.kpi-tile>` (7 colores, prop `active`), `<x-agro.divider-vertical>` (35 instancias en 31 vistas), `<x-agro.list-row>` (19 instancias en 9 vistas).
- **Migración export buttons**: `<a class="inline-flex...">` → `<flux:button>` en harvest/reception y quality-analysis.
- **Migración callouts**: 4 callouts inline → `<flux:callout>` en admin y supervisor.
- **Patrones descartados** (ROI insuficiente o incompatibles): avatar circles (heterogéneos), badges con interpolación dinámica `bg-{{ $color }}-100` (rompen Tailwind purge), mini empty-states dentro de `@empty` (padding variable, `<td>` incompatible).

### ⏳ Pendiente

- **Fase 2** — migrar inline styles de dashboards (ver abajo).
- **Fase 3** — completada (ver lotes 1–12 arriba).

---

## Componentes existentes (`resources/views/components/agro/`)

| Componente | Usos aprox. | Notas |
|---|---|---|
| `agro/card` | ~659 (66 vistas) | El más usado, bien adoptado |
| `agro/page-header` | ~120 | Duplicado — ver más abajo |
| `agro/stat-card` | ~180 | Tiene su propio colorMap interno |
| `agro/modal` | ~80 | |
| `agro/status-badge` | ~100 | |
| `agro/form-section` | ~250 | |
| `agro/table` / `table-row` / `table-cell` / `th` | ~50 | |
| `agro/empty-state` | — | |
| `agro/pagination` | — | |
| `agro/search-input` / `filter-input` | — | |
| `agro/skeleton-card` | — | |
| `agro/tabs` | — | |
| `agro/toast` | — | |
| `agro/alert-banner` | — | |
| `agro/action-button` | — | |
| `agro/progress-bar` | — | |
| `agro/upgrade-prompt` | — | |
| `agro/help-tip` | — | |
| `agro/stats-section` | — | |
| `agro/metric-cell` / `card-item-header` | — | |
| `agro/form-actions` / `form-card` | — | |
| `agro/filter-*` (5 variantes) | — | |

**Componentes huérfanos / a limpiar:**
- `components/page-header.blade.php` — duplicado de `components/agro/page-header.blade.php`, uno de los dos sobra
- `components/feature-card.blade.php` — casi sin uso
- `components/info-card.blade.php` — casi sin uso

---

## Layouts

| Archivo | Uso |
|---|---|
| `layouts/app.blade.php` | Área autenticada (Flux, `data-flux-appearance="light"`, theme `#4a7c59`) |
| `layouts/guest.blade.php` | Auth y páginas públicas |

---

## Vistas por rol (solo conteo de archivos)

| Rol | Directorio | Archivos | Usos `<x-agro.*>` |
|---|---|---|---|
| Viticulturist | `livewire/viticulturist/` | 13 | ~1.613 |
| Winery | `livewire/winery/` | 68+ | ~1.428 |
| Supervisor | `livewire/supervisor/` | 18 | ~300 |
| Admin | `livewire/admin/` | 20 | ~254 |
| Producer | `livewire/producer/` | 3 | ~114 |

`<flux:button>` → 1.008 usos en toda la app.

---

## Problemas identificados (por prioridad)

### P0 — Dos sistemas de color activos simultáneamente

Conviven en el mismo proyecto:
- `bg-agro-700` / `text-agro-600` (correcto, paleta custom)
- `bg-green-600` / `bg-emerald-50` (Tailwind raw, incorrecto)
- `bg-[var(--color-agro-green)]` (legacy CSS var, debería migrarse)

No hay una regla documentada de cuándo usar cada shade (`agro-400` vs `agro-500` vs `agro-600`).

**Ejemplos concretos:**
- `livewire/viticulturist/dashboard.blade.php:57` — botón inline `bg-green-600`
- `livewire/winery/wines/show.blade.php:118` — array de colores hardcoded
- `components/agro/card.blade.php:5` — usa `agro-400` para borde inferior

### P1 — Tarjetas y botones definidos inline

Muchas vistas evitan los componentes y escriben directamente:

```blade
{{-- Incorrecto — inline en dashboard.blade.php --}}
<div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-5">

{{-- Correcto — usando el componente --}}
<x-agro.card>
```

```blade
{{-- Incorrecto — botón inline --}}
<a class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">

{{-- Correcto — Flux button --}}
<flux:button variant="primary" size="sm">
```

**Archivos con más inline styles:**
- `livewire/viticulturist/dashboard.blade.php` líneas 13–70
- `livewire/winery/wines/show.blade.php` línea 41–60

### P2 — Headings inconsistentes

```blade
{{-- Estilo 1: Flux (correcto) --}}
<flux:heading size="xl" level="1">{{ $title }}</flux:heading>

{{-- Estilo 2: h3 neutral --}}
<h3 class="font-semibold text-zinc-800 mb-3">{{ $title }}</h3>

{{-- Estilo 3: h3 con color temático hardcoded --}}
<h3 class="font-bold text-green-900">{{ __('Tu viñedo digital') }}</h3>
```

### P3 — Componentes duplicados/huérfanos

- `components/page-header.blade.php` vs `components/agro/page-header.blade.php`
- `feature-card` e `info-card` sin uso relevante

---

## Plan de acción propuesto

### Fase 1 — Regla de color (sin tocar código) ✅ HECHO
Documentado en `resources/css/app.css`. La regla final (derivada de la landing)
difiere del borrador inicial en el hover del botón primario: **aclara**
(700→600), no oscurece a 800 (que es casi negro). Convención:
- `agro-700` bg botón primario · `hover:agro-600`
- `agro-600` → links / texto de acento
- `agro-50 + text-agro-700` → fondos de icono / badges (antes se usaba 100/600)
- `agro-500` → iconos decorativos · `agro-400` → borde de acento en card
- Prohibir `green-*` y `emerald-*` fuera de estados semánticos

### Fase 2 — Migrar inline styles recurrentes ⏳ PENDIENTE
Empezar por los archivos con más usos incorrectos:
1. `viticulturist/dashboard.blade.php`
2. `winery/wines/show.blade.php`
3. Admin y supervisor (menor impacto, menos vistas)

### Fase 3 — Limpiar huérfanos ⏳ PENDIENTE
- Eliminar el `page-header` duplicado (`components/page-header.blade.php` vs
  `components/agro/page-header.blade.php`)
- Deprecar `feature-card` e `info-card` si no se usan

### (Extra, no estaba en el plan) — Modal de filtros ✅ HECHO
Componentizado en `agro/filter-modal` y propagado a las 37 vistas. Ver Progreso.

---

## Pendiente de explorar

- [x] Leer `components/agro/card.blade.php` — props: `padding`, `color`, `title`, slots `header`/`footer`. El `color` tiene su propio match con `green-*` raw (a migrar)
- [ ] Leer `components/agro/stat-card.blade.php` — revisar el colorMap interno
- [ ] Leer `layouts/app.blade.php` — ver estructura del área autenticada
- [ ] Revisar `viticulturist/dashboard.blade.php` — mayor fuente de estilos inline
- [ ] Revisar `winery/wines/show.blade.php` — ejemplo de mezcla Flux + inline
