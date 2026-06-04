# Áreas de mejora — Agro360 Web

> Análisis realizado el 2026-06-04. Laravel 12 + Livewire 3 + Flux.
> Roles: `winery`, `viticulturist`, `producer`, `supervisor`/`DO`, `admin`.
> Regla de negocio clave: winery, viticulturist, producer y supervisor actúan de
> forma independiente; **una D.O. obligatoriamente debe tener wineries asociadas**.

Score global estimado: **7/10** — buena estructura, ejecución inconsistente. No
necesita reescritura, necesita migración disciplinada hacia patrones ya definidos.

## Registro de progreso

### ✅ Fase 0 — completada (2026-06-04)
- **Regla "D.O. debe tener ≥1 winery"**: implementada en `unassignWinery()`
  (`app/Livewire/Supervisor/Census/Index.php:77`). Bloquea desasignar la última bodega
  con toast de error en vez de dejar la D.O. huérfana.
- **PHPStan operativo en CI**:
  - Instalado `larastan/larastan ^3.0` como dev dependency.
  - `phpstan.neon` corregido: incluye `vendor/larastan/larastan/extension.neon`,
    `excludePaths` ahora opcional (`app/Console/Kernel.php` no existe en Laravel 12),
    eliminado `ignoreErrors` muerto.
  - Generado `phpstan-baseline.neon` (3.198 errores preexistentes congelados).
  - Corregidos 16 errores no-bagelinables `return.missing` (métodos `save()/update(): mixed`
    sin `return` en el path del `catch`) → añadido `return null;` final.
    Archivos: `Viticulturist/DigitalNotebook/{Create,Edit}*` (14) + `Winery/Harvest/Reception/{Create,Edit}` (2).
  - PHPStan nivel 5 → **OK, 0 errores** sobre el baseline.
  - Añadido paso **PHPStan** al workflow `.github/workflows/tests.yml` antes de los tests.
    (Pint se descartó por ahora: el código base aún no pasa `pint --test` — ver pendientes.)
- **Tests**: `WineryAssignmentTest` actualizado — `test_supervisor_can_unassign_winery`
  ahora parte de 2 bodegas; nuevo `test_supervisor_cannot_unassign_last_winery`. 9/9 en verde.
- **Bug preexistente corregido**: `makeSupervisor()`/`makeSupervisorWithWinery()`
  (`tests/Feature/SupervisorTestCase.php`) no fijaban `can_login => true`; con `actingAs()`
  (instancia en memoria) el `CheckCanLogin` bloqueaba el supervisor → `test_census_renders`
  fallaba con "Cuenta desactivada". Añadido `can_login => true`.

### Pendiente de Fase 0 (follow-up de bajo esfuerzo)
- **Pint en CI**: ejecutar `vendor/bin/pint` (modo fix) sobre todo el repo en un commit
  dedicado de formato, y luego reañadir `pint --test` al workflow. No incluido aquí para
  no mezclar un diff masivo de estilo con los cambios funcionales.

### ✅ Fase 1 — Facturación: red de tests + Policies/FormRequests (2026-06-04)
- **Red de caracterización (44 tests, 101 asserts, en verde)** que congela el
  comportamiento actual de los 4 flujos de facturación antes de refactorizar:
  - `tests/Feature/Api/Winery/GrapePurchaseInvoiceApiTest.php` (22) — liquidación compra de uva.
  - `tests/Feature/Api/Winery/WineSaleInvoiceApiTest.php` (14) — venta de producto/vino.
  - `tests/Feature/Api/Viticulturist/HarvestSaleInvoiceApiTest.php` (8) — listado venta de cosecha.
  - (producer_sale ya cubierto por `Producer/Invoices/MixedInvoice*Test`.)
- **Autorización + validación movidas a FormRequests** (patrón nuevo en el repo,
  `app/Http/Requests/Api/...`):
  - Bases `WineryApiRequest` / `ViticulturistApiRequest`: centralizan el chequeo de rol
    (`authorize()` → 403) que antes estaba duplicado como `abort_unless($user->hasXAccess(), 403)`
    en ~13 acciones.
  - Requests específicos con `rules()` (validación inline extraída) y `withValidator()`
    para los guards cross-entity (viticultor vinculado, recepción de la bodega) que antes
    eran `abort_*(422)` en el controlador.
  - Controllers afectados: `Api\Winery\GrapePurchaseInvoiceController`,
    `Api\Winery\InvoiceController`, `Api\Viticulturist\HarvestSaleInvoiceController`.
