# Arreglos de tests pendientes

Estado a 2026-07-26 (sesión de continuación).

## Ya arreglados y mergeados a `main`

- (`6f4282bb`) `UserFactory` no hidrataba `is_beta_user` (default BD `true`) → 403 espurios en tests de API.
- (`2bc83fbe`) API no respetaba `compra_uva_externa` del `producer` en `grape-invoices` (paridad con la web).
- **Causa raíz de los 122 fallos "preexistentes" (2026-07-24) encontrada y arreglada**: el commit
  `d547d01d` ("fix: eliminar 30 errores PHPStan lote 6") borró por error el bypass de
  `ApiRole::handle()` que ignoraba `tokenCan()` cuando no hay un token Sanctum real
  (sesión web o `actingAs()` en tests). Arreglo: restaurar el bypass en
  `app/Http/Middleware/ApiRole.php` + 2 entradas en `phpstan-baseline.neon`.
  Verificado: **122 → 21 fallos**, sin regresiones nuevas.

## Los 21 fallos restantes: investigados y arreglados en esta sesión

Todos los 21 fallos de la lista anterior fueron investigados hasta la causa raíz. En el proceso
aparecieron 5 fallos **nuevos** (regresiones propias de esta sesión, ya corregidas) porque el primer
fix de invoices cambió el modelo de negocio de stock — ver detalle abajo. Pendiente de confirmación
final: una corrida completa de la suite tras el último lote de fixes (en curso, ver "Cómo retomar").

### 1. Labels/colors "unknown value" (5 tests)

`FertilizationPlan`, `Certification`, `WaterConcession`: los accessors `getXLabelAttribute()` /
`getXColorAttribute()` indexaban el array de constantes con `$this->status` / `$this->certification_type`
sin fallback (`self::STATUSES[$this->status]` en vez de `... ?? $this->status`), y petaban con un valor
de BD fuera del enum esperado. Arreglo: añadir `?? fallback` en los 3 modelos + 3 entradas nuevas en
`phpstan-baseline.neon` (false positive `nullCoalesce.offset`: PHPStan infiere el tipo de la propiedad
como la unión literal de los valores usados en código, no como "cualquier string de BD").

### 2. Stock de invoices: `delivery_status` como único disparador de venta (bug real de negocio)

Un commit previo (`a5ba7189`, modelo "Vinai2") ya había establecido que `delivery_status='delivered'`
debía ser el ÚNICO disparador de conversión reserva→venta — pero el refactor había quedado incompleto:

- **Bug 1 (doble reserva)**: `InvoiceObserver` revertía venta→reserva en cualquier `sent→draft` sin
  comprobar si la entrega ya se había confirmado. Si no, duplicaba la reserva (repro: 200→400).
  Fix: gatear el revert con `$invoice->delivery_status === 'delivered'`.
- **Bug 2 (venta prematura)**: `InvoiceItemObserver::created()` vendía directamente (`directSale`) al
  crear un item en una invoice `sent`/`approved`, ignorando `delivery_status`. Fix (aprobado por el
  usuario vía pregunta explícita): reservar siempre salvo `delivery_status==='delivered'`, propagado a
  `created/updated/deleting/restored` y a `ContainerStockService::adjustItemQuantity` (nuevo parámetro
  `bool $isDelivered` en vez de mirar el status de la invoice).
- Downstream: `app/Livewire/Viticulturist/Billing/HarvestSale/Edit.php` tenía la misma inconsistencia
  (pasaba `$this->invoice->status` a `releaseFromInvoice` en vez de `'draft'`).

Tests reescritos para reflejar el modelo correcto (delivery_status, no status, decide reserva vs venta):
`InvoiceStatusTransitionStockTest.php`, `StockManagementIntegrationTest.php`,
`InvoiceItemStockManagementTest.php`, `ContainerStockServiceTest.php` (estos 2 últimos NO estaban en la
lista de 21 — eran passing antes porque el bug 2 los dejaba pasar con el modelo viejo; al arreglar el
bug real, se rompieron como regresión esperada y hubo que actualizarlos al modelo correcto).

