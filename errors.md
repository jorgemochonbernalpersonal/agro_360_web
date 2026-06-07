# Feature Test Failures — seguimiento de progreso

Suite: `php artisan test --testsuite=Feature`
Fecha original: 2026-06-06 — Estado inicial: 2462 passed / 117 failed
Última actualización: 2026-06-07

---

## ✅ RESUELTO — Grupo 8 — Supervisor: supervisión y pool (24 tests)

**Estado:** CORREGIDO — todos pasan

Tests que fallaban:
- `Tests\Feature\Personal\VisibilityTest` > viticulturist with supervisor sees supervisor pool
- `Tests\Feature\Supervisor\Campaigns\CampaignsDataTest` > tabs counts reflect years and viticulturists
- `Tests\Feature\Supervisor\Census\CensusTabsTest` (2 tests)
- `Tests\Feature\Supervisor\Notebook\NotebookAccessTest` > supervisor cannot request for viticulturist not in pool
- `Tests\Feature\Supervisor\Oversight\ActivityIndexTest` (5 tests)
- `Tests\Feature\Supervisor\Oversight\GrowersIndexTest` (7 tests)
- `Tests\Feature\Supervisor\Oversight\NotebookIndexTest` (3 tests)
- `Tests\Feature\Supervisor\Statistics\StatisticsDataTest` > top varieties ordered by area descending
- `Tests\Feature\Supervisor\Territory\TerritoryDataTest` > by variety groups planted area per grape variety
- `Tests\Feature\Winery\WineryViticulturistScopeTest` > viticulturist with supervisor sees supervisor pool

**Fixes aplicados:**
- `app/Livewire/Supervisor/Oversight/Activity/Index.php` — cambiado de `SupervisorViticulturist` a `WineryViticulturist::where('source', SOURCE_SUPERVISOR)` en ambas queries
- `tests/Feature/Supervisor/Campaigns/CampaignsDataTest.php` — `can_login: false` en `makeViticulturistForCampaigns()` para evitar autocreación de Campaign por UserObserver
- `tests/Feature/Supervisor/Notebook/NotebookAccessTest.php` — cambiado `expectException(ModelNotFoundException::class)` por `->assertHasErrors(['targetViticulturistId'])`
- `app/Livewire/Supervisor/Census/Index.php` — fix sesión anterior
- `app/Livewire/Supervisor/Oversight/Growers/Index.php` — fix sesión anterior

---

## ✅ RESUELTO — Grupo 9 — Middleware CheckRole (1 test)

**Estado:** CORREGIDO — 11/11 tests pasan

**Fix aplicado:**
- `app/Livewire/Winery/Dashboard.php` — `render()` pasa explícitamente las 23 computed properties al view. Causa: Livewire 3 no inyecta `#[Computed]` en el scope del blade automáticamente.

---

## ✅ RESUELTO — Grupo 10 — Viticulturist: Campaign y DigitalNotebook (5 tests)

**Estado:** CORREGIDO — todos pasan

Tests que fallaban:
- `Tests\Feature\Viticulturist\Campaign\CreateTest` > validation fails with invalid data
- `Tests\Feature\Viticulturist\Campaign\EditTest` > viticulturist can update own campaign via livewire
- `Tests\Feature\Viticulturist\DigitalNotebook\EstimatedYields\CreateTest` > mount sets campaign id
- `Tests\Feature\Viticulturist\DigitalNotebook\Harvest\CreateTest` > mount sets campaign id
- `Tests\Feature\Viticulturist\DigitalNotebook\Harvest\HarvestCalculationsTest` > yield variance calculated w…

**Causa raíz:** `UserObserver::created()` autocrea una Campaign para el año actual cuando `can_login=true`. Esto añadía un registro inesperado que:
- En CreateTest: hacía que `Campaign::count()` fuese 1 en lugar de 0 (tras fallo de validación)
- En EditTest: creaba un duplicado de campaña 2026, disparando error de unicidad al intentar actualizar a 2026
- En DigitalNotebook tests: el componente montaba con `campaign_id` de la campaign autocreada (id menor) en lugar de la creada por el test

