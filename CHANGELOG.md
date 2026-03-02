# Agro365 — Registro de Cambios

> Documento que recoge todos los cambios, refactorizaciones y nuevas implementaciones realizadas con Claude Code.
> Stack: **Laravel 11 + Livewire 3 + Alpine.js + Flux UI**

---

## 1. Sistema de Diseño — Migración UI Completa

### Objetivo
Unificar toda la interfaz del rol `viticulturist` bajo un sistema de componentes propio (`x-agro.*`) eliminando estilos inline, SVGs crudos y colores Tailwind `gray-*`.

### Cambios globales en todas las vistas `resources/views/livewire/**/*.blade.php`

| Antes | Después |
|-------|---------|
| `text-gray-*` / `bg-gray-*` / `border-gray-*` | `text-zinc-*` / `bg-zinc-*` / `border-zinc-*` |
| `min-h-screen bg-gradient-to-br from-gray-50...` wrapper | `<div class="space-y-6 animate-fade-in">` |
| KPI divs con gradientes inline | `<x-agro.stat-card label="..." :value="..." icon="..." color="...">` |
| SVGs inline para iconos | `<flux:icon icon="heroicon-name" class="...">` |
| `<h3>` headings en cards | `<x-slot:header>` con icon div + span |
| Divs de filtros ad-hoc | `<x-agro.filter-bar>` |
| `x-agro.table-actions` | `<x-agro.table-cell align="right">` + div |
| Divs de sesión/info | `<flux:callout variant="info/warning/success">` |
| Botones raw con SVG | `<flux:button variant="..." icon="...">` |
| Secciones de formulario `h3+div` | `<x-agro.form-section title="...">` |
| Arrays de headers con objetos SVG | Arrays de strings `['Col1', 'Col2', ...]` |

### Componentes `x-agro.*` disponibles
`card`, `page-header`, `data-table`, `table-row`, `table-cell`, `filter-bar`, `filter-input`, `filter-select`, `stat-card`, `status-badge`, `form-card`, `form-section`, `form-actions`, `empty-state`, `modal`, `action-button`, `pagination`, `progress-bar`, `tabs`, `toast`, `skeleton-card`, `filter-section`

---

## 2. Patrón Index / Create / Edit — Migración de Modales

### Objetivo
Eliminar el patrón de CRUD inline con modales Livewire (`wire:click="openCreate"`, `wire:click="openEdit(id)"`, `<flux:modal>`) y sustituirlo por páginas separadas con rutas propias.

### Patrón implementado

**Antes (modal inline):**
```
Index.php — tenía: showModal, editingId, form fields, openCreate(), openEdit(), save(), resetForm()
index.blade.php — tenía: flux:button wire:click="openCreate", wire:click="openEdit()", <flux:modal>...</flux:modal>
routes: solo GET /module → Index
```

**Después (páginas separadas):**
```
Index.php — solo: filtros, deactivate/delete, render()
Create.php — mount() con defaults, rules(), save() → redirect con toast
Edit.php — mount($model) con ownership check (abort 403), rules(), save() → redirect
index.blade.php — flux:button href="{{ route(...create') }}", <a href="{{ route(...edit, $entry) }}">
routes: GET /module → Index, GET /module/create → Create, GET /module/{model}/edit → Edit
```

### Módulos migrados al patrón Index/Create/Edit

#### Módulos con patrón anterior existente (ya tenían rutas separadas)
| Módulo | Componentes PHP | Vistas Blade |
|--------|----------------|--------------|
| Machinery | Index + Create + Edit + Show | ✅ |
| Containers | Index + Create + Edit + Show | ✅ |
| Campaign | Index + Create + Edit + Show | ✅ |
| Clients | Index + Create + Edit + Show | ✅ |
| PhytosanitaryProducts | Index + Create + Edit | ✅ |
| Phenology | Index + Create + Edit | ✅ |

#### Módulos migrados de modal a páginas separadas
| Módulo | PHP creados | Vistas creadas | Index.php limpiado | index.blade actualizado |
|--------|-------------|----------------|-------------------|------------------------|
| **EnergyUsages** | Create + Edit | create + edit | ✅ | ✅ |
| **AdvisoryMemberships** | Create + Edit | create + edit | ✅ | ✅ |
| **CommercialAuthorizations** | Create + Edit | create + edit | ✅ | ✅ |
| **FieldApplicators** | Create + Edit | create + edit | ✅ | ✅ |
| **FieldEquipment** | Create + Edit | create + edit | ✅ | ✅ |
| **CueExports** | Create + Edit | create + edit | ✅ | ✅ |
| **MarketedHarvests** | Create + Edit | create + edit | ✅ | ✅ |
| **PlotEnvironments** | Create + Edit | create + edit | ✅ | ✅ |
| **ResidueAnalyses** | Create + Edit | create + edit | ✅ | ✅ |
| **ResidueManagements** | Create + Edit | create + edit | ✅ | ✅ |
| **Exploitations** | Create + Edit | create + edit | ✅ | ✅ (modal DGC conservado) |