Cubre de la lista de 21: `changing draft to sent also sets invoice number`,
`changing draft to sent converts reservations to sales`, `complete flow create approve revert cancel
restores stock`, `modifying quantities maintains stock integrity`, `multiple items transition correctly`,
`stock accuracy after multiple operations`, `stock movements create complete audit trail`.

### 3. `SecurityLoggerTest > log captcha activated logs notice` — test desactualizado

`SecurityLogger::logCaptchaActivated(string $email, int $failedAttempts = 0)` recibe el contador como
2º parámetro explícito (el caller real, `Login.php`, ya lo pasa bien). El test llamaba sin ese arg y
simulaba `session(['login_failed_attempts' => 5])`, que el método nunca lee. Fix: llamar con el arg
explícito `SecurityLogger::logCaptchaActivated('test@example.com', 5)`.

### 4. Oversight Notebook: modelo equivocado (`SupervisorViticulturist` vs `WineryViticulturist`)

`app/Livewire/Supervisor/Oversight/Notebook/Index.php` calculaba `$accessibleVitIds` con
`SupervisorViticulturist::where('notebook_access', true)` — pero `notebook_access` vive en
`WineryViticulturist` (con `source=SOURCE_SUPERVISOR`), tal y como hace el componente hermano
`Activity/Index.php`. Cubre: `activities visible when notebook access granted`,
`filter by type narrows results`, `filter by viticulturist narrows results`.

### 5. `ExternalGrapeTest > edit saves changes` — test desactualizado

Llamaba `->call('update')`, pero el método público del patrón `AbstractEdit` (Form Pattern Unification)
es `save()`, no `update()`. Fix: `->call('save')`.

### 6. `mount sets campaign id` (Harvest y EstimatedYields CreateTest) — test con setup defectuoso

`makeViticulturist()` dispara `UserObserver` (`can_login=true` en un viticultor → auto-crea la
campaña activa del año vía `Campaign::getOrCreateActiveForYear`). El helper `makeCampaign()` de ambos
test files creaba OTRA campaña activa vía `Campaign::factory()->active()->create(...)`, dejando 2
campañas activas del mismo año para el mismo viticultor (sin unique constraint en BD). El componente
hace `Campaign::where(...)->active()->first()`, no determinista entre las dos filas duplicadas.
Fix: `makeCampaign()` reutiliza la campaña ya auto-creada (`update(['year' => ..., 'active' => true])`)
en vez de crear una segunda. Aplicado en ambos test files (`Harvest/CreateTest.php` pasaba por suerte
de orden de filas — mismo bug latente, ahora con test determinista).

### 7bis. Flake de factory: `InvoiceItem::factory()` + `Harvest::factory()` con rangos aleatorios independientes

El flake ya conocido (`invoice item belongs to harvest`) reapareció en la corrida de verificación
con un nombre distinto del mismo fichero (`has harvest returns true when has harvest`): ambos tests
hacían `Harvest::factory()->create()` + `InvoiceItem::factory()->create(['harvest_id' => ...])` sin
usar el estado `withHarvest()` de `InvoiceItemFactory`, que existe precisamente para clampar
`quantity` a la mitad del `total_weight` de la cosecha. Sin él, `quantity` (10–1000) y `total_weight`
(100–5000) son rangos aleatorios independientes; cuando `quantity > available_qty`,
`InvoiceItemObserver::created()` → `reserveStock()` lanza `RuntimeException` ("Stock insuficiente").
Al cambiar ambos tests a `InvoiceItem::factory()->withHarvest($harvest)->create()` salió a la luz un
segundo bug, este sí en el propio factory: `withHarvest()` accedía a
`$harvestModel->plotPlanting->grapeVariety->name` sin `?->`, y como estos tests crean un `Harvest`
"pelado" (sin `plot_planting_id`), petaba con "Attempt to read property on null". Fix: encadenar con
`?->` en `database/factories/InvoiceItemFactory.php` (`database/factories` no está en el `paths` de
`phpstan.neon`, así que este fix no toca el baseline). Verificado 5/5 corridas aisladas sin flake.

