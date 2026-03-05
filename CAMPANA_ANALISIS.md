# Análisis Técnico — Módulo Campaña

**Fecha:** 2026-03-03
**Stack:** Laravel 11 + Livewire 3 + Alpine.js + Flux UI
**Grupo sidebar:** Operaciones → Campaña

---

## 1. Inventario de Componentes

| Componente | Ruta | Archivo |
|-----------|------|---------|
| `Campaign\Index` | `GET /viticulturist/campaign` | `app/Livewire/Viticulturist/Campaign/Index.php` |
| `Campaign\Create` | `GET /viticulturist/campaign/create` | `app/Livewire/Viticulturist/Campaign/Create.php` |
| `Campaign\Edit` | `GET /viticulturist/campaign/{id}/edit` | `app/Livewire/Viticulturist/Campaign/Edit.php` |
| `Campaign\Show` | `GET /viticulturist/campaign/{id}` | `app/Livewire/Viticulturist/Campaign/Show.php` |
| `CampaignDocuments\Index` | `GET /viticulturist/campaign-documents` | `app/Livewire/Viticulturist/CampaignDocuments/Index.php` |
| `CampaignSign\Index` | `GET /viticulturist/campaign-sign` | `app/Livewire/Viticulturist/CampaignSign/Index.php` |

**Vistas:**
- `resources/views/livewire/viticulturist/campaign/index.blade.php`
- `resources/views/livewire/viticulturist/campaign/create.blade.php`
- `resources/views/livewire/viticulturist/campaign/edit.blade.php`
- `resources/views/livewire/viticulturist/campaign/show.blade.php`
- `resources/views/livewire/viticulturist/campaign-documents/index.blade.php`
- `resources/views/livewire/viticulturist/campaign-sign/index.blade.php`

---

## 2. Descripción por Componente

### 2.1 Campaign\Index

Grid de cards con tabs activa/inactiva, búsqueda por nombre/descripción y filtro por año (modal).

**Métodos públicos:**
- `switchTab($tab)` — cambia entre activas/inactivas
- `toggleActive($campaignId)` — activa via `$campaign->activate()` / desactiva directo
- `delete($campaignId)` — solo si `activities_count === 0`
- `clearFilters()`

**Seguridad:** `Campaign::forViticulturist(Auth::id())->findOrFail()` + `Auth::user()->can()`

**QueryString:** `tab`, `search`, `yearFilter`

---

### 2.2 Campaign\Create

Formulario de creación con valores por defecto (año actual, nombre "Campaña YYYY").

**Validaciones:**
- `name` required, max:255
- `year` required, integer, min:2000, max:año_actual+5
- `start_date` nullable, date
- `end_date` nullable, date, after_or_equal:start_date
- Unicidad de año por viticulturist (manual, antes del `create`)

**Flujo:** `validate()` → comprueba unicidad → `DB::transaction` → `Campaign::create()` → `activate()` si `$active=true` → redirect index

---

### 2.3 Campaign\Edit

Idéntico a Create en campos y validaciones. Diferencia: ignora el propio ID en la comprobación de unicidad de año.

**Flujo de activación en Edit:**
```php
if ($this->active && !$this->campaign->active) → $campaign->activate()
elseif (!$this->active && $this->campaign->active) → $campaign->update(['active' => false])
```

---

### 2.4 Campaign\Show

Vista de detalle con stats de actividades por tipo, información general, acciones rápidas y últimas 10 actividades.

**loadCount en mount:**
- `activities` (total)
- `activities as phytosanitary_count`
- `activities as fertilization_count`
- `activities as irrigation_count`
- `activities as cultural_count`
- `activities as observation_count`

**Stat cards en vista:**
- Total, Tratamientos, Fertilizaciones, Riegos, Labores, Observaciones

**Links de acciones rápidas:**
```php
route('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id])
```

---

### 2.5 CampaignDocuments\Index

Gestión de documentos adjuntos a campañas. Create/Edit en modal inline (sin página separada).

**Almacenamiento:** `Storage::disk('private')` en `campaign-documents/{user_id}/`

**Campos:**
- `campaign_id`, `name`, `document_type`, `notes`, `uploadedFile`

**Validaciones upload:**
- Crear: `required|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx`
- Editar: `nullable|file|...` (archivo opcional en edición)

**Métodos públicos:** `openCreate()`, `openEdit($id)`, `save()`, `delete($id)`

**Seguridad:** `where('viticulturist_id', Auth::id())->findOrFail()`

---

### 2.6 CampaignSign\Index

Flujo de firma en 2 pasos para la campaña activa del viticultor.

**Pasos:**
1. `signMidValidation()` → `mid_validation_signed=true`, `mid_validation_date=now()`, `mid_validation_user_id`
2. `signFinalValidation()` → `final_validation_signed=true`, `final_validation_date=now()`, `locked_at=now()`

**Guards:**
- Paso 2 requiere que Paso 1 esté completado
- Cada paso comprueba que no esté ya firmado
- Requiere checkbox de confirmación (`confirmMid` / `confirmFinal`)

