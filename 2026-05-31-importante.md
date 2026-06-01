# 📌 IMPORTANTE — Unificación / limpieza de componentes y listados

**Fecha:** 2026-05-31
**Estado:** piloto hecho, rollout pendiente. Retomar el rollout de `WithListing` por dominios.

---

## Resumen de lo hecho esta sesión

### 1. Limpieza de componentes Blade muertos → ya en `staging` (pusheado)
- Eliminados **35 componentes muertos** (3 tandas). `resources/views/components/` raíz pasó de 38 a **8 vivos**.
- Los 8 vivos: `activity-locked-badge`, `app-layout`, `feature-card`, `info-card`, `page-header`, `related-links` (vía `@include`), `sidebar`, `top-bar`.
- `components/agro/` (35 componentes) es el **único design system**.
- **Formularios 100% en Flux** (`flux:input/select/label/button`). Los primitivos `x-input/select/label/textarea/button/form-*` raíz estaban muertos → borrados.

### 2. Notación unificada → ya en `staging`
- Todo a notación punto `x-agro.NAME` (antes convivía `x-agro-NAME` con guion).
- Eliminado el bucle de alias de guion en `AppServiceProvider::boot` (era código muerto; la notación punto resuelve nativa).

### 3. Documentación → ya en `staging`
- `docs/patron-vista-listado.md`: plantilla canónica de vista de listado (basada en `plots/index`), con orden de bloques, snippet y checklist.

### 4. Trait `WithListing` (Livewire) → PENDIENTE DE PUSH
- **Rama:** `refactor/with-listing-trait` (sobre staging).
- Nuevo: `app/Livewire/Concerns/WithListing.php` — unifica el boilerplate de listados.
- **Piloto migrado (3):** `Clients/Index`, `Viticulturist/Machinery/Index`, `Plots/Index`. -38/+6 líneas.
- Commits: `1f2e83dc` (trait), `c15dde2a` (clients), `5e4cb2f1` (machinery), `f8984543` (plots).

---

## ▶️ LO QUE QUEDA (retomar aquí)

### Rollout de `WithListing` a los ~91 listados restantes
~94 componentes Livewire repiten el mismo boilerplate (`$search`, `WithPagination`, `resetPage`, tabs). Migrar por **dominios** en microcommits:
1. `viticulturist/` (los que tengan tabs+search)
2. `winery/`
3. `supervisor/`
4. `producer/` + `admin/` + resto

**No es 100% mecánico** — revisar cada componente: nombres de filtro propios, `mount()` que toque `currentTab`, tabs con default distinto a `'active'`.

### Cómo migrar un componente (receta)
1. Añadir `use App\Livewire\Concerns\WithListing;`
2. En la línea `use` de traits: `WithPagination` → `WithListing`
3. Borrar el import `use Livewire\WithPagination;`
4. Borrar las propiedades `public $search` y `public $currentTab`
5. Borrar los métodos `switchTab()` y `updatingSearch()`
6. Quitar las entradas `'search'` y `'currentTab'` de `$queryString` (las cubre `#[Url]` del trait)
7. **Mantener** los filtros propios + sus `updatingX()` + `clearFilters()` + `render()`
8. Verificar: `php -l` + test en **aislamiento** (no en lote)

---

## ⚠️ Notas / aprendizajes

- **No reescribir lo que funciona.** Las vistas ya cubren empty/loading/pagination con patrones válidos distintos (empty-state directo / data-table / @forelse; loading-grid / wire:loading). No homogeneizar por cosmética.
- **Flakiness de tests:** colisión de seed en paralelo → `SQLSTATE[23000] Duplicate entry 'TEMP' for key 'grape_varieties_code_unique'`. Provoca fallos masivos NO relacionados con los cambios. **Verificar siempre en aislamiento** (`php artisan test <fichero>`).
- **Cambio de comportamiento intencionado:** componentes sin `updatingSearch` (p.ej. Plots) ahora resetean a página 1 al buscar (correcto).
- **Auditar componentes:** usar `grep "<x-NOMBRE\b"` en `.blade.php` (NO substrings tipo `x-label\b`, que dan falsos positivos como `x-label-trailing`; ni contar ficheros `.backup`).
- **Decisión abierta de bajo valor:** `page-header` raíz (8 usos, con icon/badge/auto-acento) vs `x-agro.page-header` (232). Recomendación: dejarlo (migrar regresaría features).

---

## Estado git
- `staging`: limpieza + notación + docs (pusheado por el usuario).
- `refactor/with-listing-trait`: trait + piloto (4 commits) — **pendiente de push y de continuar rollout**.
