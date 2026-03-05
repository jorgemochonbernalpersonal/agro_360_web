# Análisis Técnico — Sección "Operaciones" del Sidebar

**Fecha:** 2026-03-03
**Stack:** Laravel 11 + Livewire 3 + Alpine.js + Flux UI
**Scope:** Módulos del grupo `operations` en `NavigationHelper::buildMenu()`

---

## 1. Estructura Actual del Sidebar

El sidebar está construido sobre `NavigationHelper::getMenu()` con caché por usuario/ruta (1 hora).
El grupo **Operaciones** contiene actualmente 4 ítems:

```
📁 Operaciones
  ├── 📋 Campaña          → viticulturist.campaign.*
  │     ├── Campañas
  │     ├── Documentos
  │     └── Firma y Cierre
  ├── ✏️  Cuaderno Digital → viticulturist.digital-notebook.*
  │     ├── Actividades
  │     ├── Rendimientos
  │     └── Fitosanitarios
  ├── 📊 Cumplimiento PAC → viticulturist.pac-compliance
  └── 📄 Informes Oficiales → viticulturist.official-reports.*
```

---

## 2. Inventario de Componentes por Módulo

### 2.1 Campaña

| Componente | Ruta | Estado |
|-----------|------|--------|
| `Campaign\Index` | `GET /viticulturist/campaign` | ✅ |
| `Campaign\Create` | `GET /viticulturist/campaign/create` | ✅ |
| `Campaign\Edit` | `GET /viticulturist/campaign/{id}/edit` | ✅ |
| `Campaign\Show` | `GET /viticulturist/campaign/{id}` | ✅ |
| `CampaignDocuments\Index` | `GET /viticulturist/campaign-documents` | ✅ |
| `CampaignSign\Index` | `GET /viticulturist/campaign-sign` | ✅ |

**Funcionalidades en Index:** `toggleActive()`, `delete()` (con guard: no eliminar si tiene actividades)
**Política de acceso:** `CampaignPolicy` vía `Auth::user()->can()`

### 2.2 Cuaderno Digital

El módulo más extenso de toda la aplicación.

| Componente | Ruta | Estado |
|-----------|------|--------|
| `DigitalNotebook` (ActivityList + ActivityFilters + ActivityStats) | `GET /viticulturist/digital-notebook` | ✅ |
| `ActivityAuditHistory` | embebido | ✅ |
| `CreatePhytosanitaryTreatment` | `GET /digital-notebook/treatment/create` | ✅ |
| `EditPhytosanitaryTreatment` | `GET /digital-notebook/treatment/{id}/edit` | ✅ |
| `CreateFertilization` | `GET /digital-notebook/fertilization/create` | ✅ |
| `EditFertilization` | `GET /digital-notebook/fertilization/{id}/edit` | ✅ |
| `CreateIrrigation` | `GET /digital-notebook/irrigation/create` | ✅ |
| `EditIrrigation` | `GET /digital-notebook/irrigation/{id}/edit` | ✅ |
| `CreateCulturalWork` | `GET /digital-notebook/cultural/create` | ✅ |
| `EditCulturalWork` | `GET /digital-notebook/cultural/{id}/edit` | ✅ |
| `CreateObservation` | `GET /digital-notebook/observation/create` | ✅ |
| `EditObservation` | `GET /digital-notebook/observation/{id}/edit` | ✅ |
| `CreateHarvest` | `GET /digital-notebook/harvest/create` | ✅ |
| `EditHarvest` | `GET /digital-notebook/harvest/{id}/edit` | ✅ |
| `ShowHarvest` | `GET /digital-notebook/harvest/{id}` | ✅ |
| `EstimatedYields\Index` | `GET /digital-notebook/estimated-yields` | ✅ |
| `EstimatedYields\Create` | `GET /digital-notebook/estimated-yields/create` | ✅ |
| `EstimatedYields\Edit` | `GET /digital-notebook/estimated-yields/{id}/edit` | ✅ |
| `Containers\Index` | Ruta en `viticulturist.containers.*` | ⚠️ Ver nota |
| `PhytosanitaryProducts\Index/Create/Edit` | `GET /viticulturist/phytosanitary-products/*` | ✅ |