**Total archivos nuevos/modificados en esta migración: ~65**

### Caso especial: Exploitations
El modal de gestión de DGC (Declaración de Gestión de Cultivos) se **conserva en el Index** porque es un sub-recurso gestionado inline dentro de cada card de explotación. Solo se migró el CRUD de la propia Explotación a Create/Edit.

### Rutas añadidas en `routes/viticulturist.php`
```php
// Para cada uno de los 11 módulos migrados:
Route::get('/create',          Component\Create::class)->name('create');
Route::get('/{model}/edit',    Component\Edit::class)->name('edit');
```
Módulos: `energy-usages`, `advisory-memberships`, `commercial-authorizations`, `field-applicators`, `field-equipment`, `cue-exports`, `marketed-harvests`, `plot-environments`, `residue-analyses`, `residue-managements`, `exploitations`

---

## 3. Módulo Almacén de Insumos — Unificación

### Objetivo
Fusionar 3 módulos solapados (Inventario, Insumos, Almacenes) en un único ítem de menú con tabs.

### Módulos eliminados
| Archivo eliminado | Sustituido por |
|-------------------|----------------|
| `app/Livewire/Viticulturist/Inventory/Index.php` | `Almacen/Index.php` (tab fitosanitarios) |
| `app/Livewire/Viticulturist/Supplies/` (directorio) | `Supplies/Create.php` + `Supplies/Edit.php` |
| `app/Livewire/Viticulturist/Warehouses/Index.php` | `Almacen/Index.php` (tab almacenes) |
| `resources/views/livewire/viticulturist/inventory/index.blade.php` | `almacen/index.blade.php` |
| `resources/views/livewire/viticulturist/warehouses/index.blade.php` | tab dentro de almacen |
| `resources/views/livewire/viticulturist/supplies/` (directorio) | `supplies/create.blade.php` + `edit.blade.php` |

### Nuevas rutas: `viticulturist.almacen.*`
```
almacen.index                          → Almacen/Index.php (tabs: fitosanitarios | insumos | almacenes)
almacen.stock.analytics
almacen.stock.create
almacen.stock.export
almacen.stock.{stock}.edit
almacen.stock.{stock}.consume
almacen.stock.{stock}.movements
almacen.warehouses.create
almacen.warehouses.{warehouse}.edit
```

### Cambio en modelo Supply
- `SUPPLY_TYPES`: eliminado el tipo `phytosanitary` → valores actuales: `fertilizer`, `seed`, `postharvest`, `other`
- Campo añadido: `warehouse_id` (nullable FK → warehouses)

---

## 4. Bug Fix: Cascading Selects con Flux UI + Livewire 3

### Problema
`flux:select` con `wire:model.live` dentro de un `div[wire:key]` dejaba de responder tras ser reemplazado por Idiomorph. Alpine no re-inicializaba el binding en el nuevo elemento.

### Solución implementada
- Selects con `wire:model.live` que disparan cascada: **sin `wire:key`** en su wrapper. Permanecen en el DOM, Idiomorph actualiza las `<option>` in-place.
- Selects leaf/final con `wire:model` (sin `.live`): `wire:key` es seguro.

**Aplicado en:** formularios de Plots Create/Edit (comunidad → provincia → municipio)

---

## 5. Tests — Nuevos archivos creados

### Tests de Feature (Livewire)
| Archivo | Tests | Descripción |
|---------|-------|-------------|
| `tests/Feature/Plots/PlotFieldsTest.php` | 8 | `tenure_regime`, `soil_type`, `orientation` |
| `tests/Feature/Plots/Plantings/CreateTest.php` | 8 | Creación de plantaciones |
| `tests/Feature/Plots/Plantings/EditTest.php` | 5 | Edición de plantaciones |
| `tests/Feature/Viticulturist/Phenology/CreateTest.php` | 7 | Creación de fenología |
| `tests/Feature/Viticulturist/Phenology/EditTest.php` | 5 | Edición de fenología |
| `tests/Feature/Viticulturist/Phenology/IndexTest.php` | 4 | Listado + filtros |

### Tests de Unit (Modelos)
| Archivo | Tests | Descripción |
|---------|-------|-------------|
| `tests/Unit/Models/PhenologyObservationTest.php` | 6 | Modelo PhenologyObservation |

