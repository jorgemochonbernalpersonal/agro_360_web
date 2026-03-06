# Plan de Implementación: Rol Winery (Bodega)

> Fecha: 2026-03-06
> Basado en: análisis de vinai2 (Seller) + arquitectura existente de agro360
> Principio rector: **inspirado en vinai2, no copiado** — UX Livewire reactivo, foco en
> cumplimiento normativo español, integrado con el cuaderno del viticultor

---

## 1. Contexto y diferencias clave con vinai2

vinai2 (`Seller`) es una plataforma de **marketplace** con e-commerce, POS, affiliate y tienda
pública. El rol `winery` de agro360 es una herramienta de **gestión vitícola y enológica
profesional B2B**.

| Aspecto | vinai2 Seller | agro360 Winery |
|---|---|---|
| Tech stack | Blade + Controllers + jQuery | Livewire + Alpine.js + Tailwind 4 |
| UX | Navegación de página completa | Reactivo, sin recargas |
| Facturación | Marketplace e-commerce | Compra de uva + venta de vino B2B |
| Cuaderno | FieldbookProfileSeller (acceso automático) | Consent flow GDPR explícito |
| Regulatorio | SILICIE básico | SILICIE + libro de bodega digital |
| E-commerce | Tienda pública, productos, carritos | NO — solo gestión interna |
| Contenedores | Básico | Completo con mantenimiento y vista gráfica |

---

## 2. Sidebar implementado

El sidebar es dinámico vía `NavigationHelper::getMenu()`. Para `role = winery`:

```
┌─────────────────────────────────┐
│  [Logo] Agro365                 │
│         Bodega              [◀] │
├─────────────────────────────────┤
│  🏠  Dashboard                  │  ← main (siempre visible)
│  📋  Campañas de Vendimia       │  ← main (siempre visible)
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  VENDIMIA ▾                     │  sección: harvest
│    📥  Recepción de Uva         │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  BODEGA ▾                       │  sección: cellar
│    🧪  Contenedores             │
│    ⇄   Elaboración de Vino      │
│    🔍  Análisis de Lab.         │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  VITICULTORES ▾                 │  sección: viticulturists
│    👥  Mis Viticultores         │
│    🗺️   Parcelas                 │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  RECURSOS ▾                     │  sección: resources
│    🏪  Inventario Insumos       │
│    🚚  Proveedores              │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  FACTURACIÓN ▾                  │  sección: billing
│    ⬇️   Compra de Uva           │
│    ⬆️   Venta de Vino           │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  CLIENTES ▾                     │  sección: clients
│    👤  Clientes                 │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  REGISTRO OFICIAL ▾             │  sección: compliance
│    📊  SILICIE                  │
│         · Panel                 │
│         · Movimientos           │
│    📁  Documentos Bodega        │
├╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌┤
│  SISTEMA ▾                      │  sección: system
│    ⚙️   Configuración           │
├─────────────────────────────────┤
│  [B] Nombre Bodega    [→ salir] │
└─────────────────────────────────┘
```

Cada sección es colapsable con Alpine.js y persiste estado en `localStorage`. Implementado en:
- `app/Helpers/NavigationHelper.php` — bloque `if ($role === 'winery')`
- `resources/views/components/sidebar.blade.php` — secciones `harvest`, `cellar`, `viticulturists` añadidas

---

## 3. Módulos MVP (Fases 1–8)

### 3.1 Reutilización directa (zero new code)

Componentes ya role-aware vía `Plot::forUser()` y `PlotQueryBuilder::forWinery()`:

| Componente | Ruta winery | Estado |
|---|---|---|
| `Livewire\Plots\Index` | `winery.plots.index` | Rutas añadidas en winery.php |
| `Livewire\Plots\Show` | `winery.plots.show` | Rutas añadidas en winery.php |

### 3.2 Árbol de componentes Livewire nuevos

