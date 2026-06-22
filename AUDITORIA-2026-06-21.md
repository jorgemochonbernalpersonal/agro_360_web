# Auditoría — Agro360 Web (2026-06-21)

> Alcance acordado: **Calidad/Arquitectura + Rendimiento**, con informe **y arreglo** de hallazgos
> críticos/altos. Stack: Laravel 12.43 + Livewire 3 + Flux. Rama: `staging`.
> Tamaño: ~1256 PHP en `app/`, 516 componentes Livewire, 170 modelos, 455 tests, 383 migraciones.
>
> Método: 3 barridos paralelos (rendimiento, deuda técnica, arquitectura) + verificación manual
> de cada hallazgo contra el código real (relación Eloquent existe, acceso en Blade confirmado)
> antes de tocar nada.

---

## ✅ Arreglado en esta sesión (12 ficheros modificados, 14 borrados)

### Rendimiento — N+1 eliminados (8 + 1 bug)
| # | Fichero | Cambio | Impacto |
|---|---------|--------|---------|
| 1 | `Viticulturist/DigitalNotebook/AbstractActivityIndex.php:219` | `+'campaign'` al eager-load base | **−hasta 12 queries/página en 7 pantallas** (todas las subclases de actividad) |
| 2 | `Winery/Announcements/Index.php:96` + blade `:23` | `withCount('viticulturists')` → `viticulturists_count` | −hasta 15 COUNT/página |
| 3 | `Admin/Organizations/Index.php:266` | `+'parent'` al `with()` | −hasta 20 queries/página |
| 4 | `Producer/IntegratedEstate/PlotTable.php:42` | `+'municipality:id,name'` | −N queries/render (componente reactivo) |
| 5 | `Viticulturist/PlannedWorks/Index.php:108` | `+'plot','campaign'` | −2N queries/página |
| 6 | `Viticulturist/HarvestByproducts/Index.php:81` | `+'campaign'` | −N queries/página |
| 7 | `Viticulturist/FertilizationPlans/Index.php:78` | `+'campaign'` | −N queries/página |
| 8 | `Viticulturist/AdvisoryMemberships/Index.php:31` | `+'campaign'` | −N queries/página |
| 9 | `Reports/Generators/{Phytosanitary,FullNotebook}ReportGenerator.php` | `+'plot.sigpacCodes','plot.sigpacUses'` | **informes SIEX oficiales**: −~4 queries/actividad en exportación de campaña completa (CSV+XML sobre la misma colección) |

**Bug de correctitud corregido de paso:** `resources/views/public/wine-trace.blade.php:86` recorría
`plotPlanting->plotVariety->grapeVariety`, pero `PlotPlanting` no tiene relación `plotVariety` (solo
`grapeVariety`). La página pública de trazabilidad mostraba siempre "Variedad desconocida". Corregido a
`plotPlanting?->grapeVariety?->name`.

### Calidad — código muerto eliminado (14 ficheros)
Verificado: **0 referencias** en `app/`, `config/`, `routes/`, `database/`, `bootstrap/` ni providers.

- **`reset_demo_vit.php`** (raíz, trackeado en git) — script suelto que reseteaba la contraseña del
  viticultor demo a `demo1234` en texto plano. Código muerto **y** superficie de ataque si la raíz
  quedara expuesta. Si se necesita la utilidad, recrear como Artisan command con gate `App::environment('local')`.
- **5 servicios de caché/auditoría huérfanos** + sus 3 tests: `QueryCacheService`, `CampaignCacheService`,
  `PlotCacheService`, `ViticulturistCacheService`, `AuditService`.
- **5 servicios/calculadores RemoteSensing huérfanos** (el `RemoteSensingServiceProvider` liga los vivos
  —LAI/Chlorophyll/Maturity/AnomalyDetector— y omite estos): `NdviCalculator`, `PhenologyService`,
  `IrrigationCalculator`, `PhenologyCalculator`, `RecommendationGenerator`.

> Todo el borrado es reversible vía git. Revisa el diff antes de commitear. No confundir con los
> servicios **vivos** `IrrigationRecommendationService` y `MaturityCalculator`, que NO se han tocado.

**Nota de verificación:** la suite no pudo ejecutarse localmente (la BD MySQL `agro365` rechaza la
conexión). Todas las ediciones pasan `php -l` y `composer dump-autoload`, y se verificaron contra las
relaciones/accesos Blade reales. **Conviene correr la suite en CI antes de mergear.**

---

## ⏳ Pendiente — requiere decisión / refactor mayor

### 🔴 ALTA — Lógica de negocio en componentes Livewire (deuda estructural)
El hallazgo de mayor severidad. No se arregla en una sesión; es un carril de refactor.
- **72 de 516 componentes ejecutan `DB::transaction`** con orquestación multi-modelo + reglas de negocio
  embebidas, y **ninguno** delega en un servicio.