**Fixes aplicados:**
- `tests/Feature/Viticulturist/Campaign/CreateTest.php` — `can_login: false` en `test_validation_fails_with_invalid_data`
- `tests/Feature/Viticulturist/Campaign/EditTest.php` — `can_login: false` en `test_viticulturist_can_update_own_campaign_via_livewire`
- `tests/Feature/ViticulturistTestCase.php` — `can_login: false` en `makeViticulturist()` (afecta todos los tests DigitalNotebook)

---

## ❌ Grupo 1 — RuntimeException (6 tests)

Probablemente un método renombrado o eliminado en el refactor.

- `Tests\Feature\StockManagementIntegrationTest` > preventing overselling maintains data in…
- `Tests\Feature\Winery\Services\WineContainerStockServiceTest` > record loss decrements co…
- `Tests\Feature\Winery\Services\WineContainerStockServiceTest` > record loss does not go b…
- `Tests\Feature\Winery\Services\WineContainerStockServiceTest` > record loss creates histo…
- `Tests\Feature\Winery\Services\WineContainerStockServiceTest` > revert loss restores cont…
- `Tests\Feature\Winery\Services\WineContainerStockServiceTest` > update loss applies new q…

**Archivos a revisar:**
- `tests/Feature/Winery/Services/WineContainerStockServiceTest.php`
- `tests/Feature/StockManagementIntegrationTest.php`
- `app/Services/WineContainerStockService.php` (modificado en Fase 3)

---

## ❌ Grupo 2 — BadMethodCallException (8 tests)

Probablemente una llamada a un método de Livewire que ya no existe o fue renombrado.

- `Tests\Feature\Viticulturist\PlotCosts\CreateTest` (6 tests — toda la clase)
- `Tests\Feature\Viticulturist\PlotCosts\EditTest` > viticulturist can edit own cost
- `Tests\Feature\Viticulturist\Subcontracting\CreateTest` (6 tests — toda la clase)
- `Tests\Feature\Viticulturist\Subcontracting\EditTest` > viticulturist can edit own…

**Archivos a revisar:**
- `tests/Feature/Viticulturist/PlotCosts/CreateTest.php`
- `tests/Feature/Viticulturist/PlotCosts/EditTest.php`
- `tests/Feature/Viticulturist/Subcontracting/CreateTest.php`
- `tests/Feature/Viticulturist/Subcontracting/EditTest.php`

---

## ❌ Grupo 3 — QueryException (11 tests)

Probablemente columna o tabla que no existe / migración pendiente.

- `Tests\Feature\Commands\CleanupUnverifiedUsersTest` (4 tests — toda la clase)
- `Tests\Feature\Viticulturist\HarvestByproducts\CreateTest` (2 tests)
- `Tests\Feature\Viticulturist\HarvestByproducts\EditTest` (4 tests)
- `Tests\Feature\Viticulturist\HarvestByproducts\IndexTest` (7 tests)

**Archivos a revisar:**
- `tests/Feature/Commands/CleanupUnverifiedUsersTest.php`
- `tests/Feature/Viticulturist/HarvestByproducts/` (3 archivos)

---

## ❌ Grupo 4 — MultipleRootElementsDetectedException (2 tests)

Livewire component con múltiples elementos raíz en la vista.

- `Tests\Feature\Viticulturist\Invoices\IndexTest` > search filters by…
- `Tests\Feature\Viticulturist\Invoices\IndexTest` > corrective cannot…

**Archivos a revisar:**
- `resources/views/livewire/viticulturist/invoices/index.blade.php`

---

## ❌ Grupo 5 — Auth: ClaimAccountTest (18 tests — clase entera)

Toda la clase falla. Probable: componente Livewire renombrado o ruta cambiada.