> **Nota Containers:** Existe doble implementación:
> - `DigitalNotebook\Containers\{Index,Create,Edit}` (embebido en notebook)
> - `Viticulturist\Containers\{Index,Create,Show,Edit}` (ruta propia `viticulturist.containers.*`)
> El sidebar apunta a `viticulturist.containers.*` bajo **Recursos**, pero también existen dentro del Cuaderno Digital. Posible duplicidad.

### 2.3 Cumplimiento PAC

| Componente | Ruta | Estado |
|-----------|------|--------|
| `PacComplianceDashboard` | `GET /viticulturist/pac-compliance` | ✅ (solo lectura) |

**Es un componente de solo lectura.** Calcula:
- % de cumplimiento de actividades en el rango seleccionado (30/90/180/365/todo)
- Métricas de productos fitosanitarios (registro válido, próximos a caducar)
- Actividades bloqueadas (`is_locked`)
- Validación de periodos de carencia en cosechas

Depende de: `PacComplianceValidator`, `WithdrawalPeriodValidator`

### 2.4 Informes Oficiales

| Componente | Ruta | Estado |
|-----------|------|--------|
| `OfficialReports\Index` | `GET /viticulturist/official-reports` | ✅ |
| `OfficialReports\Create` | `GET /viticulturist/official-reports/crear` | ✅ |
| Download (controller closure) | `GET /official-reports/{report}/download` | ✅ |
| Preview (controller closure) | `GET /official-reports/{report}/preview` | ✅ |

**Funcionalidades avanzadas en Index:**
- Modal invalidar (con contraseña + motivo + límite 30 días)
- Modal compartir por email (`OfficialReportShared` Mailable)
- Modal vista previa
- Descarga en múltiples formatos (`OfficialReportService`)
- Auto-polling para informes en proceso (`hasPendingReports`)

---

## 3. Análisis de Coherencia del Grupo "Operaciones"

### ✅ Lo que tiene sentido

**Campaña** es la unidad organizativa de todo el trabajo. Todos los registros llevan `campaign_id`. Es lógico que sea el primer ítem de Operaciones — el viticultor primero crea/activa una campaña y luego opera dentro de ella.

**Cuaderno Digital** es el corazón de las operaciones diarias: registrar tratamientos, riegos, abonados, trabajos culturales, cosechas. Correctamente ubicado en Operaciones.

### ⚠️ Lo que genera dudas

**Cumplimiento PAC** — Es un dashboard analítico/de control, no una operación. No se registra nada aquí. Conceptualmente encaja mejor en:
- **Registro Oficial** (ya contiene CUE, Autorizaciones, Análisis de Residuos)
- O como subítem de Campaña (es un control de la campaña activa)

**Informes Oficiales** — Genera documentos firmados a partir de los datos del cuaderno. Es un producto *de salida* de las operaciones, no una operación en sí. Podría estar en:
- **Registro Oficial** (junto a Exportaciones CUE)
- O en una sección propia "Documentación"

### 🔴 Problemas detectados

1. **Doble contenedor de Containers**: `DigitalNotebook\Containers` y `Viticulturist\Containers` apuntan a distintas rutas. Si son el mismo modelo, hay duplicidad de mantenimiento.

2. **Fitosanitarios en sidebar bajo Cuaderno Digital** apunta a `viticulturist.phytosanitary-products.*` — es un catálogo de productos, no una actividad del cuaderno. Semánticamente debería estar en **Recursos** o **Almacén de Insumos**.

3. **NavigationHelper usa caché de 1 hora** (`Cache::remember`). Si el usuario tiene tickets de soporte abiertos, el badge del ítem "Soporte" puede no actualizarse en tiempo real.

4. **Ruta legacy inconsistente**: `official-reports/crear` (español) mientras el resto usa `create` (inglés). Puede causar confusión en tests y documentación.

---

## 4. Propuesta de Reorganización del Sidebar

### Operaciones (simplificado y más coherente)

```
📁 Operaciones
  ├── 📋 Campaña
  │     ├── Campañas
  │     ├── Documentos
  │     └── Firma y Cierre
  └── ✏️  Cuaderno Digital
        ├── Actividades
        ├── Rendimientos
        └── Fitosanitarios  ← MOVER a Recursos si es catálogo
```

### Registro Oficial (ampliado con los movidos)

