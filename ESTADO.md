# 📍 ESTADO — punto de retomada

> Última actualización: **2026-06-14**. Rama: `staging` (todo pusheado a `origin/staging`).
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

## 🔜 Pendiente (por prioridad sugerida)
1. **Clientes — afinar** (opcional, bajo riesgo): el listado compartido no expone metadata SEO
   (title/description) que sí tenía viticultor; valorar si se quiere recuperar.
2. **FormRequests resto de la API** — extender el patrón de Fase 1 a `NotebookController`,
   `SilicieController`, `Container...` (validación inline → FormRequests). *(Fase 1, pendiente)*
3. **Auditar listados** vs `docs/patron-vista-listado.md` (conformidad de las vistas). *(Fase 3)*
4. **Stock unificado** — `UnifiedStockService` + estrategias (Harvest/Container/ProductLot).
   Lo más crítico (inventario) y lo más profundo. *(carril B-core)*
5. **`User.role` vs `Organization`** — decidir fuente de verdad única y migrar. *(deuda estructural)*
6. **Deuda PHPStan legacy** — ir reduciendo los ~3115 errores del baseline poco a poco
   (objetivo: 0 nuevos sin baselinear; el legacy se baja por lotes).

## Convenciones del proyecto (recordatorio)
- Verificar tests **en aislamiento** (flakiness de seeds en paralelo).
- Unificar por **adopción de componentes/servicios compartidos role-aware**, no por herencia.
- Commits a nombre del usuario.