```
app/Livewire/Winery/
├── Dashboard                        ← KPIs: uva recibida, stock contenedores, facturas pendientes
├── Viticulturists/
│   ├── Index                        ← Tabla: nombre, parcelas, última cosecha, badge cuaderno
│   ├── Create                       ← Alta ghost (sin login) o con login. DNI = clave fusión
│   ├── Show                         ← Perfil: parcelas, plantaciones, cosechas, cuaderno (si consent)
│   └── CuadernoView                 ← Vista read-only del cuaderno (guard: cuaderno_access)
├── Campaigns/
│   ├── Index                        ← Campañas de vendimia de la bodega
│   ├── Create
│   └── Edit
├── GrapeReception/                  ← Entrada de uva de viticultores
│   ├── Index                        ← Filtros: campaña, viticulturist, variedad, estado
│   ├── Create                       ← Viticultor, parcela, kg, calidad, precio → factura auto
│   ├── Edit
│   └── Show                         ← Detalle con vinculación a contenedor y cosecha del viticulturist
├── Containers/                      ← Depósitos, barricas, tanques de la bodega
│   ├── Index                        ← Lista + vista gráfica (grid con colores según estado)
│   ├── Create
│   ├── Edit
│   ├── Show                         ← Contenido actual, historial de movimientos
│   └── Maintenance/
│       ├── Index
│       └── Create
├── WineProcess/                     ← Procesos enológicos
│   ├── Index                        ← Todos los procesos por campaña y tipo
│   ├── Create                       ← Tipo: fermentación, trasiego, clarificación, filtración, crianza
│   ├── Edit
│   └── Show                         ← Contenedores origen/destino, insumos usados, análisis vinculados
├── WineAnalysis/                    ← Análisis de laboratorio
│   ├── Index
│   ├── Create                       ← Parámetros: alcohol, acidez, SO2, pH, azúcares... + adjunto PDF
│   └── Edit
├── Inventory/                       ← Insumos enológicos (sulfuroso, levaduras, taninos, bentonita...)
│   ├── Index                        ← Tabs: stock actual / historial compras / alertas stock mínimo
│   ├── CreateStock                  ← Dar de alta insumo con cantidad inicial
│   ├── EditStock
│   └── ConsumeStock                 ← Vincular consumo a proceso de elaboración
├── Suppliers/                       ← Proveedores de insumos (NO viticultores)
│   ├── Index
│   ├── Create
│   └── Edit
├── Clients/                         ← Compradores de vino (distribuidores, restaurantes, particulares)
│   ├── Index
│   ├── Create
│   ├── Edit
│   └── Show
├── Invoices/
│   ├── GrapePurchase/               ← Facturas de compra de uva a viticultores
│   │   ├── Index
│   │   ├── Create                   ← Auto-genera desde GrapeReception
│   │   └── Edit
│   └── WineSale/                    ← Facturas de venta de vino a clientes
│       ├── Index
│       ├── Create
│       └── Edit
├── Silicie/                         ← Libro de bodega digital (obligatorio por ley en España)
│   ├── Dashboard                    ← Movimientos pendientes de declarar + estado
│   └── Movements/
│       ├── Index
│       └── Create
├── Documents/                       ← Repositorio documental (certificados, DOP, contratos...)
│   └── Index
└── Settings                         ← Datos bodega: REGA, RE, NIF, configuración facturación
```

---

## 4. Módulos de Fase 2 (vinai2 tiene equivalente, pospuestos al MVP)

Estos módulos están inspirados en vinai2 pero se posponen para evitar scope creep en el MVP.
Son funcionalidades reales de bodega que **sí se implementarán**, pero después de tener
el core funcionando.

### 4.1 Embotellado — `WineBottling`
vinai2: `WineBottlingController` + `WineBottling` model
agro360 Fase 2: registro de lotes de embotellado por campaña, vinculado a proceso de elaboración.
Campos: fecha, contenedor origen, nº botellas, formato (75cl, 150cl...), lote, pérdidas.