- 381 componentes consultan modelos directamente; 40 usan SQL crudo en la capa de presentación.
- Caso de referencia: `Viticulturist/DigitalNotebook/CreateHarvest.php` (470 líneas) — `save()` abre una
  transacción que crea/actualiza 4 entidades (`CrewMember`, `AgriculturalActivity`, `Harvest`,
  `PhenologyObservation`) con cálculo de plazos de seguridad y mapeo de ~30 campos inline.
- **Acción:** extraer a `Services`/`Actions` por dominio, empezando por `DigitalNotebook/Create*` y los
  flujos de facturación. Buen complemento del trabajo ya hecho (`InvoiceService`, `UnifiedStockService`).

### 🟠 P1 — Duplicación concentrada (fuga de lógica fiscal)
- **`WithQuickInvoiceModal.php:118-120`** calcula VAT por línea inline en vez de usar
  `InvoiceService::calculateVatLine()` (`InvoiceService.php:230`). Es la **única fuga** de lógica fiscal
  fuera de `InvoiceService`; el resto de flujos ya delegan. **Máximo ROI.**
- Carga de impuestos del usuario (pivote→`Tax::active()`→default) copy-pasteada en 6 componentes en vez de
  `InvoiceService::getInvoicingFormData()` (`InvoiceService.php:276`).
- Selección de dirección por defecto del cliente duplicada en 3 sitios pese a existir `WithInvoiceClientAddress`.
- Lectura `HarvestStock→available_qty` byte-idéntica en `Producer/Invoices/Edit.php:108` y
  `Viticulturist/Invoices/Edit.php:118`.
- Guard de capacidad de depósito (mensaje + `throw ValidationException`) repetido en 8 componentes Winery
  (Bottling/WineLosses/WineTransfers/Coupage) → extraer a `ContainerStockService` o trait.
- `formatValue()/getFieldLabel()` de historial de auditoría duplicados en `PlotAuditHistory` y
  `ActivityAuditHistory` → trait `WithAuditHistoryFormatting`.

### 🟠 P2 — Inconsistencia de patrones (residuos)
- `Producer/Harvest/PromoteToReception.php:83` — único `abort_unless` de **rol** que queda en Livewire
  (`$user->isProducer()`); migrar a Policy.
- `session()->flash` directo en vez de toast: `CueExports/Edit.php:31`, `Sigpac/Create.php:322,327`
  (su hermano `Sigpac/Edit` ya usa toast).
- Dos abstracciones de listado compiten: `WithListing` (canónico) vs `Shared/AbstractIndex` (usa
  `WithPagination` crudo). Que `AbstractIndex` use `WithListing` reconciliaría ~42 subclases.
- `rules()` byte-idénticos en pares Create/Edit de ~20 dominios sin trait `With*FormRules`
  (SoilAnalyses, Machinery, WineAnalysis, Exploitations, AgriInsurance…).

### 🟡 MEDIA — Índices de BD faltantes
FK-like y columnas filtradas sin índice (recomendado: una migración `add_missing_indexes`):
- `container_states`, `container_current_states`, `container_histories` → `wine_id`,
  `wine_process_detail_id`, `external_grape_id` (tablas calientes de estado).
- `security_events.admin_id`, `notification_logs.admin_id` (el `user_id` adyacente sí está indexado).
- `subscriptions.status`, `payments.status`, `invoices.delivery_status`, `invoices.sif_status`.

### 🟡 MEDIA — Mass assignment abierto
- `app/Models/WineryAnnouncement.php:45` usa `$guarded = []` (único modelo sin restricción de los 170).
  Cerrar con `$fillable` explícito tras revisar los flujos de creación.

### 🟢 BAJA — Baseline PHPStan (level 5)
Muy saneado: **13 errores congelados**, de los cuales **8 son falsos positivos irreducibles** de
invarianza de `Collection`. 5 quick-wins (no tocados para no desestabilizar CI sin PHPStan local):
- 3× `method.notFound` de scopes (`forWinery`, `byType`, `inRiskPeriod`) → anotar `@method` en el modelo
  (patrón ya existente: `app/Models/Builders/PlotQueryBuilder.php`).
- 1× `booleanAnd.rightAlwaysTrue` en `Viticulturist/Harvests/Index.php`.
- 1× `catch.neverThrown` en `Admin/Users/Index.php`.
> Al corregirlos hay que **quitar también su entrada del baseline** o CI fallará por ignore no-matcheado.

---

## Veredicto

Base sólida y bien mantenida (score histórico interno 7/10; el trabajo de unificación previo —
`InvoiceService`, `UnifiedStockService`, traits `With*FormRules`, Policies — es visible y correcto).
La deuda real restante es **una sola, estructural**: la lógica de negocio que vive dentro de los
componentes Livewire en lugar de en servicios. Todo lo demás son residuos acotados.

**Recomendación de orden:** (1) correr CI con los fixes de esta sesión → mergear; (2) cerrar
`WithQuickInvoiceModal` por `InvoiceService` (P1, alto ROI, bajo riesgo); (3) migración de índices;
(4) abrir el carril "extraer transacciones a servicios" empezando por `DigitalNotebook/Create*`.