- **Decisión de diseño conservada**: el scoping por `user_id` que devuelve **404** ante
  facturas de otra bodega (no `authorize()` sobre el modelo encontrado, que daría 403 y
  filtraría existencia) se mantiene intencionadamente. Extraído a helper `findOwnedInvoice()`.
- La máquina de estados (cancelada/pagada/borrador → 422) permanece en el controlador.
- PHPStan: **OK, 0 errores** sobre baseline tras el refactor.

### 🔴 Hallazgo nuevo (Fase 1) — la suite de tests API está ROJA
- `ApiRole` exige **además del rol** que el token tenga la ability (`tokenCan($role)`).
  Los tests API existentes usan `$this->actingAs($user, 'sanctum')`, que **no adjunta token**,
  así que `tokenCan()` devuelve false → **403** en todos ellos.
- Verificado: `ApiRoleMiddlewareTest::test_middleware_allows_correct_role_directly` **falla** hoy.
- Afecta a `ContainerApiTest`, `PlotApiTest`, `NotebookApiTest`, `AuthApiTest`, etc.
- **Workaround usado en los tests nuevos**: `Sanctum::actingAs($user, ['*'])` (adjunta
  `TransientToken`, `can()` siempre true).
- **Pendiente (separado, grande)**: decidir si (a) se corrigen todos los tests API existentes
  para usar `Sanctum::actingAs`, o (b) se relaja `ApiRole` para no exigir `tokenCan` cuando
  el rol del usuario ya coincide. Hasta entonces el `testsuite=Feature` no puede ir 100% verde en CI.

---

## 1. Modelo de roles y control de acceso

### 🔴 CRÍTICO — La regla "una D.O. debe tener wineries" NO está implementada
- No existe ninguna validación que impida que una D.O. se quede sin bodegas.
- `app/Livewire/Supervisor/Census/Index.php:77` (`unassignWinery`) permite desasignar
  la última bodega → la D.O. queda huérfana (interfaz vacía, nada que supervisar).
- Tampoco se exige al crear una D.O.
- Búsqueda `supervisedWineries.*count|empty` → 0 resultados.

**Fix sugerido** en `unassignWinery()`:
```php
if (SupervisorWinery::where('supervisor_id', Auth::id())->count() <= 1) {
    abort(403, 'Una D.O. debe mantener al menos una bodega asociada.');
}
```
Considerar también bloquear en `SupervisorWinery::deleting()` (`app/Models/SupervisorWinery.php:29`).

### 🟠 Dualidad de sistemas de identidad de rol (en transición)
- `User.role` (string) **+** modelo nuevo `Organization` (`type`: winery / denomination_of_origin),
  introducido en `2026_03_18`. Sistema dual sin fuente de verdad única.
- **Acción:** decidir cuál es canónico y migrar del todo, o quedará deuda permanente.

### 🟡 Roles definidos como alias confuso
- `app/Models/User.php:112-113`: `ROLE_DO = 'supervisor'` (mismo string que `ROLE_SUPERVISOR`).
  Parte del código usa `ROLE_DO`, otra `ROLE_SUPERVISOR`. Migrar a enum único.

### 🟠 `abilities_configured` con lógica retrocompatible ambigua
- `app/Http/Middleware/CheckWineryAbility.php:39` y `User.php:198`: "no configurada" = acceso total.
- Riesgo: si un admin activa el flag sin asignar abilities → la bodega queda **bloqueada del todo**.
- Falta logging en la transición `false → true`.

### 🟡 N+1 en Policies
- `app/Policies/PlotPolicy.php:64`: `$user->supervisedWineries->pluck('winery_id')` sin eager loading.
  Usar query directa: `SupervisorWinery::where('supervisor_id', $user->id)->pluck('winery_id')`.

### 🟡 `is_readonly_admin` no se aplica
- `app/Models/User.php:127-129`: el flag existe y hay helper `isReadOnlyAdmin()`, pero no hay
  middleware ni Policy que lo enforce. No protege nada actualmente.