```
📁 Registro Oficial
  ├── 🏢 Explotación SIEX/REA
  ├── 🛡️  Autorizaciones Comerciales
  ├── 👤 Asesorías Técnicas
  ├── 🪪 Aplicadores ROPO
  ├── ⚙️  Equipos ITB/ITEA
  ├── 🔬 Análisis de Residuos
  ├── 🗑️  Gestión de Residuos
  ├── 📤 Exportaciones CUE
  ├── 📊 Cumplimiento PAC     ← MOVER desde Operaciones
  └── 📄 Informes Oficiales   ← MOVER desde Operaciones
```

---

## 5. Plan de Trabajo por Módulo

### Fase 1 — Campaña
- [ ] Revisar flujo Create → Show → Documentos → Firma y Cierre
- [ ] Verificar que `Campaign::getOrCreateActiveForYear()` se usa consistentemente
- [ ] Revisar vista Show: ¿qué información agrega vs Index?
- [ ] Revisar CampaignDocuments e Index de CampaignSign

### Fase 2 — Cuaderno Digital
- [x] Revisar ActivityList: filtros, paginación, tipos de actividad → DigitalNotebook.php unificado, repositorio correcto
- [x] Revisar Create/Edit por tipo → 6 tipos × 2 = 12 componentes, todos implementados y migrados
- [ ] Resolver duplicidad Containers (DigitalNotebook vs standalone) → **DT-01 activo, ver sección 9**
- [x] Revisar EstimatedYields: vinculado a campaña (selectedCampaign queryString) ✅
- [x] Verificar `is_locked`: implementado en modelo + repositorio + UI (mensaje en deleteActivity()) ✅
- [ ] Limpiar subcomponentes legacy ActivityList/ActivityStats/ActivityFilters (posible código muerto)
- [ ] Implementar rutas de Containers (no existen bajo digital-notebook.* ni viticulturist.containers.*)
- [ ] Decidir: pruning y post_harvest definidos en ACTIVITY_TYPES pero sin componentes Create/Edit

### Fase 3 — Cumplimiento PAC
- [x] Mover a Registro Oficial (sidebar) → eliminado de `operations`, añadido al final de `compliance`
- [x] `PacComplianceValidator` y `WithdrawalPeriodValidator` revisados — correctos, sin cambios necesarios
- [x] `updatedTimeRange()` recalcula correctamente — dispara `loadMetrics()` en el listener

### Fase 4 — Informes Oficiales
- [x] Mover a Registro Oficial (sidebar) → eliminado de `operations`, añadido al final de `compliance`
- [x] Flujo Create revisado — correcto: selección de tipo + rango fechas + campaña + firma digital + job async
- [x] `OfficialReportService` revisado — correcto: PDF, CSV, XML
- [x] Ruta `/crear` → `/create` corregida en `routes/viticulturist.php`
- [x] Polling `hasPendingReports` revisado — usa `wire:poll.5s` condicional, correcto

---

## 6. Cambios Aplicados Hasta Ahora

| Cambio | Archivo | Estado |
|--------|---------|--------|
| Redirección CORS agro365.eu → agro365.es | `public/.htaccess` | ✅ Aplicado |

---

## 7. Deuda Técnica Identificada

| ID | Descripción | Severidad | Módulo |
|----|-------------|-----------|--------|
| DT-01 | Doble implementación de Containers | Media | Cuaderno Digital / Recursos |
| DT-02 | Caché de NavigationHelper no se invalida al crear tickets | Baja | Sistema/Soporte |
| DT-03 | ~~Ruta `official-reports/crear` debería ser `create`~~ | Baja | ✅ Resuelto |
| DT-04 | ~~Fitosanitarios como subítem del Cuaderno pero es un catálogo de Recursos~~ | Media | ✅ Resuelto |
| DT-05 | ~~Cumplimiento PAC e Informes Oficiales mal clasificados en Operaciones~~ | Media | ✅ Resuelto |

---

## 8. Módulos Fuera de Operaciones que Requieren Atención

Detectados al analizar rutas y que no están en ningún grupo del sidebar o están mal ubicados:

| Módulo | Ruta | Sidebar |
|--------|------|---------|
| `PlotEnvironments` | `viticulturist.plot-environments.*` | ❌ No aparece |
| `Phenology` | `viticulturist.phenology.*` | ❌ No aparece (debería estar bajo Plantaciones) |
| Containers standalone | `viticulturist.containers.*` | ⚠️ No aparece directamente |

---

*Documento generado automáticamente el 2026-03-03. Actualizar al completar cada fase.*
