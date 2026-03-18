# Supervisor → Winery: Plan de implementación
> Basado en el análisis de Vinai2 (supervisor ↔ seller) + especificación DO existente

---

## Contexto y equivalencias

En agro365 el rol `supervisor` es la **Denominación de Origen (DO)**. Gestiona bodegas adscritas y el pool de viticultores que asigna a esas bodegas.

| Vinai2 | Agro365 | Tabla/Modelo |
|--------|---------|-------------|
| `supervisor` | `User.role='supervisor'` | — |
| `seller` (bodega) | `User.role='winery'` | — |
| `supervisor_seller` | `supervisor_winery` | `SupervisorWinery` |
| viticultor de seller | pool de viticultores | `winery_viticulturist(source=supervisor)` |
| Ability → feature flag | pendiente | pendiente |
| SupervisorRequest | Inspección / Acta | pendiente |
| Chat | pendiente | pendiente |

---

## Estado actual en agro365

### Lo que YA existe (modelos + rutas + stubs)

```
MODELOS:
✓ SupervisorWinery          (supervisor_id, winery_id)
✓ SupervisorViticulturist   (supervisor_id, viticulturist_id)
✓ WineryViticulturist       (source=supervisor, supervisor_id, winery_id, viticulturist_id)

RUTAS (routes/supervisor.php):
✓ /supervisor/dashboard
✓ /supervisor/census
✓ /supervisor/growers                   ← Growers\Index (funciona)
✓ /supervisor/campaigns
✓ /supervisor/oversight/wineries        ← Oversight\Wineries\Index (funciona parcialmente)
✓ /supervisor/oversight/growers         ← Oversight\Growers\Index (funciona parcialmente)
✓ /supervisor/qualification             ← STUB vacío
✓ /supervisor/labels                    ← STUB vacío
✓ /supervisor/inspection                ← STUB vacío
✓ /supervisor/regulation                ← STUB vacío
✓ /supervisor/territory                 ← STUB vacío
✓ /supervisor/statistics                ← STUB vacío
✓ /supervisor/finance                   ← STUB vacío
✓ /supervisor/settings                  ← STUB vacío
```

### Lo que Oversight\Wineries\Index ya hace
- Lista bodegas del supervisor via `SupervisorWinery`
- KGs cosechados por bodega + nº recepciones (de `harvests`)
- Nº viticultores aportados por el supervisor a cada bodega
- Filtros: búsqueda, vintage
- **Falta**: ruta Show con dashboard por bodega individual

---

## Lo que hay que construir (por orden de prioridad)

---

### BLOQUE 1 — Dashboard de bodega individual
**Ruta nueva**: `GET /supervisor/oversight/wineries/{winery}` → `Oversight\Wineries\Show`

**Equivalente Vinai2**: `SupervisorSellerController.show($id)` — el panel más rico del sistema.

#### Datos a mostrar

```
HEADER
  Nombre bodega, email, dirección, logo
  Badge: activa / inactiva
  Nº viticultores aportados por esta DO
  Nº recepciones esta campaña
  Total kg esta campaña

PARCELAS Y VIÑEDO (viticultores del supervisor en esta bodega)
  Tabla: viticultor → nº parcelas → ha totales → kg límite → última actividad

RECEPCIONES (harvests donde winery_id = bodega)
  Últimas 10 recepciones: fecha, viticultor, kg, variedad, calidad
  Gráfica: kg por semana en campaña activa
  Desglose por variedad (kg + %)

VINOS Y BODEGA
  Nº lotes activos
  Nº contenedores
  Litros en bodega

SOLICITUDES/INSPECCIONES (futuro)
  Últimas 5 actuaciones del supervisor sobre esta bodega

ACCIONES RÁPIDAS
  [Toggle activo/inactivo]
  [Solicitar cuaderno de campo]   ← si queda pendiente
  [Crear inspección]              ← futuro
```

#### Implementación técnica

```php
// Livewire: app/Livewire/Supervisor/Oversight/Wineries/Show.php
// Ruta:     supervisor.oversight.wineries.show

public function mount(User $winery): void
{
    // Verificar que esta bodega pertenece al supervisor
    SupervisorWinery::where('supervisor_id', Auth::id())
        ->where('winery_id', $winery->id)
        ->firstOrFail();

    $this->winery = $winery;
}
```

---

