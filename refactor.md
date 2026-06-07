# Refactor Plan — Agro 360 Web

## Contexto

Proyecto Laravel con 5 roles (`admin`, `viticulturist`, `winery`, `producer`, `supervisor`).
El análisis inicial detectó duplicación en controladores API, servicios de stock, y dashboards.
El refactor se hizo en 4 fases, ajustando el plan cuando la realidad del código era mejor (o más compleja) de lo esperado.

---

## Fase 1 — BaseApiController ✅

**Objetivo:** Estandarizar respuestas JSON y eliminar boilerplate CRUD en ~100 controladores API.

**Qué se hizo:**

Creado `app/Http/Controllers/Api/BaseApiController.php` con 5 helpers:

| Helper | Reemplaza |
|--------|-----------|
| `success($data)` | `response()->json(['data' => $data])` |
| `created($data)` | `response()->json(['data' => $data], 201)` |
| `deleted($message)` | `response()->json(['message' => $message])` tras un delete |
| `paginationMeta($paginator, $extra)` | Bloque `meta` manual con total/per_page/current_page/last_page/has_more |
| `paginated($paginator, $data, $extraMeta)` | Respuesta paginada completa en una línea |

**Alcance:**
- 125 archivos migrados: `extends Controller` → `extends BaseApiController`
- Viticulturist: 72 llamadas a helpers en 51 controladores
- Winery: migración completa en 24+ controladores
- Admin + Supervisor: ~14 cambios en 7 controladores
- Raíz Api: 7 cambios en 5 controladores
- Producer: sin cambios — todas sus respuestas son agregados complejos con claves no estándar (correcto)

**Qué se dejó sin migrar (correcto):**
- Respuestas que combinan `data` + `message` en el mismo JSON — no encajan en `created()`
- Respuestas con claves propias en la raíz (`campaign`, `stats`, `plots`…) — son agregaciones, no CRUD
- `AuthController` — sus respuestas llevan `token`, `expires_in`, `user`, etc.

---

## Fase 2 — Producer: duplicación real ✅

**Objetivo:** Extraer la lógica duplicada concreta dentro del módulo Producer.

**Descubrimiento:** Producer estaba mejor de lo esperado. Las rutas ya reutilizan componentes de
Viticulturist y Winery directamente. Los 8 controladores API son agregadores únicos. El 80% es
genuinamente Producer-specific. La duplicación real era solo entre `Dashboard.php` y `DashboardController.php`.

**Qué se hizo:**

Creado `app/Services/ProducerDashboardService.php`:
- Centraliza las 10+ queries de agregados (campo + bodega + contenedores + alertas de rendimiento)
- `DashboardController` reducido de 142 → 14 líneas (llama al servicio + aplica cache)
- `Dashboard.php` Livewire usa el servicio para stats; mantiene solo las queries de recientes (UI-only)

**Qué se aplazó:**
- `InvoiceStockService` — `Create.php` + `Edit.php` suman ~1400 líneas de lógica de stock de facturas.
  Alta complejidad, alto riesgo de rotura. No se toca hasta tener cobertura de tests.

---

## Fase 3 — Stock services ✅

**Objetivo:** Unificar los 4 servicios de stock con una base común.

**Descubrimiento:** Un `BaseStockService` genérico habría sido sobre-ingeniería. Los servicios son
arquitecturalmente distintos: usan ledgers distintos (`HarvestStock` vs `InvoiceStockMovement`),
semánticas distintas (tres cubos vs volumen físico), y sirven callers distintos.

**Qué se hizo:**

`WineContainerStockService` — el patrón `updateX = revertX + recordX` se repetía 3 veces idéntico
(`updateTransfer`, `updateLoss`, `updateBottling`). Extraído a `revertAndApply(callable, callable)`.
Los 3 métodos quedaron reducidos a 4 líneas cada uno.

**Qué se dejó sin tocar (correcto):**
- `ContainerStockService` y `HarvestStockService` comparten la tabla `HarvestStock` pero sirven
  dominios distintos (bodega vs viticultor). Fusionarlos rompería la separación.
- `ProductStockService` — ya era limpio y mínimo, sin duplicación interna.

---

## Fase 4 — Dashboards ✅

**Objetivo:** Sistema de widgets compartidos para los 5 dashboards.

**Descubrimiento:** Un `WidgetRegistry` genérico sería sobre-ingeniería. Los dashboards son tan
distintos en dominio que forzar una abstracción común añadiría indirección sin reducir código real.

| Dashboard | Situación final |
|-----------|----------------|
| Admin (369 líneas) | SaaS metrics con raw SQL y caching propio. No se toca. |
| Viticulturist (194 líneas) | Ya usaba `#[Computed]` properties — patrón óptimo. |
| Winery (173 líneas) | **Migrado a `#[Computed]`** — ver detalle abajo. |
| Supervisor (114 líneas) | Corto y focalizado en su dominio. No se toca. |
| Producer (90 líneas) | Delegado a `ProducerDashboardService` en Fase 2. |

**Winery Dashboard — qué se hizo:**
- 173 líneas de queries inline en `render()` → 20 `#[Computed]` properties
- `render()` reducido a 3 líneas (igual que Viticulturist)
- `ContainerMaintenance`: de FQCN inline a import en cabecera
- `containerUsage`: de 2 queries con clone → 1 `selectRaw`
- `yieldAlerts`: loop de forecasts centralizado en una computed que devuelve `[exceeded, at_risk]`

---

## Qué no se unifica (decisión deliberada)

- Flujos de bodega (fermentación, embotellado, etiquetado) — dominios exclusivos de Winery
- Cuaderno de campo del viticultor (SIGPAC, fitosanitarios, PAC) — dominio exclusivo de Viticulturist
- Panel del Supervisor/DO — poco solapamiento real con el resto
- Modelos de dominio — ya bien separados
- `InvoiceStockService` — aplazado hasta tener tests

---

## Resumen de impacto

| Fase | Archivos tocados | Resultado principal |
|------|-----------------|---------------------|
| 1 | 125+ controladores | Respuestas API estandarizadas, boilerplate eliminado |
| 2 | 3 archivos | `ProducerDashboardService` — queries centralizadas |
| 3 | 1 archivo | `revertAndApply` — 3 métodos update unificados |
| 4 | 1 archivo | Winery Dashboard migrado a `#[Computed]` |