### 4.2 Etiquetado — `WineLabeling` + `WineLabel`
vinai2: `WineLabelingController` + `LabelBatchController` + `LabelTemplateController`
agro360 Fase 2: gestión de etiquetas por lote embotellado. Vinculación con solicitudes al
Consejo Regulador (DO). Requiere embotellado implementado primero.

### 4.3 Composición Varietetal — `WineComposition`
vinai2: `WineCompositionController` + `WineComposition` model
agro360 Fase 2: porcentaje de cada variedad de uva que compone un vino. Se genera desde
las entradas de uva (GrapeReceptions) asociadas al proceso. Necesario para la ficha técnica
del vino y la declaración SILICIE completa.

### 4.4 Mermas — `WineLoss`
vinai2: `WineLossController` + `WineLoss` model
agro360 Fase 2: registro de mermas por evaporación, filtración, trasiego. Vinculado a
contenedor y proceso. Necesario para el balance de masas del SILICIE.

### 4.5 Control de Fermentación — `WineFermentationControl`
vinai2: `WineFermentationControlController`
agro360 Fase 2: registro de parámetros diarios durante la fermentación (Baumé, temperatura,
densidad, notas). Vinculado a contenedor. Puede generar gráfica de evolución.

### 4.6 Notas de Cata — `WineTastingNote`
vinai2: `WineTastingNoteController`
agro360 Fase 2: registro de catas internas por lote/proceso. Campos: vista, nariz, boca,
puntuación. No público (diferencia con vinai2 que las publicaba en tienda).

### 4.7 Subproductos — `WineSubproduct`
vinai2: `WineSubproductController` + `WineSubproduct` model
agro360 Fase 2: orujos, lías, mostos concentrados. Registro de destino (destilería, abono,
compostaje). Relevante para la declaración de subproductos del SILICIE.

### 4.8 Uva Externa — `ExternalGrape`
vinai2: `ExternalGrapeController`
agro360 Fase 2: entrada de uva de viticultores que NO están en el sistema (proveedores
ocasionales sin cuenta). Diferente a `GrapeReception` que requiere viticulturist registrado.

### 4.9 Enólogo — `Oenologist`
vinai2: `OenologistController`
agro360 Fase 2: registro del técnico enológico responsable. Datos: nombre, nº colegiado,
firma digital. Aparece en documentos oficiales y en la declaración de elaboración.

### 4.10 Certificación de Parcelas — `PlotCertification`
vinai2: `PlotCertificationController`
agro360 Fase 2: gestión de certificaciones de parcelas de proveedores (ecológico, DOP, IPG).
Vinculado a PlotPlanting del viticulturist. Necesario para declarar el origen del vino.

### 4.11 Ficha Técnica del Vino — `WineDocument`
vinai2: `WineDocumentController`
agro360 Fase 2: documento oficial del vino con composición, analíticas, etiquetado.
Generación PDF. Necesario para exportación y relación con el Consejo Regulador.

### 4.12 Transferencias entre bodegas — `WineTransfer`
vinai2: `WineTransferController`
agro360 Fase 2: trasvase de vino entre bodegas del mismo grupo o venta a granel.
Genera movimiento en SILICIE y albarán de trasvase.

### 4.13 Estimaciones de Rendimiento — `YieldEstimation`
vinai2: `YieldEstimationController`
agro360 Fase 2: predicción de kg de uva esperados antes de la vendimia por parcela y
variedad. Integra datos de las EstimatedYields del viticulturist si tiene acceso al cuaderno.

---

## 5. Módulos de vinai2 excluidos definitivamente

No aplican en el contexto de agro360 winery:

| Módulo vinai2 | Razón |
|---|---|
| E-commerce / tienda pública | agro360 es gestión interna, no marketplace |
| POS (punto de venta) | Fuera de scope |
| Affiliate / comisiones | No aplica |
| Club points / fidelización | No aplica |
| Wholesale pricing | No aplica |
| Auction | No aplica |
| Chat Seller↔Supervisor | Se implementa con el rol DO, no antes |
| Infovi (integración MAPAMA) | Específico de vinai2, complejo, no prioritario |
| Fenología | Solo en viticulturist — bodega no gestiona viñedo |
| Cuaderno de campo propio | Bodega solo LEE el cuaderno del viticulturist (con consent) |

---

## 6. Inventario de Insumos — diseño detallado

A diferencia del viticulturist (fitosanitarios + insumos agrícolas), la bodega gestiona
**insumos enológicos**. El flujo es:

```
1. Crear insumo (catálogo propio)
   → nombre, tipo, unidad, stock_mínimo, proveedor habitual

2. Registrar compra (entrada de stock)
   → cantidad, precio, nº albarán, proveedor
   → sube stock automáticamente
   → opcionalmente genera factura de compra al proveedor

3. Consumir en proceso de elaboración
   → al crear/editar un WineProcess se puede vincular insumos usados
   → baja stock automáticamente

4. Alertas
   → badge en sidebar si hay insumos por debajo del stock mínimo
   → listado en dashboard
```

Tipos de insumo (`supply_type`):
- `enologico` — SO2, levaduras, bacterias, enzimas, taninos, bentonita, gelatina, goma arábiga
- `limpieza` — detergentes, desinfectantes, sosa cáutica
- `laboratorio` — reactivos, tiras medidoras, soluciones tampón
- `envases` — botellas, corchos, cápsulas, cajas (Fase 2)
- `otro`

Nueva tabla `winery_supplies`:
```sql
CREATE TABLE winery_supplies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    winery_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    commercial_name VARCHAR(150) NULL,
    supply_type VARCHAR(30) NOT NULL DEFAULT 'enologico',
    unit VARCHAR(20) NOT NULL DEFAULT 'kg',   -- kg, L, ud, g, mL
    current_stock DECIMAL(10,3) NOT NULL DEFAULT 0,
    minimum_stock DECIMAL(10,3) NULL,
    unit_price DECIMAL(10,4) NULL,
    supplier_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (winery_id) REFERENCES users(id),
    FOREIGN KEY (supplier_id) REFERENCES winery_suppliers(id)
);

CREATE TABLE winery_supply_purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    winery_id BIGINT UNSIGNED NOT NULL,
    supply_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NULL,
    purchase_date DATE NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,4) NULL,
    total_cost DECIMAL(12,2) NULL,
    invoice_number VARCHAR(100) NULL,
    supplier_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (winery_id) REFERENCES users(id),
    FOREIGN KEY (supply_id) REFERENCES winery_supplies(id)
);
```

---

## 7. Arquitectura técnica: Shared Abstract Layer

### 7.1 Problema actual

Los abstract base classes están bajo namespace `App\Livewire\Viticulturist`:
- `viticulturistId()` → retorna `Auth::id()` (semánticamente incorrecto para Winery)
- `authorizeOwnership()` compara `$model->viticulturist_id` (falla en modelos con `winery_id`)

### 7.2 Solución: Shared namespace

```
app/Livewire/
├── Shared/
│   ├── AbstractIndex.php      ← ownerId(), ownerField() genéricos
│   ├── AbstractCreate.php
│   └── AbstractEdit.php
├── Viticulturist/
│   ├── AbstractIndex.php      ← extends Shared (BC wrapper, override ownerField → 'viticulturist_id')
│   ├── AbstractCreate.php
│   └── AbstractEdit.php
└── Winery/
    ├── AbstractIndex.php      ← extends Shared (override ownerField → 'winery_id')
    ├── AbstractCreate.php
    └── AbstractEdit.php
```

---

## 8. Base de datos — migraciones requeridas

### MVP (Fases 1–8)