### Lecciones aprendidas (guardadas en memoria)
- `$model->refresh()` necesario tras `factory()->create()` para obtener defaults de DB
- Componentes Livewire deben tener **un único root element** — `x-agro.form-card` como root requiere un `<div>` wrapper adicional
- `Livewire::test` con `mount()` que hace redirect → snapshot null; usar `Livewire::withQueryParams(...)->test(...)`
- `WineryViticulturist` requerido en tests de viticulturist con plots
- `SigpacUse` requerido para `sigpac_use` (required|array|min:1) en Plot edit

---

## 6. Funcionalidades Específicas por Módulo

### EnergyUsages
- Registro de consumo energético (electricidad, gasoil, gas, agua, solar, otro)
- Filtros por campaña y tipo de energía
- KPIs de consumo total en index

### AdvisoryMemberships
- Registro de membresías a entidades de asesoramiento fitosanitario
- Alerta de vencimiento próximo (≤30 días) en index
- Fechas de inicio/fin con validación

### CommercialAuthorizations
- Autorizaciones comerciales de fitosanitarios (ROPO, carnet de aplicador...)
- Control de expiración con badge de estado

### FieldApplicators
- Registro de aplicadores de campo (nombre, DNI/NIE, carnet, teléfono, email)
- Validación de formato DNI/NIE

### FieldEquipment
- Inventario de equipos de aplicación (tipo, marca, modelo, nº serie)
- Control de fecha de revisión/inspección ITVa

### CueExports
- Gestión de exportaciones al CUE (Cuaderno de Explotación Único)
- Estados: `draft → generated → sent → accepted`
- Acciones: `markAsGenerated()`, `markAsSent()` desde index

### MarketedHarvests
- Registro de ventas de cosecha vinculadas a actividades de cosecha
- Cálculo automático de `total_value = quantity_kg × price_per_kg`
- `campaign_id` derivado de `$harvest->activity->campaign_id`

### PlotEnvironments
- Fichas ambientales por parcela/campaña
- Secciones: captaciones de agua, zonas protegidas, topografía, riesgo de erosión
- `updateOrCreate` por `[campaign_id, plot_id]` para evitar duplicados

### ResidueAnalyses
- Análisis de LMR (Límites Máximos de Residuos) de laboratorio
- Resultado `overall_compliant` con badge verde/rojo

### ResidueManagements
- Gestión de residuos de poda, orujo y subproductos vitícolas
- Validación dinámica: `justification` es `required|min:20` cuando `practice_type === 'burning'`

### Exploitations
- Explotaciones agrarias (SIEX/REA) con datos del titular
- DGC (Declaración de Gestión de Cultivos) gestionadas inline en el Index
- Campos: `rea_code`, `siex_exploitation_id`, `is_ecological`, `is_integrated_production`, `is_quality_scheme`

---

## 7. Archivos NO migrados (fuera de alcance)
- `resources/views/content/**` — Páginas de marketing públicas
- `resources/views/blog/**` — Blog
- `resources/views/legal/**` — Páginas legales
- `resources/views/components/**` (raíz) — Componentes Blade legacy (sustituidos por `x-agro.*`)
- `resources/views/reports/**` — Plantillas de informes PDF

---

## 8. Convenciones Establecidas

### Componentes Livewire
```php
#[Layout('layouts.app')]
class Create extends Component
{
    use WithToastNotifications;

    // Propiedades públicas para el formulario
    public string $field = '';

    // mount() — defaults, pre-selección campaña activa
    public function mount(): void { ... }

    // rules() — validación dinámica si procede
    protected function rules(): array { ... }

    // save() — validar, crear, toast, redirect
    public function save(): void
    {
        $this->validate();
        Model::create([...]);
        $this->toast('Creado correctamente', 'success');
        $this->redirect(route('viticulturist.module.index'), navigate: true);
    }
}
```

### Ownership check en Edit
```php
public function mount(Model $model): void
{
    if ($model->viticulturist_id !== Auth::id()) {
        abort(403);
    }
    $this->fill($model->only([...]));
}
```

### Route model binding — parámetros camelCase
```php
Route::get('/{marketedHarvest}/edit',    Edit::class)->name('edit');
Route::get('/{plotEnvironment}/edit',    Edit::class)->name('edit');
Route::get('/{residueAnalysis}/edit',    Edit::class)->name('edit');
Route::get('/{residueManagement}/edit',  Edit::class)->name('edit');
Route::get('/{fieldApplicator}/edit',    Edit::class)->name('edit');
Route::get('/{fieldEquipment}/edit',     Edit::class)->name('edit');
Route::get('/{advisoryMembership}/edit', Edit::class)->name('edit');
Route::get('/{commercialAuthorization}/edit', Edit::class)->name('edit');
Route::get('/{exploitation}/edit',       Edit::class)->name('edit');
Route::get('/{cueExport}/edit',          Edit::class)->name('edit');
Route::get('/{energyUsage}/edit',        Edit::class)->name('edit');
```
