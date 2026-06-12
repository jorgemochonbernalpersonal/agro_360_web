# Auditoría UI — Agro365

## Estado actual

La base de componentes está bien pensada pero la adopción es incompleta.
Muchas vistas escriben HTML/Tailwind inline en lugar de usar los componentes existentes.
**Diagnóstico: caos controlable, no refactor profundo.**

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

### Fase 1 — Regla de color (sin tocar código)
Documentar en `app.css` qué shade usar para qué:
- `agro-600` → color de texto activo / links
- `agro-700` → botones primarios (bg)
- `agro-800` → hover de botones / CTA oscuro
- `agro-50/100` → fondos de iconos / badges
- Prohibir `green-*` y `emerald-*` fuera de estados semánticos

### Fase 2 — Migrar inline styles recurrentes
Empezar por los archivos con más usos incorrectos:
1. `viticulturist/dashboard.blade.php`
2. `winery/wines/show.blade.php`
3. Admin y supervisor (menor impacto, menos vistas)

### Fase 3 — Limpiar huérfanos
- Eliminar el `page-header` duplicado
- Deprecar `feature-card` e `info-card` si no se usan

---

## Pendiente de explorar

- [ ] Leer `components/agro/card.blade.php` — entender props y variantes disponibles
- [ ] Leer `components/agro/stat-card.blade.php` — revisar el colorMap interno
- [ ] Leer `layouts/app.blade.php` — ver estructura del área autenticada
- [ ] Revisar `viticulturist/dashboard.blade.php` — mayor fuente de estilos inline
- [ ] Revisar `winery/wines/show.blade.php` — ejemplo de mezcla Flux + inline