### BLOQUE 2 — Toggle activo/inactivo de bodega
**Equivalente Vinai2**: `SupervisorSellerController.toggleActive()` → `user.active = 0|1`

#### Modelo de datos

Agro365 `users` tiene:
- `can_login` (boolean) — controla si puede iniciar sesión

El supervisor puede desactivar el acceso de una bodega poniendo `can_login = false`.

> **Diferencia con Vinai2**: Vinai2 usa `user.active`. Agro365 usa `can_login`.
> Mantener `can_login` — es el campo correcto y ya está en uso.

#### Flujo

```
Supervisor ve bodega en Oversight\Wineries\Index o Show
  → Botón "Desactivar acceso"
  → Confirmación modal
  → user.can_login = false
  → Toast: "Bodega desactivada. No podrá iniciar sesión."
  → Badge en Index cambia a "Sin acceso"

Reactivar:
  → Botón "Activar acceso"
  → user.can_login = true
```

#### Dónde implementarlo

- Método `toggleAccess(int $wineryId)` en `Oversight\Wineries\Index`
- También disponible en `Oversight\Wineries\Show`
- Guard: verificar `SupervisorWinery` antes de actuar

#### Consideraciones

- Solo afecta al login. Los datos no se borran.
- Si la bodega tiene role=producer, también se bloquea el acceso viticultor.
- Notificación opcional al email de la bodega.

---

### BLOQUE 3 — Asignación de bodegas (flujo completo)

Actualmente las bodegas se asignan al supervisor manualmente en BD. No hay UI.

**Equivalente Vinai2**: bidireccional — supervisor asigna O bodega solicita.

#### Opción A: Supervisor asigna bodega (vista del supervisor)

```
GET /supervisor/census → formulario de búsqueda
  Buscar bodega por nombre/email
  → Resultados: bodegas registradas NO asignadas a este supervisor
  → Botón "Asignar" → crea SupervisorWinery
```

#### Opción B: Bodega solicita supervisor

```
GET /winery/supervisor → página "Mi Denominación de Origen"
  Si no tiene supervisor: botón "Solicitar adscripción a DO"
    → Buscar DO por nombre
    → Envía solicitud (estado: pendiente)
  Si tiene supervisor: muestra datos de la DO, solicitudes pendientes
```

#### Modelo de datos necesario

La tabla `supervisor_winery` actual no tiene campo `status`. Para el flujo bidireccional:

```sql
-- Migración: add status to supervisor_winery
ALTER TABLE supervisor_winery
  ADD COLUMN status VARCHAR(20) DEFAULT 'active'
    COMMENT 'active | pending | rejected',
  ADD COLUMN requested_by VARCHAR(20) DEFAULT 'supervisor'
    COMMENT 'supervisor | winery',
  ADD COLUMN requested_at TIMESTAMP NULL,
  ADD COLUMN responded_at TIMESTAMP NULL;
```

> **Decisión de diseño**: Si el supervisor siempre es quien asigna (flujo Agro365 original),
> se puede omitir el campo status y hacer siempre asignación directa.
> Solo añadir status si se quiere el flujo bidireccional de Vinai2.

---

### BLOQUE 4 — Asignación de viticultores a bodegas (UI del supervisor)

El supervisor ya tiene viticultores en su pool (`supervisor_viticulturist`). Necesita poder asignarlos a bodegas concretas.

**Flujo actual**: se hace directamente en BD. No hay UI.

#### Flujo propuesto

```
Supervisor en Oversight\Wineries\Show (bodega X)
  → Sección "Viticultores asignados a esta bodega"
  → Botón "Asignar viticultor"
    → Modal: lista de viticultores del pool del supervisor
    → Selecciona uno o varios
    → Crea WineryViticulturist(
        winery_id=X,
        viticulturist_id=Y,
        source='supervisor',
        supervisor_id=Auth::id()
      )

Desasignar:
  → Botón "Retirar" en la fila
  → Elimina WineryViticulturist
  → NO elimina al viticultor del pool del supervisor
```

#### Método en Show.php

