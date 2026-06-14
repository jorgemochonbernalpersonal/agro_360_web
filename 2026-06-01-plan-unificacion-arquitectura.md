# 📌 Auditoría backend + Plan de unificación de arquitectura

**Fecha:** 2026-06-01 · **Actualizado:** 2026-06-14
**Estado:** críticos de seguridad cerrados. Carriles ejecutados: **Facturación** (Fase 2),
**Listados** (`WithListing`) y **Clientes** (compartidos) — todos en `staging`/pusheados.
Pendiente: stock unificado, FormRequests del resto de la API, decidir `User.role` vs `Organization`.
Resumen vivo en `ESTADO.md`.

---

## 1. Resumen de la auditoría del backend

Stack: Laravel 12 + Livewire 3 + Flux. Volumen: 171 modelos, ~120 controladores API,
**425 componentes Livewire**, 15 servicios + 23 de RemoteSensing, 379 migraciones, 377 tests.

**Veredicto:** ~7/10. Buena estructura de capas pero **ejecución inconsistente**. No necesita
reescritura. El patrón se repite en cada capa: las herramientas correctas existen pero solo se
usan a medias.

### 4 críticos detectados
1. **Policies faltantes** (Harvest/Wine/Container/Invoice) — defensa en profundidad. → HECHO (ver §2).
2. **Enforcement de permisos disperso** — la API usa `abort_unless` repetido en 13+ controladores;
   Livewire usa `abort_if` inline; no se usan Policies en la API. Parcial.
3. **Sin FormRequests** — validación 100% inline, duplicada API/Livewire; controladores gordos
   (`NotebookController` 444, `SilicieController` 438). Sin empezar.
4. **CI no protege calidad** — el workflow solo corre tests. `phpstan`/`larastan` **no está instalado**
   (el `phpstan.neon` incluye un `phpstan-baseline.neon` inexistente → ni corre). `pint` sí está
   instalado pero no corre en CI. API casi sin tests (6 en total, **0 de facturación**). Sin empezar.

### Hallazgo de seguridad importante
El "IDOR crítico" del informe inicial **estaba exagerado**: los componentes route-bound
(`/{wine}`, `/{container}`, `/{harvest}`, `/{invoice}`) **sí** validan ownership en `mount()`
(`abort_if user_id/winery_id`). El único hueco REAL explotable era `harvest_id` en facturas del
Producer (venía del estado del cliente sin revalidar). Ya arreglado.

---

## 2. Lo HECHO en esta sesión (rama `fix/producer-invoice-harvest-ownership`)

### Fase A — Hueco real de facturación (commit `2341b138`)
- En `Producer/Invoices/Create.php:488` y `Edit.php:826` el `harvest_id` de cada línea venía del
  estado Livewire del cliente y se usaba sin revalidar ownership → permitía reservar stock / facturar
  la cosecha de otro viticultor inyectando un id ajeno. (La rama `wine_lot_id` ya estaba scopeada.)
- **Fix:** scopeo `Harvest::find` por `whereHas('activity', viticulturist_id === Auth::id())`; si no
  pertenece, lanza y la transacción revierte (mismo idioma que `HarvestSale`).
- Test nuevo `test_producer_cannot_invoice_another_viticulturists_harvest`. 8/8 pasan.
- **Nota:** `HarvestSale` (Create:132 / Edit:174) y `GrapePurchase` (Create:50 scopeado, Edit:182/189)
  ya estaban guardados. El informe inicial los marcó como sospechosos por error.

### Fase B — 4 Policies como defensa en profundidad (commit `ef226caa`)
- `HarvestPolicy`, `WinePolicy`, `ContainerPolicy`, `InvoicePolicy`.
  - Wine/Container/Invoice → ownership por `user_id`.
  - Harvest → visibilidad dual: bodega (`winery_id`) + viticultor (`activity.viticulturist_id`) +
    supervisor (read-only, sobre sus bodegas).
- Test `CellarOwnershipPolicyTest` (5/5 pasan, 22 assertions).
- ⚠️ **El array `$policies` de `AppServiceProvider` está MUERTO** (no hay `registerPolicies()` que lo
  lea). Las policies funcionan por **auto-descubrimiento de convención** (`Modelo` → `ModeloPolicy`).
  Se añadieron al array igual por consistencia, pero es inerte.

### Pendiente inmediato de la parte de seguridad
- Reemplazar los `abort_if($x->user_id !== Auth::id())` inline de los `mount()` por
  `$this->authorize()` usando las nuevas Policies (consistencia).
- Usar Policies en la **API** (hoy `abort_unless`). Esto es el crítico #2/#3.

---

## 3. El problema de fondo: duplicación cross-rol

Cada ROL (Viticulturist / Winery / Producer / Supervisor / Admin) **reimplementa los mismos
conceptos por separado**, con lógica copia-pegada que diverge. El bug de seguridad de Fase A fue un
síntoma directo: una de las 3 copias del flujo de factura se olvidó una validación.

> **Insight clave:** la infraestructura para unificar **YA EXISTE**, solo que casi nadie la usa.
> Esto no es un problema de diseño (los patrones ya están inventados) sino de **adopción/migración**
> → mucho menos arriesgado que partir de cero. Es la línea que ya empezaste con `WithListing`.