**Campaña cargada:** `Campaign::getOrCreateActiveForYear(Auth::id())` — opera siempre sobre la campaña activa

---

## 3. Problemas Detectados

### 🔴 P1 — CampaignDocuments no tiene descarga (ALTO)

**Archivo:** `app/Livewire/Viticulturist/CampaignDocuments/Index.php`

El componente guarda archivos en `Storage::disk('private')` pero no expone ningún método `download()`. Los usuarios pueden subir y eliminar documentos pero **no pueden descargarlos**.

**Fix necesario:**
1. Añadir método `download(int $id)` en el componente que devuelva `Storage::download()`
2. O añadir una ruta en `viticulturist.php` para la descarga con verificación de ownership
3. Añadir botón de descarga en la vista

---

### 🔴 P2 — Cosechas ausentes en stats del Show (MEDIO)

**Archivos:**
- `app/Livewire/Viticulturist/Campaign/Show.php` — líneas 23-40
- `resources/views/livewire/viticulturist/campaign/show.blade.php` — líneas 41-47

`loadCount` incluye 5 tipos de actividad pero omite `harvest`. La cosecha es una actividad core del cuaderno digital (tiene su propio Create/Edit/Show) y debería aparecer en las estadísticas de campaña.

**Fix necesario:**
```php
// En Show.php mount(), añadir al loadCount:
'activities as harvest_count' => function($query) {
    $query->ofType('harvest');
},
```
```blade
{{-- En show.blade.php, añadir stat card: --}}
<x-agro.stat-card label="Cosechas" :value="$campaign->harvest_count" icon="archive-box" color="green" />
```

---

### 🟡 P3 — Flash session() muerto en show.blade.php (BAJO)

**Archivo:** `resources/views/livewire/viticulturist/campaign/show.blade.php` — líneas 3-13

```blade
@if(session('message'))    {{-- nunca se dispara --}}
@if(session('error'))      {{-- nunca se dispara --}}
```

El componente `Show` usa el trait `WithToastNotifications` para notificaciones. Nadie llama a `session()->flash('message', ...)` en este componente. Este bloque es código muerto.

**Fix:** Eliminar las líneas 2-13 de `show.blade.php`.

---

### 🟡 P4 — CampaignSign sin selector de campaña (MEDIO)

**Archivo:** `app/Livewire/Viticulturist/CampaignSign/Index.php` — línea 23

```php
$this->campaign = Campaign::getOrCreateActiveForYear($user->id);
```

El componente opera **siempre sobre la campaña activa**. Si el viticultor necesita firmar una campaña anterior (que ya no está activa), no puede hacerlo desde esta pantalla.

**Opciones:**
- Añadir un selector de campaña con filtro por `mid_validation_signed` / `final_validation_signed`
- O documentar que es un comportamiento intencionado (solo se firma la campaña en curso)

---

### 🟡 P5 — Link `selectedCampaign` en Show sin verificar (MEDIO)

**Archivo:** `resources/views/livewire/viticulturist/campaign/show.blade.php` — líneas 103, 146

```blade
route('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id])
```

El componente `DigitalNotebook` debe tener `selectedCampaign` en su `$queryString` array para que este parámetro sea recogido en `mount()`. Si no está declarado, el enlace lleva a la campaña activa por defecto ignorando el parámetro.

**Verificar:** `app/Livewire/Viticulturist/DigitalNotebook.php` → `protected $queryString`

---

## 4. Resumen de Fixes

| # | Problema | Severidad | Estado | Archivos afectados |
|---|---------|-----------|--------|-------------------|
| P1 | Sin descarga en CampaignDocuments | 🔴 Alto | ✅ Resuelto | `routes/viticulturist.php` + `campaign-documents/index.blade.php` |
| P2 | Harvest ausente en stats del Show | 🔴 Medio | ✅ Resuelto | `Campaign/Show.php` + `show.blade.php` |
| P3 | `session()` flash muerto en Show | 🟡 Bajo | ✅ Resuelto | `show.blade.php` |
| P4 | CampaignSign sin selector de campaña | 🟡 Medio | ✅ Resuelto | `CampaignSign/Index.php` + `campaign-sign/index.blade.php` |
| P5 | `selectedCampaign` queryString no registrado | 🟡 Medio | ✅ Resuelto | `DigitalNotebook.php` |
| P6 | `harvest` no en match() de tipos en show.blade — aparecía como "Observación" | 🔴 Bajo | ✅ Resuelto | `show.blade.php` línea 147 |

---

## 5. Orden de Implementación Propuesto

```
1. P3 — Limpiar session() flash muerto (2 min, riesgo cero)
2. P2 — Añadir harvest_count a Show (10 min, impacto visual inmediato)
3. P5 — Verificar selectedCampaign en DigitalNotebook (5 min, solo lectura)
4. P1 — Implementar descarga de documentos (30 min, feature completa)
5. P4 — Decisión de negocio: ¿CampaignSign necesita selector? (discutir)
```

---

*Documento generado el 2026-03-03. Actualizar estado al resolver cada punto.*