| Migración | Descripción |
|---|---|
| `alter_campaigns_add_winery_id` | `winery_id` nullable en campaigns |
| `alter_containers_add_winery_id` | `winery_id` nullable, `viticulturist_id` nullable |
| `create_grape_receptions` | Tabla de entradas de uva |
| `create_wine_processes` | Procesos enológicos |
| `create_wine_analyses` | Análisis de laboratorio |
| `create_winery_supplies` | Catálogo de insumos bodega |
| `create_winery_supply_purchases` | Compras de insumos |
| `create_winery_suppliers` | Proveedores de la bodega |
| `create_cuaderno_access_requests` | Consentimientos GDPR cuaderno |
| `alter_invoices_add_type` | Campo `type` en invoices (sale / grape_purchase) |

### Fase 2

| Migración | Descripción |
|---|---|
| `create_wine_bottlings` | Lotes de embotellado |
| `create_wine_labelings` | Etiquetado por lote |
| `create_wine_labels` | Diseños de etiqueta |
| `create_wine_compositions` | Composición varietetal |
| `create_wine_losses` | Mermas |
| `create_wine_fermentation_controls` | Control diario de fermentación |
| `create_wine_tasting_notes` | Notas de cata internas |
| `create_wine_subproducts` | Orujos, lías, subproductos |
| `create_external_grapes` | Uva de proveedores externos |
| `create_oenologists` | Enólogos responsables |
| `create_plot_certifications_winery` | Certificaciones parcelas proveedores |
| `create_wine_documents` | Fichas técnicas del vino |
| `create_wine_transfers` | Trasvases entre bodegas |
| `create_yield_estimations_winery` | Estimaciones de vendimia |

---

## 9. Policies y autorización

| Policy | Acciones winery |
|---|---|
| `PlotPolicy` | `viewAny`, `view` — NO create/update/delete |
| `CampaignPolicy` | CRUD completo sobre campañas propias (`winery_id`) |
| `ContainerPolicy` | CRUD completo sobre contenedores propios (`winery_id`) |
| `GrapeReceptionPolicy` | CRUD completo |
| `WineProcessPolicy` | CRUD completo |
| `WineAnalysisPolicy` | CRUD completo |
| `InvoicePolicy` | CRUD facturas propias (type: sale + grape_purchase) |
| `ClientPolicy` | CRUD sobre clientes propios |
| `CuadernoAccessPolicy` | Solo puede *solicitar* — el viticulturist aprueba/revoca |

---

## 10. Rutas — estado actual de `routes/winery.php`

Las rutas están implementadas. Los módulos pendientes redirigen al dashboard (stub) hasta
que se implementen. Las rutas de Plots reutilizan los componentes del viticulturist
directamente (role-aware).

---

## 11. Orden de implementación

### FASE 0 — Shared Abstract Layer (~1 día)
- Crear `Shared\AbstractIndex/Create/Edit`
- Viticulturist Abstracts pasan a ser thin wrappers (BC garantizado)
- Crear `Winery\AbstractIndex/Create/Edit`
- Correr test suite completo

### FASE 1 — Viticultores + Parcelas (~3 días)
- `Winery\Viticulturists\Index/Create/Show`
- `Winery\Viticulturists\CuadernoView` (read-only con guard)
- Panel de consentimientos GDPR (solicitar / ver estado / revocar)
- Parcelas reutiliza `Plots\Index/Show` (rutas ya añadidas)

### FASE 2 — Campañas + Recepción de Uva (~3 días)
- Migraciones: `campaigns.winery_id` + `grape_receptions`
- `Winery\Campaigns\*`
- `Winery\GrapeReception\*`
- Auto-generación factura de compra al confirmar entrada

### FASE 3 — Contenedores (~3 días)
- Migración: `containers.winery_id` nullable + `ContainerQueryBuilder::forWinery()`
- `Winery\Containers\*` con CRUD + mantenimiento
- Vista gráfica de bodega (grid con estado)