### Infra canónica existente (verificada)
- `app/Livewire/Shared/`: `AbstractCreate.php`, `AbstractEdit.php`, `AbstractIndex.php`.
- `app/Livewire/Concerns/` (11 traits): `WithListing`, `WithInvoiceActions`, `WithHarvestSaleStock`,
  `WithOwnershipRules`, `WithReadOnlyGuard`, `WithRoleAwareRedirect`, `WithRoleBasedFields`,
  `WithToastNotifications`, `WithUserFilters`, `WithUserPreferences`, `WithViticulturistValidation` (deprecated).
- **Adopción real estimada: 5-15%** de los 425 componentes. El resto reimplementa a mano.

### Componentes Livewire por rol (verificado)
Viticulturist **218** · Winery **145** · Supervisor 25 · Admin 20 · Producer 12 · Clients 5 · Auth 7 · Sigpac 5.

### Conceptos más duplicados (del mapeo)
| Concepto | Implementaciones paralelas | Similitud | Riesgo si hay bug |
|---|---|---|---|
| **Facturación** | `Producer/Invoices`, `Viticulturist/Invoices`, `Viticulturist/Billing/HarvestSale`, `Winery/Billing/GrapePurchase`, `Winery/Billing/ProductSale` | 85-95% skeleton | **Dinero** |
| **Stock/Inventario** | `ContainerStockService`, `HarvestStockService`, `ProductStockService`, `WithHarvestSaleStock` (trait) | 70% | **Inventario** |
| **Listados (Index)** | ~50-60 componentes | 90% boilerplate | Bajo |
| **Clientes** | `Viticulturist/Clients` vs `Winery/Clients` | 95% idéntico | Bajo |
| **Validaciones** | reglas repetidas en ~30 sitios | 85% | Bajo |

4 tipos de factura reales: `producer_sale`, `harvest_sale`, `grape_purchase`, `wine_sale`.

---

## 4. Plan de unificación — decidir por dónde arrancar

Es un programa de **semanas**, por concepto, en **microcommits**, **verificando en aislamiento**
(`php artisan test <fichero>` — ojo flakiness de seeds en paralelo, ver §5). Dos carriles:

### Carril A — Listados (bajo riesgo, alto volumen, ya empezado)
Adoptar `WithListing`/`Shared/AbstractIndex` en los ~50-60 Index restantes, por dominios:
`viticulturist/` → `winery/` → `supervisor/` → `producer/`+`admin/`.
Receta de migración ya documentada en `2026-05-31-importante.md` (-38/+6 líneas por componente).

### Carril B — Facturación + Stock (alto valor, alto riesgo, tests-first)
1. **Tests de caracterización** de los 4 flujos de factura ANTES de tocarlos (red de seguridad).
2. Extraer `App\Services\InvoiceService`: `createInvoice()`, `calculateTotals()`, `generateInvoiceNumber()`,
   `validateInvoiceData()`, guard de ownership.
3. `BaseInvoiceCreate` / `BaseInvoiceEdit` (heredan; cada rol solo define su `InvoiceType` + UI).
4. Migrar los 4 flujos uno a uno.
5. (Más profundo) Unificar los 4 servicios de stock en `UnifiedStockService` + `StockStrategy`
   (Harvest/Container/ProductLot). El más crítico (inventario) y el más arriesgado.

### Menú para retomar (elegir 1 como primer concepto)
- [x] **Facturación (tests-first)** — carril B. ✅ HECHO: red de tests (Fase 1) + 5 flujos
      consolidados en `InvoiceService` (Fase 2, 2026-06-14). Sin clases base. Ver `mejoras.md` §Fase 2.
- [x] **Listados (seguir WithListing)** — carril A. ✅ HECHO: rollout de `WithListing` completo
      (16 listados). Ver `2026-05-31-importante.md`.
- [ ] **Stock unificado** — carril B-core. Lo más crítico pero lo más profundo. PENDIENTE.
- [x] **Clientes** — ✅ HECHO (2026-06-14): los 3 roles usan `App\Livewire\Clients\*` (compartido,
      role-aware). Borrados 8 componentes duplicados (`Viticulturist\Clients\*` + `Winery\Clients\*`
      huérfano de rutas) y sus 8 vistas; −3435 líneas. Commit `25ec4878`.

> Recomendación: si se quiere máximo valor → **Facturación con tests-first**. Si se quiere validar el
> enfoque de unificación con riesgo mínimo antes de lo gordo → **Clientes** primero, luego Facturación.

---

## 5. Notas / aprendizajes (no perder)
- **No reescribir lo que funciona.** Unificar por adopción de patrones existentes, no rediseñando.
- **Flakiness de tests:** colisión de seeds en paralelo (`grape_varieties_code_unique` duplicate)
  provoca fallos masivos NO relacionados. **Verificar siempre en aislamiento.**
- **Commits a nombre del usuario, sin Co-Authored-By de Claude.**
- **PowerShell vs Bash:** al commitear desde el tool Bash, NO usar here-strings `@'...'@` de PowerShell
  (se cuela un `@` literal). Usar `printf` a un fichero + `git commit -F`.
- Memoria persistente actualizada: `project_auditoria_backend.md` (índice en `MEMORY.md`).
