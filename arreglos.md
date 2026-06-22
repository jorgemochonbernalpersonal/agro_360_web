# Arreglos — seguimiento (punto de retomada)

> Auditoría del 2026-06-21. Rama: `staging`. Informe completo en `AUDITORIA-2026-06-21.md`.
> Este fichero = qué está hecho y qué sigue para retomar la sesión "ataca todos".

## ✅ HECHO (sesión 1 — ya en working tree, SIN commit)

### Rendimiento — 9 N+1 eliminados (eager-loads aditivos, bajo riesgo)
- `app/Livewire/Viticulturist/DigitalNotebook/AbstractActivityIndex.php:219` → `+'campaign'` (afecta 7 pantallas)
- `app/Livewire/Winery/Announcements/Index.php:96` + blade `:23` → `withCount('viticulturists')` → `viticulturists_count`
- `app/Livewire/Admin/Organizations/Index.php:266` → `+'parent'`
- `app/Livewire/Producer/IntegratedEstate/PlotTable.php:42` → `+'municipality:id,name'`
- `app/Livewire/Viticulturist/PlannedWorks/Index.php:108` → `+'plot','campaign'`
- `app/Livewire/Viticulturist/HarvestByproducts/Index.php:81` → `+'campaign'`
- `app/Livewire/Viticulturist/FertilizationPlans/Index.php:78` → `+'campaign'`
- `app/Livewire/Viticulturist/AdvisoryMemberships/Index.php:31` → `+'campaign'`
- `app/Services/Reports/Generators/{Phytosanitary,FullNotebook}ReportGenerator.php` → `+'plot.sigpacCodes','plot.sigpacUses'`

### Bug corregido
- `resources/views/public/wine-trace.blade.php:86` → relación inexistente `plotPlanting->plotVariety`
  cambiada a `plotPlanting?->grapeVariety?->name`

### Código muerto borrado (14 ficheros, `git rm`, reversible)
- `reset_demo_vit.php` (reseteaba pass demo en claro)
- `app/Services/Cache/{QueryCacheService,CampaignCacheService,PlotCacheService}.php`
- `app/Services/ViticulturistCacheService.php`, `app/Services/AuditService.php`
- `app/Services/RemoteSensing/{NdviCalculator,PhenologyService}.php`
- `app/Services/RemoteSensing/Calculators/{IrrigationCalculator,PhenologyCalculator}.php`
- `app/Services/RemoteSensing/Generators/RecommendationGenerator.php`
- 3 tests: `tests/Unit/Services/{AuditServiceTest,Cache/CampaignCacheServiceTest,Cache/PlotCacheServiceTest}.php`

**Estado validación:** `php -l` OK en todos, `composer dump-autoload` OK. Tests NO ejecutados aún.

## 🧪 Infraestructura de tests (DESCUBIERTO esta sesión)
- La suite necesita **MariaDB en contenedor** (no la BD de dev).
- Docker Desktop: arrancar con `"C:\Program Files\Docker\Docker\Docker Desktop.exe"` (tarda ~1 min).
- Test DB: `docker compose up -d mariadb_test` → contenedor `agro365_mariadb_test`, puerto **3308**, db `agro365_test`.
- Comprobar lista: `docker exec agro365_mariadb_test mariadb -uagro365 -ppassword -e "SELECT 1" agro365_test`
- Ejecutar: `php artisan test --env=testing` (lee `.env.testing` → DB_PORT=3308, DB_DATABASE=agro365_test).
- `tests/bootstrap.php` reconstruye la BD de test al arrancar (solo permite wipear `agro365_test`).
- ⚠️ PENDIENTE: correr la suite completa para fijar **baseline verde** ANTES de seguir refactorizando.

## ⏳ PENDIENTE — "ataca todos" (orden sugerido por ROI/riesgo)

### A — Discretos seguros
- [ ] `WineryAnnouncement.php:45` → cambiar `$guarded = []` por `$fillable` explícito (revisar flujos de creación).
- [ ] `session()->flash` → toast: `Viticulturist/CueExports/Edit.php:31`, `Sigpac/Create.php:322,327`.
- [ ] `Producer/Harvest/PromoteToReception.php:83` → `abort_unless($user->isProducer(),403)` a Policy.
- [ ] Migración `add_missing_indexes`: `container_states`/`container_current_states`/`container_histories`
      (`wine_id`,`wine_process_detail_id`,`external_grape_id`); `security_events.admin_id`,
      `notification_logs.admin_id`; `subscriptions.status`, `payments.status`,
      `invoices.delivery_status`, `invoices.sif_status`.

