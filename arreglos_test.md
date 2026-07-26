# Arreglos de tests pendientes

Estado a 2026-07-26.

## Ya arreglados y mergeados a `main`

- (`6f4282bb`) `UserFactory` no hidrataba `is_beta_user` (default BD `true`) → 403 espurios en tests de API.
- (`2bc83fbe`) API no respetaba `compra_uva_externa` del `producer` en `grape-invoices` (paridad con la web).
- **Causa raíz de los 122 fallos "preexistentes" (2026-07-24) encontrada y arreglada**: el commit
  `d547d01d` ("fix: eliminar 30 errores PHPStan lote 6") borró por error el bypass de
  `ApiRole::handle()` que ignoraba `tokenCan()` cuando no hay un token Sanctum real
  (sesión web o `actingAs()` en tests). PHPStan marcaba `$hasToken` como "always true"
  porque el `@var TToken` del trait `HasApiTokens` de Sanctum no es nullable en el phpdoc,
  aunque en runtime sí puede serlo. Al quitar el bypass, cualquier request autenticado sin
  token Sanctum real (todos los tests que usan `$this->actingAs($user, 'sanctum')` en vez de
  `Sanctum::actingAs($user, [...])`) recibía 403 en cualquier ruta con roles en
  `api.role:...`. Arreglo: restaurar el bypass en `app/Http/Middleware/ApiRole.php` y añadir
  los 2 false positives (`notIdentical.alwaysTrue`, `booleanNot.alwaysFalse`) al
  `phpstan-baseline.neon`, mismo patrón que otros false positives ya documentados ahí.
  Verificado con la metodología de abajo: **122 → 21 fallos**, sin regresiones nuevas
  (comprobado `comm -13` entre baseline y fixed; el único nombre que aparecía como "nuevo",
  `invoice item belongs to harvest`, es un flake de datos aleatorios de factory —confirmado
  pasando 3/3 en ejecución aislada, sin relación con este cambio).

## Quedan 21 fallos (2026-07-26), sin agrupar por clase (mezcla, no concentrados en pocas clases)

```
activities visible when notebook access granted
by variety groups planted area per grape variety
certification type label returns raw value for unknown type
changing draft to sent also sets invoice number
changing draft to sent converts reservations to sales
complete flow create approve revert cancel restores stock
concession type label returns raw value for unknown type
edit saves changes
filter by type narrows results
filter by viticulturist narrows results
invoice item belongs to harvest       (flake de factory, no investigar — ver arriba)
log captcha activated logs notice
modifying quantities maintains stock integrity
mount sets campaign id
multiple items transition correctly
status color returns zinc for unknown status
status label returns raw value for unknown status
stock accuracy after multiple operations
stock movements create complete audit trail
top varieties ordered by area descending
type color returns zinc for unknown type
```

Sin investigar todavía. Varios nombres sugieren un patrón común (labels/colors para valores
"unknown", varios sobre stock/reservas de invoices, uno sobre RemoteSensing/varietal areas) —
probablemente 3-4 causas raíz distintas, no 20 bugs sueltos.

## Cómo retomar

Correr un test suelto para ver la clase completa, p.ej.:

```
php artisan test --filter="status_color_returns_zinc_for_unknown_status"
```

Metodología para verificar cualquier fix sin regresiones (usada en los bugs ya arreglados):

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
test solo 2-3 veces aislado para descartar flake de datos aleatorios de factory (ya pasó una vez,
ver arriba).

Nota: la suite completa tarda ~25-26 min. La BD de test corre en el contenedor Docker
`agro365_mariadb_test` (puerto 3308) — si `php artisan test` falla con
`SQLSTATE[HY000] [2002]`, arrancarlo con `docker start agro365_mariadb_test`.

## Detalles / memoria relacionada

Contexto completo de lo ya investigado y arreglado, incluyendo el porqué de cada bug, en la memoria
del agente:
- `project_test_suite_health_2026_07_24.md`
- `feedback_test_factory_defaults.md`