```php
public function assignViticulturist(int $viticulturistId): void
{
    // Verificar que el viticultor es del pool del supervisor
    SupervisorViticulturist::where('supervisor_id', Auth::id())
        ->where('viticulturist_id', $viticulturistId)
        ->firstOrFail();

    WineryViticulturist::firstOrCreate([
        'winery_id'        => $this->winery->id,
        'viticulturist_id' => $viticulturistId,
    ], [
        'source'        => WineryViticulturist::SOURCE_SUPERVISOR,
        'supervisor_id' => Auth::id(),
        'assigned_by'   => Auth::id(),
    ]);
}

public function unassignViticulturist(int $viticulturistId): void
{
    WineryViticulturist::where('supervisor_id', Auth::id())
        ->where('winery_id', $this->winery->id)
        ->where('viticulturist_id', $viticulturistId)
        ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
        ->delete();
}
```

---

### BLOQUE 5 — Sistema de habilidades (feature flags)
**Equivalente Vinai2**: `Ability` model + `user_abilities` pivot + `updateAbilitiesBulk()`

En Vinai2 el supervisor activa/desactiva módulos completos de la bodega (etiquetado, exportaciones, etc.).

En agro365 esto se traduce en: **la DO controla qué módulos están disponibles para cada bodega adscrita**.

#### Modelo de datos nuevo

```sql
-- Tabla de habilidades disponibles
CREATE TABLE abilities (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(50) NOT NULL UNIQUE,  -- 'cuaderno_campo', 'calificacion', etc.
  name        VARCHAR(100) NOT NULL,
  description TEXT,
  module      VARCHAR(50),  -- agrupación: 'viñedo', 'bodega', 'regulatorio'
  created_at  TIMESTAMP,
  updated_at  TIMESTAMP
);

-- Pivot: habilidades activas por usuario (bodega)
CREATE TABLE user_abilities (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NOT NULL,  -- FK users (winery)
  ability_id  BIGINT UNSIGNED NOT NULL,
  granted_by  BIGINT UNSIGNED,           -- FK users (supervisor)
  granted_at  TIMESTAMP,
  UNIQUE (user_id, ability_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (ability_id) REFERENCES abilities(id) ON DELETE CASCADE
);
```

#### Habilidades iniciales para bodegas

| code | Nombre | Módulo |
|------|--------|--------|
| `harvest_reception` | Recepciones de vendimia | bodega |
| `wine_process` | Elaboración de vinos | bodega |
| `cellar_management` | Gestión de bodega | bodega |
| `label_batches` | Lotes de etiquetas | bodega |
| `cuaderno_access` | Acceso cuadernos viticultores | regulatorio |
| `grape_purchase_invoice` | Facturas compra uva | facturación |
| `product_sales` | Ventas de producto | facturación |
| `veriFaktu` | VeriFactu (factura electrónica) | facturación |
| `yield_forecasts` | Previsiones de cosecha | viñedo |
| `quality_analysis` | Análisis de calidad | bodega |

#### UI en Oversight\Wineries\Show

```
Sección "Módulos activos"
  Listado de abilities con toggle switch
  Botón "Guardar cambios" → llama updateAbilities()

public function updateAbilities(array $abilityIds): void
{
    // Verificar que la bodega es de este supervisor
    // Borrar todas las user_abilities actuales
    // Insertar las nuevas
    UserAbility::where('user_id', $this->winery->id)->delete();
    foreach ($abilityIds as $id) {
        UserAbility::create([
            'user_id'    => $this->winery->id,
            'ability_id' => $id,
            'granted_by' => Auth::id(),
            'granted_at' => now(),
        ]);
    }
}
```

> **Nota**: El sistema de abilities es el más disruptivo porque requiere que
> la autorización en toda la app de winery compruebe abilities además del role.
> Implementar como **último bloque** para no romper lo existente.

---

### BLOQUE 6 — Sistema de solicitudes/inspecciones (operativa regulatoria)

**Equivalente Vinai2**: 8 tipos de `SupervisorRequest` con firma digital.

En Agro365 los módulos `Inspection`, `Qualification`, `Regulation`, `Labels` son stubs. Este bloque los materializa.

#### Tipos de solicitud para agro365

Simplificado respecto a Vinai2 — solo los relevantes para una DO vitivinícola española:

| Tipo | Descripción | Iniciado por |
|------|-------------|-------------|
| `inspection` | Inspección de parcela/bodega | Supervisor |
| `label_request` | Solicitud de contraetiquetas | Bodega |
| `qualification` | Calificación de lote de vino | Bodega → Supervisor aprueba |
| `nonconformity` | Acta de no conformidad | Supervisor |
| `harvest_declaration` | Declaración de cosecha | Bodega → Supervisor aprueba |
| `certification` | Certificación ecológica/IGP | Bodega → Supervisor aprueba |

