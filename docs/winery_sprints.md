# Winery Role — Plan de Sprints

## Estado general

```
Fase 0  ✅  Shared Abstract Layer
Sprint 1 ✅  Viticultores
Sprint 2 ✅  Recepción de Uva
Sprint 3 🔄  Contenedores (en curso)
Sprint 4 ⏳  Stock de Vino + Facturación
```

---

## Fase 0 — Shared Abstract Layer ✅ COMPLETADO

**Objetivo:** Extraer lógica genérica de los abstracts de Viticulturist para reutilizarla en Winery.

### Ficheros creados/modificados

| Fichero | Estado |
|---|---|
| `app/Livewire/Shared/AbstractIndex.php` | ✅ Nuevo |
| `app/Livewire/Shared/AbstractCreate.php` | ✅ Nuevo |
| `app/Livewire/Shared/AbstractEdit.php` | ✅ Nuevo |
| `app/Livewire/Viticulturist/AbstractIndex.php` | ✅ Refactorizado (hereda Shared) |
| `app/Livewire/Viticulturist/AbstractCreate.php` | ✅ Refactorizado (hereda Shared) |
| `app/Livewire/Viticulturist/AbstractEdit.php` | ✅ Refactorizado (hereda Shared) |
| `app/Livewire/Winery/AbstractIndex.php` | ✅ Nuevo |

---

## Sprint 1 — Viticultores + Parcelas 🔄 EN CURSO

**Objetivo:** La bodega puede ver sus viticultores, sus parcelas y las plantaciones por variedad de uva.
No crea parcelas (eso lo hace el viticultor), solo las visualiza.

### Notas clave
- SIGPAC y multiplot SIGPAC ya funcionan — `Plots\Index` y `Plots\Show` son role-aware via `Plot::forUser()`
- Las rutas `/winery/plots` y `/winery/plots/{plot}` ya apuntan a los componentes compartidos
- La bodega NO puede crear/editar parcelas del viticultor (solo view)

### Tareas

#### 1.1 — Index de viticultores ✅ COMPLETADO
| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Viticulturists/Index.php` | ✅ |
| `resources/views/livewire/winery/viticulturists/index.blade.php` | ✅ |
| `routes/winery.php` — ruta viticulturists.index | ✅ |

#### 1.2 — Show del viticultor ✅ COMPLETADO
Muestra el detalle de un viticultor: datos, sus parcelas y por cada parcela las plantaciones (variedad, ha, año).

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Viticulturists/Show.php` | ✅ |
| `resources/views/livewire/winery/viticulturists/show.blade.php` | ✅ |
| `routes/winery.php` — ruta viticulturists.show | ✅ |

**Datos a mostrar:**
- Cabecera: nombre, email, estado acceso, origen (own/supervisor/self)
- Cards de stats: nº parcelas, ha totales, nº plantaciones, kg límite total
- Tabla de parcelas con: nombre, municipio, ha, SIGPAC, nº plantaciones
- Por cada parcela → plantaciones: variedad, ha plantadas, año, kg/ha límite, estado

#### 1.3 — Crear viticultor (ghost) ✅ COMPLETADO
La bodega crea un viticultor sin login (`can_login = false`) para registrar sus datos y parcelas.
Cuando el viticultor se registre con el mismo DNI, las cuentas se fusionan.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Viticulturists/Create.php` | ✅ |
| `resources/views/livewire/winery/viticulturists/create.blade.php` | ✅ |
| `routes/winery.php` — ruta viticulturists.create | ✅ |
| `database/migrations/2026_03_06_000001_add_dni_to_users_table.php` | ✅ |
| `User.$fillable` — campo `dni` añadido | ✅ |
| `app/Livewire/Winery/AbstractCreate.php` | ✅ |

**Campos del formulario:**
- Nombre, DNI (campo fusion), email (opcional), teléfono (opcional)
- `can_login = false` por defecto
- Crea `User` + `WineryViticulturist` con `source = own`, `assigned_by = winery_id`

#### 1.4 — Invitar viticultor existente ✅ COMPLETADO
Buscar un viticultor registrado por DNI/email y vincularlo a la bodega.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Viticulturists/Invite.php` | ✅ |
| `resources/views/livewire/winery/viticulturists/invite.blade.php` | ✅ |
| `routes/winery.php` — ruta viticulturists.invite | ✅ |

---

## Sprint 2 — Recepción de Uva 🔄 EN CURSO

**Objetivo:** La bodega registra la entrada de uva: qué viticultor, de qué parcela, de qué plantación (variedad), cuántos kg y calidad.

### Flujo de datos
```
Bodega crea Campaña de Vendimia
    → Recepción de Uva (por viticultor / parcela / plantación)
        → crea AgriculturalActivity (campaign_id, type=harvest)
        → crea Harvest (plot_planting_id, total_weight, calidad, precio_kg)
```

