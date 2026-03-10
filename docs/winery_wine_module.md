# Módulo Wine — Winery (Agro365)

> Referencia: análisis vinai2 `Seller` (10-03-2026)
> Objetivo: completar el módulo de vinos de la bodega paso a paso

---

## Estado actual

El módulo wine tiene backend + frontend básico implementado:

| Componente | Estado |
|---|---|
| `Wine` model (básico) | ✅ |
| `WineProcessDetail` | ✅ |
| `WineTransfer` (from/to container) | ✅ |
| `WineLoss` | ✅ |
| `WineAnalysis` (11 params) | ✅ |
| `WineFermentationControl` | ✅ |
| Livewire: Index, Create, Edit, Show, Process/Create | ✅ |
| Rutas winery: /wines, /wines/create, /wines/{wine}, /wines/{wine}/edit | ✅ |

---

## Gaps vs vinai2

### Modelo Wine — campos faltantes

vinai2 `Wine` tiene campos que Agro365 no tiene:

| Campo | vinai2 | Agro365 | Acción |
|---|---|---|---|
| `is_must` | ✅ (mosto vs vino) | ❌ | Añadir campo boolean |
| `oenologist_id` | ✅ FK a Oenologist | ❌ | Fase 2 (requiere modelo Oenologist) |
| `category_id` | ✅ (DO, IGP, VdM...) | ❌ | Añadir enum o FK |
| `aging_type` | ✅ (joven, roble, reserva...) | ❌ | Añadir enum |
| `initial_quantity` | ✅ decimal kg entrada | ❌ | Añadir campo |
| `is_organic` | ✅ | ❌ | Añadir boolean |
| `archived` | ✅ | usa `status=cancelled` | Evaluar |

---

## Módulos a implementar (por orden)

### PASO 1 — Composición Varietetal `WineComposition` ✅
**¿Qué es?** Trazabilidad de qué uva (variedad, origen) compone cada vino y en qué %.

vinai2: `WineCompositionController` + `WineComposition` model
- Sources: `harvest` (cosecha propia del viticultor), `external_grape` (uva de proveedor externo), `source_wine` (mezcla con otro vino)
- Campos: `wine_id`, `source_type`, `source_wine_id`, `harvest_id`, `external_grape_id`, `grape_variety_id`, `quantity`, `percent_grapes`, `unit_of_measurement_id`

**En Agro365:**
- Ya existe el pivot `wine_harvests` (wine_id, harvest_id, quantity_kg, percentage) — es la base
- Falta: tabla `wine_compositions` completa + vinculación a External Grape
- Necesario para: ficha técnica del vino, declaración SILICIE, DOP

**Tareas:**
- [ ] Migración `create_wine_compositions`
- [ ] Model `WineComposition`
- [ ] Livewire `Winery\Wines\Composition\Create` (vincular cosechas al vino)
- [ ] Mostrar composición en `show.blade.php` (tab o sección)

---

### PASO 2 — Embotellado `WineBottling` ❌
**¿Qué es?** Registro del proceso de embotellado de un lote de vino.

vinai2: `WineBottlingController` + `WineBottling` + `WineBottlingSupply` + `BottledWineInventory`
- Campos: `wine_id`, `wine_process_detail_id`, `bottling_date`, `num_bottles`, `bottle_format` (75cl, 150cl...), `lot_number`, `losses_liters`
- Insumos: botellas, corchos, cápsulas — se descuentan del stock de insumos

**En Agro365:**
- Ningún modelo de embotellado existe
- Necesario para: facturación de venta de vino, etiquetado, trazabilidad

**Tareas:**
- [ ] Migración `create_wine_bottlings`
- [ ] Migración `create_bottled_wine_inventory`
- [ ] Model `WineBottling`
- [ ] Model `BottledWineInventory`
- [ ] Livewire `Winery\Wines\Bottling\Create` / `Index`
- [ ] Vincular a lote de producto (para facturación)

---