#### Modelo de datos

```sql
CREATE TABLE supervisor_requests (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supervisor_id    BIGINT UNSIGNED NOT NULL,  -- FK users (DO)
  winery_id        BIGINT UNSIGNED NOT NULL,  -- FK users (bodega)
  type             VARCHAR(50) NOT NULL,       -- 'inspection' | 'label_request' | ...
  status           VARCHAR(20) DEFAULT 'draft',
    -- draft | pending | in_review | approved | rejected | archived
  title            VARCHAR(255),
  notes            TEXT,
  response_notes   TEXT,
  -- Firma digital
  supervisor_signed_at  TIMESTAMP NULL,
  winery_signed_at      TIMESTAMP NULL,
  -- Fechas de flujo
  sent_at          TIMESTAMP NULL,
  responded_at     TIMESTAMP NULL,
  resolved_at      TIMESTAMP NULL,
  created_at       TIMESTAMP,
  updated_at       TIMESTAMP,
  FOREIGN KEY (supervisor_id) REFERENCES users(id),
  FOREIGN KEY (winery_id) REFERENCES users(id)
);

CREATE TABLE supervisor_request_documents (
  id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supervisor_request_id BIGINT UNSIGNED NOT NULL,
  uploaded_by           BIGINT UNSIGNED NOT NULL,
  file_path             VARCHAR(500) NOT NULL,
  file_name             VARCHAR(255),
  created_at            TIMESTAMP,
  FOREIGN KEY (supervisor_request_id) REFERENCES supervisor_requests(id) ON DELETE CASCADE
);
```

#### Flujo de estados

```
[Supervisor crea]
       ↓
    draft
       ↓  [Supervisor envía]
    pending  ←───────────────── bodega recibe notificación
       ↓  [Bodega responde]
   in_review ←──────────────── supervisor recibe notificación
       ↓  [Supervisor resuelve]
  approved / rejected
       ↓  [Ambos firman o se archiva]
    archived
```

---

### BLOQUE 7 — Vista del lado bodega: "Mi Denominación de Origen"

La bodega necesita ver quién es su supervisor y gestionar la relación.

#### Ruta nueva

```
GET /winery/denomination → Winery\Denomination\Index
```

#### Contenido

```
HEADER
  Logo DO, nombre, contacto
  Estado de adscripción: activa / pendiente / sin DO

ACCIONES RÁPIDAS (si está adscrita)
  Solicitudes pendientes de respuesta (badge contador)
  Solicitudes de cuaderno pendientes (para sus viticultores)

VITICULTORES ASIGNADOS POR LA DO
  Lista: nombre, parcelas, ha, último cuaderno
  (solo lectura — los gestiona la DO)

HISTORIAL DE SOLICITUDES
  Tabla: tipo, fecha, estado, acciones

MÓDULOS ACTIVOS (abilities)
  Vista de los módulos que la DO ha habilitado
```

---

## Orden de implementación (sprints)