- `Tests\Feature\Auth\ClaimAccountTest` — 18 tests (toda la clase)

**Archivos a revisar:**
- `tests/Feature/Auth/ClaimAccountTest.php`
- `app/Livewire/Auth/ClaimAccount.php` (o nombre equivalente)

---

## ❌ Grupo 6 — Auth: otros (2 tests)

- `Tests\Feature\Auth\DniMergeTest` > ghost with matching dni is activated
- `Tests\Feature\Auth\ResetPasswordTest` > redirects to login after reset

---

## ❌ Grupo 7 — Supervisor: OrganizationsObserverTest (12 tests — clase entera)

Toda la clase falla. Probable: observer renombrado, modelo o relación cambiada.

- `Tests\Feature\Observers\OrganizationsObserverTest` — 12 tests

**Archivos a revisar:**
- `tests/Feature/Observers/OrganizationsObserverTest.php`
- `app/Observers/WineryViticulturistObserver.php` o similar

---

## ❌ Grupo 9 (pendiente) — Navigation (3 tests)

- `Tests\Feature\Navigation\NavigationMenuTest` — 3 tests (secciones del menú winery)
  - Error: `assertArrayHasKey` falla en secciones esperadas del menú winery

---

## ❌ Grupo 11 — Varios (13 tests)

- `Tests\Feature\Personal\UnifiedIndexTest` > delete viticulturist without relations works
- `Tests\Feature\Producer\HybridAccessTest` > producer can access winery dashboard
- `Tests\Feature\Producer\ProducerRoutesTest` > financial stats winery renders
- `Tests\Feature\Viticulturist\HarvestSale\IndexTest` > cancel releases harvest stock
- `Tests\Feature\Viticulturist\QuickEntryTest` (3 tests)
- `Tests\Feature\Viticulturist\WineryAccessTest` > viticulturist cannot revoke access for unrelated winery
- `Tests\Feature\Winery\Financial\FinancialTest` > financial stats renders
- `Tests\Feature\Winery\Harvest\ForecastsTest` (2 tests)
- `Tests\Feature\Winery\Harvest\ProducerReceptionTest` > producer can create self reception
- `Tests\Feature\Winery\Silicie\DashboardTest` > dashboard can switch tabs

---

## Resumen de progreso

| Grupo | Tests | Estado |
|-------|-------|--------|
| 1 — RuntimeException WineContainerStock | 6 | ❌ pendiente |
| 2 — BadMethodCallException PlotCosts/Subcontracting | 8 | ❌ pendiente |
| 3 — QueryException HarvestByproducts | 11 | ❌ pendiente |
| 4 — MultipleRootElements Invoices | 2 | ❌ pendiente |
| 5 — Auth ClaimAccountTest | 18 | ❌ pendiente |
| 6 — Auth otros | 2 | ❌ pendiente |
| 7 — OrganizationsObserverTest | 12 | ❌ pendiente |
| 8 — Supervisor pool/supervisión | 24 | ✅ **CORREGIDO** |
| 9 — Middleware CheckRole | 1 | ✅ **CORREGIDO** |
| 9 — Navigation | 3 | ❌ pendiente |
| 10 — Campaign/DigitalNotebook | 5 | ✅ **CORREGIDO** |
| 11 — Varios | 13 | ❌ pendiente |
| **Total** | **105** | **30 corregidos / ~75 pendientes** |

---

## Commits pendientes (no commiteados aún)

- `app/Livewire/Supervisor/Oversight/Activity/Index.php`
- `tests/Feature/Supervisor/Campaigns/CampaignsDataTest.php`
- `tests/Feature/Supervisor/Notebook/NotebookAccessTest.php`
- `app/Livewire/Winery/Dashboard.php`
- `tests/Feature/Viticulturist/Campaign/CreateTest.php`
- `tests/Feature/Viticulturist/Campaign/EditTest.php`
- `tests/Feature/ViticulturistTestCase.php`