### Notas clave
- `Harvest` ya tiene todos los campos necesarios (`baume_degree`, `brix_degree`, `acidity_level`, `ph_level`, sanitary states)
- `Harvest.destination_type` se usará para distinguir uva propia vs comprada
- `AgriculturalActivity` necesita `campaign_id` → la bodega gestiona sus propias campañas

### Tareas

#### 2.1 — Campañas de Vendimia ✅ COMPLETADO
| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Harvest/Campaigns/Index.php` | ✅ |
| `app/Livewire/Winery/Harvest/Campaigns/Create.php` | ✅ |
| `resources/views/livewire/winery/harvest/campaigns/` | ✅ |
| `routes/winery.php` — campaigns.index / campaigns.create | ✅ |

**Campos:** nombre, año, fecha inicio/fin, descripción, estado (abierta/cerrada)

#### 2.2 — Recepción de Uva (Index) ✅ COMPLETADO
Lista de todas las recepciones de una campaña con stats: kg totales, nº entradas, baumé medio.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Harvest/Reception/Index.php` | ✅ |
| `resources/views/livewire/winery/harvest/reception/index.blade.php` | ✅ |
| `routes/winery.php` — grape-reception.index | ✅ |

#### 2.3 — Nueva Recepción (Create) ✅ COMPLETADO
Selector encadenado: campaña → viticultor → parcela → plantación → datos de calidad.
Control de límite kg/ha en tiempo real. Container = null (asignado en Sprint 3).

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Harvest/Reception/Create.php` | ✅ |
| `resources/views/livewire/winery/harvest/reception/create.blade.php` | ✅ |

**Campos:**
- Campaña (selector)
- Viticultor (selector — solo los de la bodega)
- Parcela (selector — filtrado por viticultor)
- Plantación (selector — filtrado por parcela, muestra variedad + ha)
- Fecha recepción, nº ticket
- Kg recibidos, precio/kg
- Calidad: grado baumé, brix, acidez, pH
- Estado sanitario: % granos, agraces, botrytis, oidio, mildiu
- Vehículo, nº documento transporte

**Transacción:**
```php
DB::transaction(function() {
    $activity = AgriculturalActivity::create([...campaign_id, type='harvest']);
    $harvest   = Harvest::create([...activity_id, plot_planting_id, total_weight, ...]);
});
```

#### 2.4 — Boleto de Entrada PDF ⏳
PDF con los datos de la recepción para entregar al viticultor.

| Fichero | Estado |
|---|---|
| `app/Http/Controllers/Winery/HarvestTicketController.php` | ⏳ |
| `resources/views/pdf/winery/harvest-ticket.blade.php` | ⏳ |

---

## Sprint 3 — Contenedores 🔄 EN CURSO

**Objetivo:** La bodega gestiona sus depósitos y barricas. Recibe uva de las recepciones y la asigna a contenedores.

### Notas clave
- Modelo `Container` ya existe — `user_id` = bodega, `capacity`/`used_capacity` en kg
- `Harvest.container_id` ya linkea cosecha a contenedor
- `HarvestObserver::updating()` gestiona automáticamente `ContainerStockService::transferContainer()`
- `Container.containerType()` relación añadida — tipos: Barrica, Depósito, Tanque, Tina, Ánfora

### Tareas

#### 3.1 — Index de Contenedores ✅ COMPLETADO
Lista con % llenado usando `x-agro.progress-bar`, tipo, capacidad/ocupación. Filtros: búsqueda, tipo, estado (activo/archivado).

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Cellar/Containers/Index.php` | ✅ |
| `resources/views/livewire/winery/cellar/containers/index.blade.php` | ✅ |
| `routes/winery.php` — containers.index | ✅ |

#### 3.2 — Crear/Editar Contenedor ✅ COMPLETADO
| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Cellar/Containers/Create.php` | ✅ |
| `app/Livewire/Winery/Cellar/Containers/Edit.php` | ✅ |
| `resources/views/livewire/winery/cellar/containers/` | ✅ |

**Campos:** nombre, tipo (ContainerType select), capacidad (kg), nº serie, fecha compra, proveedor, descripción.
Guard en Edit: capacidad no puede bajar por debajo de lo ya utilizado.

#### 3.3 — Asignar Uva a Contenedor ✅ COMPLETADO
Desde reception index (botón por fila) → página `grape-reception/{harvest}/assign`.
Actualiza `Harvest.container_id` → `HarvestObserver` llama `ContainerStockService::transferContainer()`.
Muestra preview del nivel de ocupación post-asignación.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Harvest/Reception/Assign.php` | ✅ |
| `resources/views/livewire/winery/harvest/reception/assign.blade.php` | ✅ |
| `routes/winery.php` — grape-reception.assign | ✅ |
| Reception index — columna Contenedor + botón asignar | ✅ |

