# Convenciones del proyecto

Mapa de decisiones arquitectónicas. Responde: "¿dónde va X?"

---

## Autorización

**Políticas → `app/Policies/`**
Toda la lógica de quién puede hacer qué sobre un modelo va en una Policy. No uses `abort_if` / `abort_unless` en controllers ni Livewire.

```php
// ✓ correcto
$this->authorize('update', $wine);

// ✗ evitar
abort_if($wine->user_id !== auth()->id(), 403);
```

**Controllers API → `WineryApiRequest::authorize()`**
`authorize()` comprueba `hasWineryAccess()`. La ownership del recurso concreto se resuelve con `Model::forUser($user->id)->findOrFail($id)` en el método del controller.

**Livewire read-only admin → `WithReadOnlyGuard` trait**
Componentes con acciones mutantes del rol Admin usan este trait. Los componentes solo de lectura no lo necesitan.

---

## Validación

**Livewire → traits `With*FormRules` en `app/Livewire/Concerns/`**
Existen para: Plot, Client, Sigpac, HarvestSale, GrapePurchase, ProductSale.
Úsalos en el componente con `use With*FormRules`. No dupliques las reglas inline.

**Controllers API → FormRequest extendiendo `WineryApiRequest`**
Todos los endpoints mutantes de `Api/Winery/` tienen su propio FormRequest en `app/Http/Requests/Api/Winery/`. El base class ya cubre la autorización.

```php
class StoreWineRequest extends WineryApiRequest
{
    public function rules(): array { ... }
}
```

**Ownership cross-entidad en FormRequest → `withValidator()`**
Si necesitas validar que una entidad relacionada pertenece al usuario autenticado, usa `withValidator()` que produce un error 422, no un 403.

**Viticulturist DigitalNotebook → `WithOwnershipRules` + `AbstractActivityForm`**
Las reglas de ownership (`campaign_id`, `plot_id`, `machinery_id`, `crew_id`) están centralizadas en el trait. Los componentes Create heredan de `AbstractActivityForm`. No reimplementes estas reglas.

---

## Stock y facturación

**Stock → `UnifiedStockService`**
Despacho de stock para Producer e Invoices. Tres servicios especializados: `ContainerStockService`, `WineContainerStockService`, `ProductStockService`. No toques stock directamente — siempre a través del servicio.

**Facturas → `InvoiceService`**
Centraliza cálculo de VAT/IRPF y numeración en los 5 flujos. No calcules totales inline en Livewire.

---

## Formularios Create / Edit / Index (patrón unificado)

**Todo Create/Edit/Index extiende su clase base de rol**, nunca `Livewire\Component` directo.

Jerarquía: `Shared\Abstract{Create,Edit,Index}` ← `{Rol}\Abstract{Create,Edit,Index}`.
La base resuelve `save()`, `render()`, vista (`resolveViewName`), layout, redirect role-aware
(`resolveIndexRoute`: winery→producer automático) y toasts. El componente solo declara los hooks.

**Create** — declara: `rules()`, `performCreate()`, `successMessage()`, `indexRoute()`, `viewData()`.
Usa `$this->ownerId()` para estampar la propiedad (no `Auth::id()` inline). Sin `save()` ni `render()`.

**Edit** — igual que Create con `performUpdate()`, más `mount(Model $m)` que:
1. autoriza — `$this->authorize('update', $m)` si el modelo tiene Policy (preferido, ver §Autorización);
   si no, `$this->authorizeOwnership($m)` (chequea `ownerColumn()`, default `user_id`).
2. asigna el modelo y rellena las propiedades tipadas.

**Index** — extiende `{Rol}\AbstractIndex`; declara `baseQuery()`, `defaultOrderBy()`, `viewData()`
y opcionalmente `applyFilters()`, `filterDefaults()`, `perPage()`. Ver `docs/patron-vista-listado.md`.

**Columna de propiedad por rol** — la define `ownerColumn()` en el abstract de rol:
Viticulturist→`viticulturist_id`, Winery/Producer→`user_id`. No la repitas en el componente.

**Vista Blade** — `<x-agro.form-card>` → `<x-agro.form-section>` → campos Flux → `<x-agro.form-actions>`.
`form-actions` usa props `:cancel-url` y `:submit-label` (ojo: `submit-:label` NO enlaza).

Referencia canónica: `Winery\EcoCertifications\{Create,Edit,Index}` y `Viticulturist\Certifications\*`.
Migración en curso: ~108 componentes aún extienden `Component` directo (Winery es el grueso).

**Excluidos del patrón** (divergen legítimamente, ya unificados vía servicios): Billing/Invoices
(5 flujos, `InvoiceService`) y wizards multi-paso.

---

## UI

**Componentes Blade en `resources/views/components/`**
Antes de añadir HTML repetido, comprueba si existe un componente: `divider-vertical`, `list-row`, `kpi-tile`, `x-flux::callout`, filter-modals compartidos.

**Clientes → `App\Livewire\Clients\*`**
Role-aware: funciona para Winery y Producer. No crees una copia por rol.

**Colores → variables CSS del sistema**
Usa las variables definidas (`agro-*`, `flux-*`). No uses clases Tailwind de color directamente en componentes nuevos.

---

## Tests

**Patrón de ownership en Winery:** `test_create_rejects_X_from_other_winery()`
**Patrón en Viticulturist:** `test_policy_denies_create_for_other_viticulturist_plot()`
**Patrón en Producer:** `test_producer_cannot_use_another_producers_X()`

Nunca lances la suite completa (`php artisan test` sin filtro) — colapsa MariaDB en Docker. Filtra siempre por archivo o con `--filter`.

Las reglas de ownership de Viticulturist están testeadas de forma unitaria en `OwnershipValidationRulesTest.php`. Si añades una regla nueva al trait `WithOwnershipRules`, añade el test ahí.

---

## PHPStan

Baseline en `phpstan-baseline.neon`. Los 9 errores irreducibles son false positives de invarianza de Collection — no los toques.

Al borrar un archivo PHP, regenera la baseline: `php artisan phpstan:generate-baseline` (o equivalente), de lo contrario los errores del archivo borrado quedan en la baseline como entradas huérfanas.