### 🟡 `password_must_reset` solo en web, no en API
- El middleware de cambio de password solo cuelga de `routes/web.php`. Un viticultor creado por
  una bodega puede hacer llamadas API sin resetear su contraseña.

### Mapa de enforcement (referencia)
```
REQUEST → CheckCanLogin (can_login=false?)
        → CheckRole / ApiRole (role matches?)
        → CheckWineryAbility (abilities_configured & ability in set?)
        → Policy::view/create/update (ownership + jerarquía)
        → abort_unless (guards extra de rol)
        → firstOrFail (404 si falla ownership)
```
| Pieza | Archivo |
|---|---|
| Definición roles | `app/Models/User.php:107-163, 178-203` |
| Jerarquía usuario | `app/Models/Traits/HasHierarchy.php:88-184` |
| Relación D.O.–Winery | `app/Models/SupervisorWinery.php:19-51` (tabla `supervisor_winery`, UNIQUE supervisor+winery) |
| Middleware roles | `CheckRole.php`, `ApiRole.php`, `CheckCanLogin.php`, `CheckWineryAbility.php`, `RequireWinery.php`, `RequireSupervisor.php` |
| Asignación/desvinculación | `app/Livewire/Supervisor/Census/Index.php:60-85` |

---

## 2. Calidad de código y tooling

### 🔴 CRÍTICO — CI no protege la calidad
- `phpstan.neon` incluye `phpstan-baseline.neon` **que no existe** → PHPStan no corre.
- Pint, PHP-CS-Fixer e Infection están configurados pero **no se ejecutan en CI**
  (`.github/workflows/tests.yml` solo corre tests).
- Larastan/Psalm instalados en vendor pero sin vincular a nada.
- **Fix rápido alto impacto:** crear el baseline y añadir PHPStan + Pint al workflow.

### 🔴 CRÍTICO — La API casi no tiene tests
- 125 controllers API, ~460 rutas, solo ~6 ficheros de test (básicamente Auth).
- Los **4 flujos de facturación** (producer_sale, harvest_sale, grape_purchase, wine_sale) sin cobertura.
- Bloquea cualquier refactor seguro de la lógica de dinero.

### 🔴 CRÍTICO — Validación 100% inline en controllers (sin FormRequest)
- Controllers de 500+ líneas: `Api/Viticulturist/NotebookController.php`, `Api/Winery/SilicieController.php`.
- Reglas duplicadas entre API y Livewire. Extraer `FormRequests/` por dominio.

### 🟡 Modelos sin protección de mass-assignment
- `app/Models/WineryAnnouncement.php`: `$guarded = []` (todas las columnas asignables).
- ~6 modelos sin `$fillable`/`$guarded` explícito.
- Nota positiva: **no se usa `$request->all()`** en el código (buen patrón de validación).

---

## 3. Arquitectura y duplicación

### 🟠 Duplicación masiva entre roles (problema central)
- **468 componentes Livewire**, ~87% reimplementan a mano patrones que ya existen.
- Infraestructura para unificar **ya construida** pero con **<15% de adopción**:
  - Abstracts: `app/Livewire/Shared/AbstractIndex|Create|Edit.php`
  - 11 traits en `app/Livewire/Concerns/` (`WithListing`, `WithOwnershipRules`, `WithInvoiceActions`, etc.)

| Concepto | Duplicación | Riesgo |
|---|---|---|
| Facturación (5 variantes) | 85-95% | **Dinero** |
| Stock/Inventario | ~70% | **Inventario** |
| Listados (Index, ~91 comp.) | ~90% boilerplate | Bajo |
| Clientes (viticulturist vs winery) | ~95% idéntico | Bajo |
| Validaciones de ownership | ~85% (~30 sitios) | Bajo |

### 🟠 Servicios de stock fragmentados
- `app/Services/ContainerStockService.php` (**1.028 líneas**, monolítico) + `HarvestStockService` + `ProductStockService`,
  sin abstracción base común. Cada uno reimplementa `movement_type`, tracking y auditoría.
- Considerar `UnifiedStockService` + estrategias (Harvest/Container/Product).