### Sprint 1 — Show de bodega individual
**Objetivo**: El supervisor puede ver el dashboard completo de una bodega.
- Ruta: `supervisor.oversight.wineries.show`
- Componente: `Oversight\Wineries\Show`
- Datos: recepciones, viticultores asignados, vinos, contenedores
- **Sin nuevas migraciones** — solo lectura de datos existentes
- Tests: IndexTest (show renders, only supervisor's wineries visible)

### Sprint 2 — Toggle activo/inactivo
**Objetivo**: El supervisor puede bloquear/desbloquear el acceso de una bodega.
- Método `toggleAccess()` en Show y en Index
- Notificación por email a la bodega
- **Sin nuevas migraciones** — usa `users.can_login`
- Tests: supervisor activa, supervisor desactiva, otro supervisor no puede

### Sprint 3 — Asignación de viticultores desde Show
**Objetivo**: El supervisor asigna/retira viticultores de su pool a bodegas concretas.
- Modal de selección en `Oversight\Wineries\Show`
- Métodos: `assignViticulturist()`, `unassignViticulturist()`
- Guard: viticultor debe pertenecer al pool del supervisor
- Tests: asignación correcta, intento con viticultor ajeno, desasignación

### Sprint 4 — Vista bodega: "Mi Denominación de Origen"
**Objetivo**: La bodega ve su DO, solicitudes pendientes, viticultores asignados.
- Ruta: `/winery/denomination`
- Componente: `Winery\Denomination\Index`
- Solo lectura inicial (sin flujo de solicitud aún)
- Tests: renders correctamente, datos aislados por bodega

### Sprint 5 — Asignación de bodegas (UI supervisor)
**Objetivo**: El supervisor puede buscar y asignar bodegas desde la app.
- Subcomponente de búsqueda en Census o nueva ruta en Oversight
- Crear/eliminar `SupervisorWinery`
- Decidir si se necesita status (pendiente/activo) en la tabla
- Migración si se añade status

### Sprint 6 — Sistema de solicitudes básico
**Objetivo**: Supervisor crea inspecciones. Bodega responde.
- Nuevas migraciones: `supervisor_requests`, `supervisor_request_documents`
- Componentes: `Supervisor\Inspection\Create`, `Winery\Denomination\Requests\Index`
- Flujo: draft → pending → in_review → approved/rejected
- Notificaciones en ambas direcciones
- Tests: flujo de estados, autorización

### Sprint 7 — Sistema de habilidades (abilities)
**Objetivo**: Supervisor controla qué módulos tiene activos cada bodega.
- Nuevas migraciones: `abilities`, `user_abilities`
- Seeder de abilities iniciales
- UI en Oversight\Wineries\Show: toggle switches por módulo
- Integración en middleware/gates de la app winery (último — más disruptivo)
- Tests: asignación de abilities, bodega sin ability no accede al módulo

---

## Decisiones pendientes antes de empezar

| Decisión | Opción A | Opción B |
|----------|----------|----------|
| Asignación bodega | Solo supervisor asigna | Bidireccional (bodega puede solicitar) |
| Status en supervisor_winery | No (siempre activo) | Sí (pending/active/rejected) |
| Toggle activo | `can_login` | Campo nuevo `supervisor_active` |
| Abilities | Implementar desde S7 | Posponer indefinidamente |
| Chat supervisor↔bodega | Posponer | Incluir en S6 |
| Firma digital en solicitudes | Imagen/firma dibujada | Solo texto + fecha |

---

## Archivos a crear por sprint

### Sprint 1
```
app/Livewire/Supervisor/Oversight/Wineries/Show.php
resources/views/livewire/supervisor/oversight/wineries/show.blade.php
tests/Feature/Supervisor/Oversight/Wineries/ShowTest.php
```

### Sprint 2
```
[Modificar] app/Livewire/Supervisor/Oversight/Wineries/Index.php
[Modificar] app/Livewire/Supervisor/Oversight/Wineries/Show.php
app/Notifications/WineryAccessToggledNotification.php
tests/Feature/Supervisor/Oversight/Wineries/ToggleAccessTest.php
```

### Sprint 3
```
[Modificar] app/Livewire/Supervisor/Oversight/Wineries/Show.php
tests/Feature/Supervisor/Oversight/Wineries/ViticulturistAssignmentTest.php
```

### Sprint 4
```
app/Livewire/Winery/Denomination/Index.php
resources/views/livewire/winery/denomination/index.blade.php
[Modificar] routes/winery.php
tests/Feature/Winery/Denomination/IndexTest.php
```

### Sprint 5
```
app/Livewire/Supervisor/Census/AssignWinery.php  (o modal en Census\Index)
tests/Feature/Supervisor/Census/AssignWineryTest.php
```

### Sprint 6
```
database/migrations/xxxx_create_supervisor_requests_table.php
database/migrations/xxxx_create_supervisor_request_documents_table.php
app/Models/SupervisorRequest.php
app/Models/SupervisorRequestDocument.php
app/Livewire/Supervisor/Inspection/Create.php
app/Livewire/Supervisor/Inspection/Show.php
app/Livewire/Winery/Denomination/Requests/Index.php
app/Livewire/Winery/Denomination/Requests/Show.php
app/Notifications/SupervisorRequestCreatedNotification.php
app/Notifications/SupervisorRequestRespondedNotification.php
tests/Feature/Supervisor/Inspection/...
tests/Feature/Winery/Denomination/Requests/...
```

### Sprint 7
```
database/migrations/xxxx_create_abilities_table.php
database/migrations/xxxx_create_user_abilities_table.php
app/Models/Ability.php
app/Models/UserAbility.php
database/seeders/AbilitySeeder.php
[Modificar] app/Livewire/Supervisor/Oversight/Wineries/Show.php
tests/Feature/Supervisor/Abilities/...
```
