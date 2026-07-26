# Arreglos de tests pendientes

Estado a 2026-07-24. Ya arreglados y mergeados a `main` (commits `6f4282bb`, `2bc83fbe`):
- `UserFactory` no hidrataba `is_beta_user` (default BD `true`) → 403 espurios en tests de API.
- API no respetaba `compra_uva_externa` del `producer` en `grape-invoices` (paridad con la web).

Quedan **122 fallos preexistentes** en `php artisan test` (suite completa), sin relación aparente
con los dos bugs de arriba, sin investigar todavía. Agrupados por clase:

- `NotebookApiTest` — 35
- `ContainerApiTest` — 28
- `SilicieApiTest` — 12
- `PlotApiTest` — 11
- `ContainerReturnApiTest` — 9
- `ApiRoleMiddlewareTest` — 7
- `GrapePurchaseInvoiceApiTest` — 1 (ver más abajo, distinto del resto)

## Cómo retomar

Empezar por `NotebookApiTest` (el cluster más grande):

```
php artisan test tests/Feature/Api/NotebookApiTest.php
```

Metodología para verificar cualquier fix sin regresiones (usada en los dos bugs ya arreglados):

```bash
# baseline (antes del fix)
git stash
php artisan test 2>&1 | sed -E 's/\x1b\[[0-9;]*m//g' > /tmp/baseline.txt
grep FAILED /tmp/baseline.txt | sed -E 's/^\s*FAILED\s+//' | sort > /tmp/baseline_fails.txt
git stash pop

# después del fix
php artisan test 2>&1 | sed -E 's/\x1b\[[0-9;]*m//g' > /tmp/fixed.txt
grep FAILED /tmp/fixed.txt | sed -E 's/^\s*FAILED\s+//' | sort > /tmp/fixed_fails.txt

# diff: no debe haber fallos NUEVOS
comm -13 /tmp/baseline_fails.txt /tmp/fixed_fails.txt   # nuevos (debe salir vacío)
comm -23 /tmp/baseline_fails.txt /tmp/fixed_fails.txt   # arreglados
```

No basta con comparar el conteo total de fallos — hay que confirmar que el set de tests que falla
es subconjunto exacto del anterior.

## Detalles / memoria relacionada

Contexto completo de lo ya investigado y arreglado, incluyendo el porqué de cada bug, en la memoria
del agente:
- `project_test_suite_health_2026_07_24.md`
- `feedback_test_factory_defaults.md`
