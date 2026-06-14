# 📌 IMPORTANTE — Unificación / limpieza de componentes y listados

**Fecha:** 2026-05-31 · **Actualizado:** 2026-06-14
**Estado:** trait `WithListing` ya en `staging`. Rollout del patrón `search`+tabs
`active/inactive` **completado en los componentes `Component` planos** (8 más
migrados el 2026-06-14, ver §Progreso). Lo que queda no encaja con el trait tal
cual (ver §Límites del rollout).

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

## ✅ Progreso (2026-06-14)

Migrados 8 listados `Component` planos al trait (commit `72009be4`, −132 líneas netas,
sin cambio de comportamiento ni de URLs):
`Viticulturist/Clients`, `Viticulturist/Campaign`, `Viticulturist/Containers`
(conserva `switchTab` propio que limpia `filterStatus`), `Viticulturist/PhytosanitaryProducts`,
`Viticulturist/DigitalNotebook/EstimatedYields`, `Plots/Plantings`, `Winery/Clients`,
`Winery/Oenologists`. Verificado: php -l + Pint + PHPStan + tests en aislamiento.

Con esto, **los candidatos limpios del trait están agotados** en componentes `Component`
planos.

## 🚧 Límites del rollout (por qué no se migra el resto)

El trait sirve UN patrón concreto: `$search` + tabs `active/inactive` (default `'active'`).
Los ~59 `Index` que aún usan `WithPagination` directo NO encajan:

1. **`AbstractIndex`** (Viticulturist/Winery): abstracción más rica (`baseQuery`/`applyFilters`/
   `viewData`) y aliasea `search` como `q`. Migrar al trait sería un **downgrade** + rompería URLs.
   Ej.: WaterConcessions, Certifications, EnergyUsages, Winery/Cellar/Containers, ProductLots.
2. ~~**Tab por defecto ≠ `'active'`**~~ → **RESUELTO** (2026-06-14, commit `2f27efd8`): el trait
   se generalizó con `defaultTab()` sobrescribible (`currentTab` arranca vacío, `mountWithListing()`
   lo fija, `queryStringWithListing()` calcula el `except`). Migrados 4 más: Supervisor/Inspection
   (`all`), Census (`wineries`), Regulation (`autorizaciones`), Invoices/Harvest (`list`).
3. **Tabs de sección, no listado** (`$activeTab`/`$tab`): Admin/Catalogs, Winery/Denomination,
   Territory, Warehouse. No son filtros de listado.
4. **Solo búsqueda/solo tabs (incompletos)**: el trait empaqueta search+tabs; añadir uno muerto es
   ruido. Ej.: Supervisor/Labels·Qualification (tabs sin search), o listados solo-search.
5. **Métodos propios no triviales sin test**: Admin/Users (`all`) encaja, pero su `switchTab` y
   `updatingSearch` limpian `selectedUsers` (se quedarían igual) y **no tiene test** → skip por prudencia.

**Pendiente menor:** Harvests (`pending`) NO usa paginación (switchTab/updatingSearch vacíos) → no es
listado paginado, no migrar. PlannedWorks (`pending`) es `AbstractIndex`.

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
- `staging`: limpieza + notación + docs + **trait `WithListing` ya mergeado** +
  pilotos (Clients root, Machinery, Plots) + **8 listados migrados** (commit `72009be4`).
- Rollout del patrón active/inactive: **completo** en componentes `Component` planos.
  El resto no encaja (ver §Límites del rollout).