### B — PHPStan quick-wins (5) — OJO: quitar también su entrada de `phpstan-baseline.neon`
- [ ] 3× `method.notFound` scopes (`forWinery`,`byType`,`inRiskPeriod`) → anotar `@method` en modelo
      (patrón: `app/Models/Builders/PlotQueryBuilder.php`). Ficheros: `Viticulturist/Personal/Index.php`,
      `Viticulturist/PestManagement/Index.php` (x2).
- [ ] 1× `booleanAnd.rightAlwaysTrue` → `Viticulturist/Harvests/Index.php`.
- [ ] 1× `catch.neverThrown` (QueryException) → `Admin/Users/Index.php`.

### C — Duplicación P1 (extracción)
- [ ] **WithQuickInvoiceModal.php:118-120** → usar `InvoiceService::calculateVatLine` (`InvoiceService.php:230`). Mayor ROI.
- [ ] Carga de impuestos (pivote→`Tax::active()`→default) copy-paste en 6 comps → `InvoiceService::getInvoicingFormData()` (`:276`):
      `Winery/Billing/ProductSale/{Create:66,Edit:83}`, `Viticulturist/Invoices/{Create:101,Edit:165}`,
      `Winery/Billing/ProductSale/Traits/WithQuickInvoiceModal:44`, `Concerns/WithProducerInvoiceItems:46`.
- [ ] Dirección por defecto del cliente duplicada (3 sitios) → usar `WithInvoiceClientAddress`:
      `Concerns/WithProducerInvoiceItems:104`, `ProductSale/Create:78`, `WithQuickInvoiceModal:60`.
- [ ] `HarvestStock→available_qty` byte-idéntico → extraer helper: `Producer/Invoices/Edit:108`, `Viticulturist/Invoices/Edit:118`.
- [ ] Guard de capacidad de depósito (msg + `throw ValidationException`) en 8 comps → trait `WithContainerCapacityGuard`
      o método en `ContainerStockService`: `Winery/Bottling/{Create:224,Edit:225}`, `WineLosses/{Create:96,Edit:87}`,
      `WineTransfers/{Create:66,Edit:92}`, `Coupage/Create:83`.
- [ ] `formatValue()/getFieldLabel()` historial auditoría duplicados → trait `WithAuditHistoryFormatting`:
      `Viticulturist/Plots/PlotAuditHistory:138`, `Viticulturist/DigitalNotebook/ActivityAuditHistory:139`.

### D — Inconsistencia de patrones P2
- [ ] `rules()` byte-idénticos en pares Create/Edit de ~20 dominios sin trait `With*FormRules`
      (SoilAnalyses, Machinery, WineAnalysis, Exploitations, AgriInsurance, ContainerReturns, Wines, TastingNotes…).
- [ ] `Shared/AbstractIndex.php` usa `WithPagination` crudo → migrar a `WithListing` y borrar miembros
      duplicados (`$search`/`updatingSearch`/`$currentTab`/`switchTab`) en ~42 subclases.
- [ ] Listados standalone que reimplementan a mano: `Winery/ExternalGrape/Index`, `Winery/Traceability/Index`,
      `Viticulturist/Viticulturists/Index`.

### E — 🔴 Estructural ALTA (carril largo, hacer por dominios)
- [ ] Extraer `DB::transaction` + reglas de negocio de componentes Livewire a `Services`/`Actions`.
      72 componentes; empezar por `Viticulturist/DigitalNotebook/Create*` (caso ref: `CreateHarvest.php` 470 líneas
      crea CrewMember+AgriculturalActivity+Harvest+PhenologyObservation) y flujos de facturación.

## 🔁 Protocolo al retomar
1. Arrancar Docker + `docker compose up -d mariadb_test`.
2. `php artisan test --env=testing` → fijar baseline verde (anotar nº de tests/fallos preexistentes).
3. Atacar por lotes A→E; tras cada lote, correr los tests del dominio afectado (nunca confiar a ciegas, sin DB no hay red).
4. Commits a nombre del usuario, sin mención a Claude (ver memoria `feedback_git_commits`).

## Memoria relacionada
`project_audit_2026_06_21`, `project_invoice_unification`, `project_form_pattern_unification`,
`project_phpstan_baseline`, `feedback_livewire_testing` (nunca suite completa en bucle), `feedback_git_commits`.