#### 3.4 — Elaboración de Vino ⏳
Registro de procesos sobre el contenedor: sulfitado, remontado, trasiego, clarificación, filtración.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Cellar/WineProcess/Index.php` | ⏳ |
| `app/Livewire/Winery/Cellar/WineProcess/Create.php` | ⏳ |

#### 3.5 — Análisis de Laboratorio ⏳
Análisis asociados a un contenedor: fecha, parámetros (alcohol, acidez volátil, SO2, etc.), resultado, laboratorio.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Cellar/WineAnalysis/Index.php` | ⏳ |
| `app/Livewire/Winery/Cellar/WineAnalysis/Create.php` | ⏳ |

---

## Sprint 4 — Stock de Vino + Facturación ⏳

**Objetivo:** Cerrar el ciclo: convertir uva procesada en stock de vino y generar facturas de venta.

### Flujo de datos
```
Contenedor (uva procesada)
    → embotellado / envasado
        → HarvestStock (litros disponibles por variedad/tipo)
            → InvoiceItem → Invoice (cliente)
```

### Notas clave
- `HarvestStock` ya existe con campos: `available_qty`, `reserved_qty`, `sold_qty`, `gifted_qty`, `lost_qty`
- `Harvest.isInvoiced()` ya detecta si está facturada
- El modelo `Invoice` e `InvoiceItem` ya existen (usados por viticulturist)
- Para facturas de **compra de uva** (pago al viticultor): usar `Harvest.price_per_kg * total_weight`
- Para facturas de **venta de vino** (cobro al cliente): usar `HarvestStock`

### Tareas

#### 4.1 — Gestión de Stock (entrada de vino) ⏳
Registrar litros de vino resultantes de un contenedor tras elaboración.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Cellar/Stock/Index.php` | ⏳ |
| `app/Livewire/Winery/Cellar/Stock/Create.php` | ⏳ |

#### 4.2 — Clientes ⏳
CRUD de clientes de la bodega (personas/empresas que compran vino).

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Clients/Index.php` | ⏳ |
| `app/Livewire/Winery/Clients/Create.php` | ⏳ |
| `app/Livewire/Winery/Clients/Edit.php` | ⏳ |

#### 4.3 — Facturación: Compra de Uva ⏳
Factura que la bodega emite al viticultor como justificante del precio pagado por la uva.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Billing/GrapePurchase/Index.php` | ⏳ |
| `app/Livewire/Winery/Billing/GrapePurchase/Create.php` | ⏳ |

**Datos:** viticultor, recepciones incluidas (líneas), kg, precio/kg, total, retención IRPF

#### 4.4 — Facturación: Venta de Vino ⏳
Factura de venta de vino al cliente.

| Fichero | Estado |
|---|---|
| `app/Livewire/Winery/Billing/WineSale/Index.php` | ⏳ |
| `app/Livewire/Winery/Billing/WineSale/Create.php` | ⏳ |

**Datos:** cliente, líneas de stock (variedad/tipo, litros, precio/litro), IVA, total

---

## Sprints futuros (post-MVP)

| Sprint | Módulo | Descripción |
|---|---|---|
| 5 | Recursos | Inventario de insumos enológicos + Proveedores |
| 6 | SILICIE | Panel + movimientos de libro de bodega digital |
| 7 | Documentos | Gestión documental de la bodega |
| 8 | Tests | Feature tests para todos los módulos winery |

---

## Notas arquitectónicas

### Reutilización de código
- `Plot::forUser()` — ya filtra por rol, winery ve parcelas de sus viticultores
- `Plots\Index` y `Plots\Show` — compartidos, SIGPAC y multiplot SIGPAC incluidos
- `x-agro.*` — todos los componentes UI disponibles para winery
- `Shared\Abstract*` — base para todos los Livewire de winery

### Patrón de autorización en winery
```php
// En cada baseQuery(), siempre filtrar por winery
WineryViticulturist::where('winery_id', $this->wineryId())

// Para harvests de la bodega:
Harvest::whereHas('plotPlanting.plot', fn($q) =>
    $q->whereHas('viticulturist.wineryRelationsAsViticulturist', fn($q2) =>
        $q2->where('winery_id', $this->wineryId())
    )
)
```

### Nomenclatura de rutas winery
```
winery.viticulturists.index / show / create
winery.campaigns.index / create
winery.grape-reception.index / create
winery.containers.index / create / edit
winery.wine-process.index / create
winery.wine-analysis.index / create
winery.invoices.grape-purchase.index / create
winery.invoices.wine-sale.index / create
winery.clients.index / create / edit
```