### 🟠 Policies no usadas en la API
- 14 Policies funcionan en Livewire, pero la API repite `abort_unless(...)` inline en 40+ controllers
  en vez de `$this->authorize()`. Ej.: `Api/Winery/InvoiceController.php:66`.
- Además, el array `$policies` en `AppServiceProvider` es código muerto (las Policies cargan por convención).

### 🟡 Rutas duplicadas / nombres confusos
- `producer/clients` y `producer/winery-clients` apuntan al mismo `\App\Livewire\Clients\Index`.
- El rol `producer` delega a componentes de viticulturist y winery → rutas con nombres distintos
  apuntando al mismo componente. Consolidar con scoping por rol dentro del componente.

### Tamaño de rutas (síntoma de duplicación)
```
routes/api.php           ~878 líneas / 460 rutas
routes/producer.php      ~798 líneas
routes/viticulturist.php ~465 líneas
routes/winery.php        ~324 líneas
```

---

## 4. Esquema de base de datos

### 🟡 Deuda de limpieza en migraciones
- 380 migraciones; 20+ recientes son `make_X_nullable` / `fix_*_schema` → diseño inicial parcheado.
- No hay drops destructivos (esquema funcionalmente estable, pero crece sin revertir).
- Ejemplos: `2026_04_25_000003_fix_grape_reception_batches_schema`, `*_make_*_nullable`.

---

## 5. Configuración y secretos

- `.env.example` bien documentado (89 vars con comentarios de seguridad: APP_DEBUG, SESSION_SECURE_COOKIE, CORS).
- Integraciones OK: PayPal (srmklive), Sentry, Reverb, Socialite (Google/Apple), NASA Earthdata (NDVI), reCAPTCHA v2.
- 🟡 Credenciales de Reverb (`REVERB_APP_KEY`, `REVERB_APP_SECRET`) hardcodeadas en `.env` → rotar en producción.

---

## 6. Tests

- 386 ficheros / ~3.370 métodos. Distribución: 303 Feature, 68 Unit, 8 Browser (Dusk).
- 🔴 Gap principal: API y facturación sin cobertura.
- 🟡 Flakiness: colisión de seeds en paralelo (`grape_varieties_code_unique` duplicate) →
  ejecutar en aislamiento (`php artisan test <fichero>`).

---

## 7. Roadmap recomendado (impacto vs esfuerzo)

**Fase 0 — Inmediato (cambios pequeños, alto valor)**
- [ ] Implementar regla "D.O. debe tener ≥1 winery" (`unassignWinery` + `SupervisorWinery::deleting`).
- [ ] Crear `phpstan-baseline.neon` y añadir PHPStan + Pint al CI.

**Fase 1 — Seguridad + red de tests**
- [ ] Tests de caracterización de los 4 flujos de facturación (antes de tocar nada).
- [ ] Reemplazar `abort_unless` inline de la API por Policies + `$this->authorize()`.
- [ ] Crear FormRequests por dominio (Invoice, Harvest, Wine, Container...).

**Fase 2 — Unificación de facturación (tests-first)**
- [ ] `InvoiceService` + `BaseInvoiceCreate`/`BaseInvoiceEdit`.
- [ ] Migrar Producer → Viticulturist → Winery uno a uno.

**Fase 3 — Listados (bajo riesgo, en paralelo)**
- [ ] Rollout de `WithListing` a los ~91 listados restantes.
- [ ] Auditar conformidad con `docs/patron-vista-listado.md`.

**Continuo / mayor esfuerzo**
- [ ] Unificar servicios de stock (`UnifiedStockService`).
- [ ] Decidir fuente de verdad `User.role` vs `Organization` y migrar.

---

## Apéndice — Lo que ya está bien
- Modelos bien diferenciados por dominio (parcelas, vendimias, contenedores, facturación).
- Middleware de roles/abilities estructurado y con caché.
- Policies, abstracts y traits ya creados (solo falta adoptarlos).
- Planes internos previos: `2026-05-31-importante.md`, `2026-06-01-plan-unificacion-arquitectura.md`, `docs/patron-vista-listado.md`.
- Fix reciente ya aplicado: ownership de `harvest_id` en facturas Producer (`app/Livewire/Producer/Invoices/Create.php:488`).