### FASE 4 — Elaboración + Análisis (~3 días)
- Migraciones: `wine_processes` + `wine_analyses`
- `Winery\WineProcess\*`
- `Winery\WineAnalysis\*`
- Vinculación proceso ↔ contenedores ↔ insumos ↔ análisis

### FASE 5 — Inventario + Proveedores (~2 días)
- Migraciones: `winery_supplies` + `winery_supply_purchases` + `winery_suppliers`
- `Winery\Inventory\*` con tabs: stock / compras / alertas
- `Winery\Suppliers\*`

### FASE 6 — Clientes + Facturación (~3 días)
- `Winery\Clients\*`
- `Winery\Invoices\GrapePurchase\*`
- `Winery\Invoices\WineSale\*`
- `WineryInvoicePdfController` (DOMPDF)

### FASE 7 — SILICIE + Documentos (~3 días)
- `Winery\Silicie\Dashboard` + `Movements\*`
- Libro de bodega digital
- Exportación XML SILICIE

### FASE 8 — Dashboard + Onboarding + Settings (~2 días)
- Dashboard completo: KPIs, mapa Leaflet parcelas, gráfico uva/campaña
- Onboarding winery (checklist estilo viticulturist)
- `Winery\Settings` (REGA, RE, NIF, facturación)

---

## 12. Tests

Crear `tests/Feature/WineryTestCase.php`:
- Setup: user `winery` + 3 viticultores asignados + parcelas + plantaciones

| Test | Cubre |
|---|---|
| `WineryViticulturistTest` | Listado, alta ghost, consent flow cuaderno |
| `WineryGrapeReceptionTest` | CRUD, validaciones, auto-factura |
| `WineryCampaignTest` | Ciclo de vida campaña vendimia |
| `WineryContainerTest` | CRUD, cambio contenido, mantenimiento |
| `WineryWineProcessTest` | Trasiegos entre contenedores, consumo insumos |
| `WineryInventoryTest` | Alta insumo, compra, consumo, alerta stock |
| `WineryInvoiceTest` | Factura compra uva + venta vino, PDF |
| `WinerySilicieTest` | Registro movimientos, balance |

---

## 13. Decisiones de diseño

1. **`GrapeReception` como entidad central** — en vinai2 está disperso en `FieldActivity` +
   `HarvestStock`. En agro360 es una tabla propia que vincula viticulturist → bodega con
   trazabilidad completa y FK a la cosecha del viticulturist para detectar discrepancias.

2. **`winery_supplies` separado de `ProductStock`** — el viticulturist usa `ProductStock`
   ligado a `PhytosanitaryProduct`. Los insumos de bodega son categoría distinta
   (enológicos vs fitosanitarios). Tabla propia evita acoplar contextos.

3. **`WineProcess` es el núcleo de Fase 4** — recoge todos los tipos de proceso
   (fermentación, trasiego, clarificación, filtración, crianza) en una sola tabla con `type`.
   Los procesos más especializados (fermentation_control diario, bottling) van a Fase 2
   como módulos separados.

4. **Contenedores: `viticulturist_id` y `winery_id` ambos nullable** — la bodega tiene
   depósitos/barricas propios, el viticulturist tiene bins/cisternas. Misma tabla, contexto
   diferente. Exactamente uno de los dos campos debe estar relleno (validado en app layer).

5. **Consent GDPR explícito para cuaderno** — vinai2 activa el acceso automáticamente vía
   `FieldbookProfileSeller`. En agro360 el viticulturist controla completamente quién ve
   su cuaderno. La bodega *solicita*, el viticulturist *aprueba o rechaza*, y puede
   *revocar* en cualquier momento sin afectar la activación de la cuenta.

6. **SILICIE sí, Infovi no (MVP)** — SILICIE es obligatorio por ley para bodegas.
   Infovi es una integración específica de vinai2 con MAPAMA, compleja y no prioritaria.