### PASO 3 — Aditivos `WineAdditive` ✅
**¿Qué es?** Registro de aditivos/coadyuvantes usados en cada etapa del proceso.

vinai2: `WineAdditiveController` + `WineAdditive` + `WineAdditiveSupply`
- Campos: `wine_process_detail_id`, `additive_type`, `quantity`, `unit_of_measurement_id`, `application_date`, `oenologist_id`
- Tipos: SO₂, levaduras, bacterias lácticas, enzimas, taninos, bentonita, gelatina, goma arábiga, ácido tartárico, otro
- Se vincula al inventario de insumos (`WinerySupply`) para descontar stock

**En Agro365:**
- Ningún modelo de aditivos existe
- `WinerySupply` ya existe (catálogo de insumos de bodega) — solo falta el consumo
- Necesario para: registro SILICIE (los aditivos son declarables)

**Tareas:**
- [ ] Migración `create_wine_additives`
- [ ] Model `WineAdditive`
- [ ] Livewire `Winery\Wines\Additives\Create` (inline en show o modal)
- [ ] Al guardar aditivo → descuenta de `WinerySupply.current_stock`

---

### PASO 4 — Enólogo `Oenologist` ✅
**¿Qué es?** Modelo del técnico enológico responsable de la elaboración.

vinai2: `OenologistController` + `Oenologist` model
- Campos: `user_id`, `name`, `surname`, `license_number`, `phone`, `email`, `signature` (imagen)
- Una bodega puede tener varios enólogos registrados
- Se asigna a: procesos, análisis, trasiegos, embotelladosy documentos oficiales

**En Agro365:**
- No existe — procesos y análisis no tienen responsable asignado
- Necesario para: documentos oficiales, ficha técnica, declaración SILICIE

**Tareas:**
- [x] Migración `create_oenologists`
- [x] Model `Oenologist` (belongsTo User/winery)
- [x] Livewire `Winery\Oenologists\Index` / `Create` / `Edit`
- [ ] Añadir `oenologist_id` nullable a: `wine_process_details`, `wine_analyses`, `wine_transfers`, `wine_losses`
- [ ] Selectores de enólogo en formularios de los módulos anteriores

---

### PASO 5 — Notas de Cata `WineTastingNote` ❌
**¿Qué es?** Evaluación organoléptica interna de un lote/proceso.

vinai2: `WineTastingNoteController` + `WineTastingNote`
- Campos: `wine_process_detail_id`, `tasting_date`, `oenologist_id`, `appearance`, `nose`, `palate`, `finish`, `overall_score`, `notes`
- Uso interno — no se publica (diferencia con vinai2 que lo mostraba en tienda)

**Tareas:**
- [ ] Migración `create_wine_tasting_notes`
- [ ] Model `WineTastingNote`
- [ ] Livewire `Winery\Wines\TastingNotes\Create` (modal desde show)
- [ ] Mostrar en timeline del vino

---

### PASO 6 — Uva Externa `ExternalGrape` ❌
**¿Qué es?** Entrada de uva de proveedores que NO están en el sistema como viticultores.

vinai2: `ExternalGrapeController`
- Diferente a `GrapeReception` que requiere viticulturist registrado
- Campos: proveedor (nombre libre o FK a winery_suppliers), variedad, kg, precio, campaña
- Se puede vincular a `WineComposition`

**En Agro365:**
- Existe ruta `winery.external-grape.*` pero está bajo construcción
- Necesario para: bodegas que compran uva de proveedores ocasionales no registrados

**Tareas:**
- [ ] Verificar qué existe en `app/Livewire/Winery/ExternalGrape/`
- [ ] Migración `create_external_grapes` (si no existe)
- [ ] Model `ExternalGrape`
- [ ] Completar Livewire CRUD
- [ ] Vinculación a `WineComposition`

---

### PASO 7 — Documentos del Vino `WineDocument` ❌
**¿Qué es?** Repositorio de documentos asociados a un vino (certificados, analíticas, DOP...).

