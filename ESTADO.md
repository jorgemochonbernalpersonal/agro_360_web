# 📍 ESTADO — punto de retomada

> Última actualización: **2026-06-15**. Rama: `staging`. Main actualizado con merge de 41 commits.
> Este fichero es el índice vivo: resume qué está hecho y qué sigue. El detalle está en los
> docs enlazados.

## Docs de referencia
- [`mejoras.md`](mejoras.md) — auditoría maestra + roadmap por fases + registro de progreso.
- [`2026-06-01-plan-unificacion-arquitectura.md`](2026-06-01-plan-unificacion-arquitectura.md) — auditoría backend + carriles de unificación.
- [`2026-05-31-importante.md`](2026-05-31-importante.md) — detalle de listados (`WithListing`) y limpieza de componentes.
- [`docs/patron-vista-listado.md`](docs/patron-vista-listado.md) — patrón canónico de vista de listado.
- `ui.md` — auditoría de UI / design system.

## ✅ Hecho
- **Fase 0** — regla "D.O. ≥1 winery", PHPStan + Pint en CI (baseline congelado).
- **Fase 1** — facturación: red de 44 tests de caracterización + Policies + FormRequests.
- **Fase 2 — facturación unificada** (2026-06-14): los 5 flujos consolidados en `InvoiceService`
  (totales de cabecera + línea VAT/IRPF, UI en vivo, numeración, ownership, datos de formulario).
  Sin clases base (descartadas: los flujos divergen demasiado).
- **Listados** — rollout de `WithListing` completo (16 listados; candidatos limpios agotados).
- **Clientes** — unificados en `App\Livewire\Clients\*` (role-aware) para los 3 roles;
  borrados 8 componentes duplicados + 8 vistas (−3435 líneas).
- **CI / PHPStan** — vuelto a verde; baseline regenerado (~3115 errores legacy congelados).
  `phpstan analyse` completo: 0 errores nuevos.
- **Tests ownership viticulturist** (2026-06-15): FinancialStats, ShowHarvest, TreatmentIndex,
  Teledetección (smoke + ownership PlotAnalysis + exports), Meteorología (smoke + IDOR plot ajeno),
  PAC/Normativa (Payments ownership + smoke dashboards). ~40 tests nuevos.
- **UI Fase 2 — paleta agro** (2026-06-15): 14 lotes, ~120 cambios en ~50 ficheros Blade.
  Clases `green-*/emerald-*` estructurales migradas a `agro-*`; semánticas/temáticas/categóricas conservadas.
  Reglas de clasificación consolidadas en `ui.md`.

## ✅ Hecho (continuación)
- **FormRequests API** (2026-06-15): 18 FormRequests nuevos centralizan `authorize()` + `rules()`
  en 7 controladores (`NotebookController`, `SilicieController`, `ContainerController`,
  `ContainerRoomController`, `ContainerStockEntryController`, `ContainerMaintenanceController`,
  `ContainerReturnController`). Incluye reglas dinámicas por `activity_type` en Notebook
  (con `prepareForValidation`). PHPStan: 0 errores nuevos. 88 tests verdes.
- **UnifiedStockService** (2026-06-15): Nuevo servicio centraliza el doble despacho
  harvest/wine_lot en `Producer/Invoices/Create` y `Edit`. Elimina HarvestStockService
  (código muerto, reemplazado por ContainerStockService desde el origen del proyecto).
  Baseline PHPStan: 3096 errores (−19). 292 tests Invoice + 54 stock: verdes.

## ✅ Hecho (continuación)
- **Tests FormRequests API** (2026-06-15): 37 tests nuevos en 4 ficheros cubren Notebook
  (storeTyped por slug, alias buds_per_vine, indexOfType, UpdateNotebookRequest dinámico),
  Container (store, archive, ContainerRoom CRUD, ContainerStockEntry, ContainerMaintenance),
  Silicie (auth, vintage/fiscal_year validation, smoke 6 endpoints) y ContainerReturn
  (auth, aislamiento, todos los campos required + enums). Commit `272b48fd`.
- **Decisión `User.role` vs `Organization`** (2026-06-15): `role` + pivotes son la verdad de
  identidad y jerarquía (500+ referencias, todos los middleware). `Organization` se reduce a
  **contenedor de metadatos fiscales** (INFOVI/SILICIE). `ViticulturistAssignment` era
  write-only (nadie la lee en producción) → se elimina el dual-write de
  `WineryViticulturistObserver` y el modelo/tabla. `UserObserver` se conserva (autocrea
  Organization con metadatos al registrar winery/DO/producer). Commit `eb36cec1`.
- **Limpieza abort_unless redundantes en API** (2026-06-15): 294 líneas eliminadas en 99
  controllers (Supervisor ×5, Admin ×14, Viticulturist ×98, Winery ×177). Todos eran
  chequeos de rol ya cubiertos por el middleware de grupo (`api.role:*`). Se conservan los
  `abort_if(isReadOnlyAdmin)` de Admin (sub-rol no cubierto por middleware) y los
  `abort_if` de validaciones de negocio en Winery (estado factura, integridad referencial).

## 🔜 Pendiente (por prioridad sugerida)
1. **Clientes — afinar** (opcional, bajo riesgo): el listado compartido no expone metadata SEO
   (title/description) que sí tenía viticultor; valorar si se quiere recuperar.
2. **UI Fase 3** — tarjetas y botones inline → componentes `<x-agro.*>`. Sin urgencia.
3. **Deuda PHPStan legacy** — ir reduciendo los ~3095 errores del baseline poco a poco.

## Convenciones del proyecto (recordatorio)
- Verificar tests **en aislamiento** (flakiness de seeds en paralelo).
- Unificar por **adopción de componentes/servicios compartidos role-aware**, no por herencia.
- Commits a nombre del usuario.