### 7. `by variety groups planted area per grape variety` / `top varieties ordered by area descending` — **bug real de UI**

`GrapeVariety::name` es traducible (`spatie/laravel-translatable`, columna JSON `{"es":"Tempranillo"}`).
Las queries `DB::table(...)->join('grape_varieties', ...)->select('grape_varieties.name as variety_name', ...)`
en `Supervisor/Territory/Index.php` y `Supervisor/Statistics/Index.php` devolvían el JSON crudo en vez
del nombre localizado (bypasean el accessor de Eloquent al ser raw query). Un supervisor real vería
`{"es":"Tempranillo"}` en vez de "Tempranillo" en esas pantallas. Fix: `selectRaw` con
`JSON_UNQUOTE(JSON_EXTRACT(grape_varieties.name, ?))` parametrizado con el locale actual. Se encontró
el mismo patrón (sin test que lo cubra) en `Supervisor/Oversight/Wineries/Show.php` (`variety`) y se
corrigió igual por consistencia.

## Cómo retomar

Si la corrida de verificación final (`/tmp/fixed4.txt` / `/tmp/fixed4_fails.txt`) no ha terminado,
esperar la notificación en vez de lanzar otra — **nunca dos `php artisan test` en paralelo**: el
`tests/bootstrap.php` hace DROP + migrate + seed completo de la BD compartida `agro365_test` en cada
invocación, y dos procesos a la vez corrompen la corrida del otro (visto en esta sesión: crash con
`Duplicate entry 'EXENTO-0.00-General'` en `TaxSeeder`).

Metodología para verificar cualquier fix sin regresiones:

```bash
# baseline (antes del fix)
git stash
php artisan test 2>&1 | sed -E 's/\x1b\[[0-9;]*m//g' > /tmp/baseline.txt
grep -E "^\s*⨯" /tmp/baseline.txt | sed -E 's/^\s*⨯\s+//; s/\s+[0-9.]+s\s*$//' | sort > /tmp/baseline_fails.txt
git stash pop

# después del fix
php artisan test 2>&1 | sed -E 's/\x1b\[[0-9;]*m//g' > /tmp/fixed.txt
grep -E "^\s*⨯" /tmp/fixed.txt | sed -E 's/^\s*⨯\s+//; s/\s+[0-9.]+s\s*$//' | sort > /tmp/fixed_fails.txt

# diff: no debe haber fallos NUEVOS
comm -13 /tmp/baseline_fails.txt /tmp/fixed_fails.txt   # nuevos (debe salir vacío)
comm -23 /tmp/baseline_fails.txt /tmp/fixed_fails.txt   # arreglados
```

No basta con comparar el conteo total de fallos — hay que confirmar que el set de tests que falla
es subconjunto exacto del anterior. Si aparece algo "nuevo", antes de asumir regresión, correr ese
test solo 2-3 veces aislado para descartar flake, y si no es flake, investigar si es una regresión
real del fix (como pasó con los 5 tests de stock de esta sesión) o un test con setup defectuoso
(como el de `mount sets campaign id`).

Nota: la suite completa tarda ~34 min. La BD de test corre en el contenedor Docker
`agro365_mariadb_test` (puerto 3308) — si `php artisan test` falla con
`SQLSTATE[HY000] [2002]`, arrancarlo con `docker start agro365_mariadb_test`.

## Detalles / memoria relacionada

Contexto completo de lo ya investigado y arreglado, incluyendo el porqué de cada bug, en la memoria
del agente:
- `project_test_suite_health_2026_07_24.md`
- `feedback_test_factory_defaults.md`