vinai2: `WineDocumentController` + `WineDocument`
- Campos: `wine_id`, `name`, `type` (certificate, analysis, other), `file_path`, `issued_at`

**Tareas:**
- [ ] Migración `create_wine_documents`
- [ ] Model `WineDocument`
- [ ] Livewire upload de documentos (desde `show.blade.php`)

---

### PASO 8 — Subproductos `WineSubproduct` ❌
**¿Qué es?** Orujos, lías y otros subproductos del proceso.

vinai2: `WineSubproductController` + `WineSubproduct` + `WineSubproductStock`
- Campos: `wine_id`, `subproduct_type` (orujo, lías, mosto concentrado, otro), `quantity`, `unit`, `destination` (destilería, abono, compostaje)
- Declarable en SILICIE como salida de la bodega

**Tareas:**
- [ ] Migración `create_wine_subproducts`
- [ ] Model `WineSubproduct`
- [ ] Livewire `Winery\Wines\Subproducts\Create`

---

## Mejoras al modelo Wine existente

### Flujo de creación multi-step (vinai2 tiene 6 pasos)

vinai2 guía al enólogo en la creación del vino:
1. Tipo (Mosto / Vino, modo virtual)
2. Contenedores origen
3. Contenedores destino
4. Composición (variedades/%)
5. Datos básicos (nombre, enólogo, categoría, añada)
6. Revisión

Agro365 actual: formulario simple de una sola página.

**Mejora propuesta** (Fase 2): convertir `Wines/Create` en un wizard multi-step con `wire:navigate` o Alpine stepper.

---

### Campos a añadir al modelo Wine

```php
// Migración: alter_wines_add_fields
$table->boolean('is_must')->default(false);           // mosto (sin fermentar) vs vino
$table->string('aging_type')->nullable();             // joven, barrica, reserva, gran_reserva
$table->string('category')->nullable();               // VdM, IGP, DO, DOCa, vino_de_pago
$table->decimal('initial_quantity_kg', 12, 3)->nullable(); // uva de entrada en kg
$table->boolean('is_organic')->default(false);
$table->foreignId('oenologist_id')->nullable()->constrained('oenologists');
```

---

## Diagrama de dependencias

```
WineComposition  ←── ExternalGrape
      │
      ▼
    Wine ──── WineProcessDetail ──── WineAdditive ──── WinerySupply
                    │
                    ├── WineTransfer
                    ├── WineLoss
                    ├── WineAnalysis ──── Oenologist
                    ├── WineFermentationControl
                    ├── WineTastingNote
                    └── WineBottling ──── BottledWineInventory
                                              │
                                         WineLabeling (Fase 3)
```

---

## Orden sugerido de implementación

| # | Módulo | Dependencias | Prioridad |
|---|---|---|---|
| 1 | Campos faltantes en Wine | ninguna | ~~Alta~~ ✅ |
| 2 | `Oenologist` | ninguna | Alta (bloquea todo lo demás) |
| 3 | `WineComposition` | Oenologist | Alta |
| 4 | `WineAdditive` | WinerySupply (ya existe), Oenologist | Alta |
| 5 | `ExternalGrape` | WineComposition | Media |
| 6 | `WineBottling` | Oenologist | Media |
| 7 | `WineTastingNote` | Oenologist | Media |
| 8 | `WineDocument` | ninguna | Media |
| 9 | `WineSubproduct` | ninguna | Baja |
| 10 | Wizard multi-step creación | WineComposition, Oenologist | Baja |

---

## Notas

- El modelo `Wine` ya tiene relación `wineHarvests` (pivot `wine_harvests`) — esto es la semilla de `WineComposition`
- `WinerySupply` ya existe — `WineAdditive` solo necesita la tabla de consumo y UI
- `WineTransfer` en Agro365 es `from/to container` — en vinai2 es `from/to process_detail` — el diseño actual es más simple pero suficiente para MVP
- SILICIE depende de tener `WineComposition`, `WineAdditive` y `WineSubproduct` completos — sin ellos no se puede generar el libro de bodega digital
