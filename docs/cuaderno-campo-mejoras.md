# Cuaderno de Campo — Mejoras e Incorporaciones desde Vinai
**Rol: Viticultor | Agro365**
*Última revisión: 2026-02-22*

---

## 1. Estado actual en Agro365

### Ya implementado (base sólida)

| Módulo | Modelo / Tabla | Estado |
|--------|---------------|--------|
| Parcelas + SIGPAC + geometrías + teledetección | `plots` | ✅ Completo |
| Plantaciones (variedad, densidad, espaldera, ciclo de vida, límite kg) | `plot_plantings` | ✅ Completo |
| Campañas anuales | `campaigns` | ✅ Completo |
| Actividades de campo — hub central | `agricultural_activities` | ✅ Completo |
| Tratamientos fitosanitarios | `phytosanitary_treatments` | ✅ Completo |
| Fertilizaciones | `fertilizations` | ✅ Completo |
| Riegos | `irrigations` | ✅ Completo |
| Labores culturales | `cultural_works` | ✅ Completo |
| Observaciones (plaga/enfermedad) | `observations` | ✅ Completo |
| Vendimias (Brix, Baumé, pH, trazabilidad, stock) | `harvests` | ✅ Completo |
| Estimaciones de rendimiento | `estimated_yields` | ✅ Básico — mejorar |
| Maquinaria y cuadrillas | `machinery`, `crews` | ✅ Completo |
| Contenedores/almacenamiento | `containers` | ✅ Completo |
| Variedades de uva, sistemas de conducción | catálogos | ✅ Completo |
| Teledetección remota (NDVI, NDWI, Brix predicho) | `plot_remote_sensing` | ✅ Avanzado |
| Facturación, clientes, informes oficiales | varios | ✅ Completo |

---

## 2. Mejoras a tablas/modelos existentes

### 2.1 Estimaciones de rendimiento — `estimated_yields`

**Problema actual:** Solo se guarda el número final de kg/ha estimado. No hay trazabilidad del cálculo ni permite múltiples estimaciones a lo largo de la temporada.

**Lo que añadir:**

```
Nuevos campos en estimated_yields:
─────────────────────────────────────────────────────────
thumbs_per_vine          integer    nullable   -- yemas/planta (poda)
bunches_per_plant        decimal(8,2) nullable -- racimos contados/planta (muestreo)
bunch_weight_grams       decimal(8,2) nullable -- peso medio del racimo (g)
total_plants_sampled     integer    nullable   -- nº plantas muestreadas
sampling_area_pct        decimal(5,2) nullable -- % de la plantación muestreada
health_percentage        decimal(5,2) nullable -- % racimos sanos
potential_alcohol        decimal(5,2) nullable -- % alcohol probable
auto_calculated_yield    decimal(10,2) nullable -- resultado del cálculo automático
vintage                  year       nullable   -- añada
virtual_surface          decimal(10,4) nullable -- superficie virtual (para DO)
protection_level         string     nullable   -- nivel de protección (DO)
classification_id        FK         nullable   -- clasificación DO/IGP
estimation_round         integer    default 1  -- ronda: 1=pre-envero, 2=envero, 3=pre-vendimia
```

**Fórmula automática a implementar:**
```
auto_calculated_yield = (bunches_per_plant × bunch_weight_grams / 1000)
                        × (vine_count / area_planted) × area_planted
                     = bunches_per_plant × bunch_weight_grams × vine_count / 1000
```

**Cambio en constraints:**
- Eliminar UNIQUE(plot_planting_id, campaign_id)
- Nuevo UNIQUE(plot_planting_id, campaign_id, estimation_round)
- Esto permite: pre-envero → envero → pre-vendimia → revisión final

---

### 2.2 Tratamientos fitosanitarios — `phytosanitary_treatments`

**Problema actual:** No registra los métodos alternativos previos (obligatorio para IPM / Producción Integrada y DO ecológica).

**Lo que añadir:**

```
Nuevos campos en phytosanitary_treatments:
─────────────────────────────────────────────────────────
water_volume_liters      decimal(8,2) nullable -- volumen de caldo (L/ha)
under_advisory           boolean    default false -- bajo asesoramiento técnico
advisory_action_date     date       nullable   -- fecha de recomendación
-- Gestión integrada de plagas (IPM) — obligatorio para certificaciones:
prior_non_chemical_methods boolean default false -- métodos no químicos previos
plague_monitoring        boolean    default false -- seguimiento de la plaga
manual_mechanical_control boolean   default false -- control manual/mecánico
biological_control       boolean    default false -- control biológico
cultural_preventions     boolean    default false -- prevenciones culturales
```

---

### 2.3 Actividades de campo — `agricultural_activities` + subtipos

**Problema actual:** Falta tipo `post_harvest` y campos específicos de vendimia en `harvests` (estado sanitario detallado, ticket de albarán).

**Lo que añadir:**

```
Nuevo tipo de actividad en enum activity_type:
  + 'post_harvest'        -- tratamientos post-vendimia de viña
  + 'pruning'             -- poda (actualmente en 'cultural')
  + 'phenology'           -- observación fenológica formal

Nuevos campos en harvests:
─────────────────────────────────────────────────────────
harvest_ticket_number    string     nullable   -- nº albarán/ticket vendimia
sanitary_state_grapes    decimal(5,2) nullable -- % estado sanitario uva
sanitary_state_agraces   decimal(5,2) nullable -- % agraces
sanitary_state_botrytis  decimal(5,2) nullable -- % botritis
sanitary_state_oidium    decimal(5,2) nullable -- % oídio
sanitary_state_mildew    decimal(5,2) nullable -- % mildiu

Nuevos campos en cultural_works (poda):
─────────────────────────────────────────────────────────
pruning_type             string     nullable   -- tipo poda: guyot, doble_guyot, vaso, etc.
productive_buds_per_hectare integer nullable   -- yemas productivas/ha resultantes
```

---

### 2.4 Parcelas — `plots`

**Lo que añadir:**

```
Nuevos campos en plots:
─────────────────────────────────────────────────────────
maximum_yield_kg_ha      decimal(10,2) nullable -- rendimiento máximo histórico registrado
degree_day_base          decimal(4,1) default 10 -- temperatura base grados-día (°C)
code_parcel              string     nullable   -- código de parcela catastral
site_name                string     nullable   -- paraje
valley                   string     nullable   -- valle/zona
enclosure_code           string     nullable   -- código recinto SIGPAC adicional
```

---

### 2.5 Perfil/Configuración viticultor

**Lo que añadir en `viticulturist_settings` o tabla de perfil:**

```
─────────────────────────────────────────────────────────
default_limit_kg_per_ha  decimal(8,2) nullable -- límite kg/ha por defecto para nuevas plantaciones
degree_day_base          decimal(4,1) default 10 -- base global para cálculo GD
signature_path           string     nullable   -- firma digital para documentos PDF
signature_protected      boolean    default false
document_prefix_activity string     default 'ACT' -- prefijo numeración actividades
document_prefix_harvest  string     default 'VND' -- prefijo numeración vendimias
legal_text_fieldbook     text       nullable   -- pie de página legal en PDF cuaderno
notify_harvest_alerts    boolean    default true  -- alertas de vendimia por email
notify_activity_alerts   boolean    default true  -- alertas de actividades por email
```

---

## 3. Nuevos módulos a añadir

### 3.1 Fenología — `phenology_observations`

**Por qué:** Vinai tiene un módulo completo de observaciones fenológicas formales, independiente de las observaciones genéricas del cuaderno. La fenología es clave para:
- Calcular grados-día acumulados
- Correlacionar tratamientos fitosanitarios con estadio fenológico
- Prever fecha de vendimia
- Cumplimiento de algunas DOs (registro de estados fenológicos obligatorio)

**Tabla nueva: `phenology_observations`**
```sql
id                       bigint PK
plot_planting_id         FK → plot_plantings
campaign_id              FK → campaigns
viticulturist_id         FK → users
event                    enum: budbreak, shoot_growth, flowering, fruit_set,
                               veraison, pre_harvest, harvest
obs_date                 date
source                   enum: manual, sensor, model -- origen del dato
confidence               integer (0-100) nullable    -- nivel de confianza
degree_days_accumulated  decimal(8,2) nullable       -- GD acumulados a esa fecha
notes                    text nullable
active                   boolean default true
timestamps
```

**Vistas/Livewire:**
- Listado por plantación con línea de tiempo fenológica
- Comparativa entre campañas (¿este año va adelantado?)
- Widget en dashboard de parcela

---

### 3.2 Análisis de residuos — `residue_analyses`

**Por qué:** Obligatorio para exportación, certificaciones ecológicas y cumplimiento RASFF. Vinai lo tiene como módulo completo vinculado a la campaña.

**Tabla nueva: `residue_analyses`**
```sql
id                       bigint PK
campaign_id              FK → campaigns
plot_planting_id         FK → plot_plantings  nullable
agricultural_activity_id FK → agricultural_activities  nullable (tratamiento previo)
viticulturist_id         FK → users
analysis_date            date
sample_date              date
laboratory_name          string
laboratory_accreditation string nullable        -- nº acreditación lab
products_analyzed        json                   -- array de productos analizados
results                  json                   -- array: {product, result_ppb, mrl_ppb, compliant}
overall_compliant        boolean
certificate_file         string nullable        -- ruta del certificado PDF
notes                    text nullable
active                   boolean default true
timestamps
```

**Lógica:**
- `overall_compliant = true` si todos los resultados están por debajo del MRL
- Alertas si algún resultado supera el MRL

---

### 3.3 Gestión de residuos agrícolas — `residue_managements`

**Por qué:** Gestión de restos de poda, raspones, hollejos, etc. Obligatorio en cuadernos de explotación oficiales y para certificaciones de sostenibilidad.

**Tabla nueva: `residue_managements`**
```sql
id                       bigint PK
campaign_id              FK → campaigns
plot_id                  FK → plots  nullable
plot_planting_id         FK → plot_plantings  nullable
viticulturist_id         FK → users
date                     date
practice_type            enum: incorporation,  -- triturado e incorporación al suelo
                               removal,        -- retirada de la explotación
                               burning,        -- quema (cuando permitido)
                               composting,     -- compostaje
                               biogas,         -- biogás
                               sale,           -- venta
                               other
material_type            enum: pruning_wood,   -- leña/madera de poda
                               grape_marc,     -- orujo
                               vine_leaves,    -- hojas
                               grass,          -- cubierta vegetal
                               other
estimated_quantity       decimal(10,2) nullable -- kg o toneladas
quantity_unit            string nullable
justification            text nullable          -- obligatorio para quema
notes                    text nullable
active                   boolean default true
timestamps
```

---

### 3.4 Consumo energético — `energy_usages`

**Por qué:** Huella de carbono, certificaciones de sostenibilidad y cuadernos de explotación oficiales en España (CUE). También útil para calcular coste real de las operaciones.

**Tabla nueva: `energy_usages`**
```sql
id                       bigint PK
campaign_id              FK → campaigns
agricultural_activity_id FK → agricultural_activities  nullable
machinery_id             FK → machinery  nullable
viticulturist_id         FK → users
date                     date
energy_type              enum: diesel,        -- gasóleo agrícola
                               gasoline,      -- gasolina
                               electricity,   -- electricidad
                               lpg,           -- GLP
                               natural_gas,   -- gas natural
                               water_pump,    -- bombeo agua
                               other
quantity                 decimal(10,3)
unit                     enum: liters, kwh, m3, kg
cost_per_unit            decimal(10,4) nullable
total_cost               decimal(10,2) nullable
usage_description        string nullable      -- descripción de la operación
co2_kg_equivalent        decimal(10,3) nullable -- calculado automáticamente
notes                    text nullable
active                   boolean default true
timestamps
```

**Lógica automática:** calcular `co2_kg_equivalent` por tipo de energía usando factores de emisión estándar.

---

### 3.5 Tratamientos post-vendimia — nuevo tipo de actividad

**Por qué:** Los tratamientos de otoño en la viña (cobre, azufre, sellado de heridas de poda) son parte del cuaderno de campo oficial pero actualmente no hay tipo específico.

**Implementación:** Añadir `post_harvest` al enum de `activity_type` en `agricultural_activities` con un nuevo subtipo:

**Tabla nueva: `post_harvest_treatments`** (como subtipo de `agricultural_activities`)
```sql
id                       bigint PK
activity_id              FK → agricultural_activities
product_id               FK → phytosanitary_products  nullable
application_type         enum: copper_treatment,      -- tratamiento de cobre
                               sulfur_treatment,      -- tratamiento de azufre
                               wound_sealing,         -- sellado de heridas poda
                               foliar_application,    -- aplicación foliar otoño
                               other
treated_area_ha          decimal(10,4)
dose_per_hectare         decimal(10,3)
dose_unit                string
water_volume_liters      decimal(8,2) nullable
notes                    text nullable
timestamps
```

---

### 3.6 Cosecha comercializada — `marketed_harvests`

**Por qué:** Vinai tiene un módulo para registrar dónde va la uva cosechada (cooperativa, bodega propia, terceros) con cantidad, precio y documentación. En Agro365 actualmente hay `destination` y `buyer_name` en la vendimia, pero si hay **múltiples entregas de una misma vendimia en días distintos** (muy habitual), no hay soporte.

**Tabla nueva: `marketed_harvests`**
```sql
id                       bigint PK
harvest_id               FK → harvests
campaign_id              FK → campaigns
viticulturist_id         FK → users
delivery_date            date
quantity_kg              decimal(10,2)
destination_type         enum: own_winery,       -- bodega propia
                               cooperative,      -- cooperativa
                               third_party,      -- terceros
                               other
buyer_name               string nullable
buyer_rega_code          string nullable          -- código REGA/bodega compradora
transport_document       string nullable          -- nº albarán transporte
vehicle_plate            string nullable          -- matrícula vehículo
price_per_kg             decimal(8,4) nullable
total_value              decimal(12,2) nullable   -- calculado automático
notes                    text nullable
active                   boolean default true
timestamps
```

---

### 3.7 Insumos/Almacén — `supplies` + `supply_purchases`

**Por qué:** Vinai tiene un sistema completo de almacén de insumos (fitosanitarios, fertilizantes, semillas) con stock en tiempo real y compras. Actualmente Agro365 tiene `phytosanitary_products` como catálogo pero **no gestión de stock real** de lo que tiene el viticultor en su almacén.

**Tabla nueva: `supplies`** (almacén del viticultor)
```sql
id                       bigint PK
viticulturist_id         FK → users
name                     string
commercial_name          string nullable
registration_number      string nullable           -- nº registro producto
supply_type              enum: phytosanitary, fertilizer,
                               seed, postharvest, other
phytosanitary_product_id FK → phytosanitary_products  nullable  -- link al catálogo
unit_of_measurement_id   FK → units
expiry_date              date nullable
initial_stock            decimal(10,3) default 0
current_stock            decimal(10,3) default 0
min_stock_alert          decimal(10,3) nullable    -- alerta stock mínimo
-- Composición nutricional (para fertilizantes):
nutrient_n               decimal(5,2) nullable     -- % Nitrógeno
nutrient_p2o5            decimal(5,2) nullable     -- % Fósforo
nutrient_k2o             decimal(5,2) nullable     -- % Potasio
nutrient_cao             decimal(5,2) nullable     -- % Calcio
nutrient_mgo             decimal(5,2) nullable     -- % Magnesio
nutrient_so3             decimal(5,2) nullable     -- % Azufre
organic_matter           decimal(5,2) nullable
active                   boolean default true
timestamps
```

**Tabla nueva: `supply_purchases`**
```sql
id                       bigint PK
supply_id                FK → supplies
viticulturist_id         FK → users
campaign_id              FK → campaigns  nullable
invoice_date             date
invoice_number           string nullable
quantity                 decimal(10,3)
unit_of_measurement_id   FK → units
total_cost               decimal(10,2) nullable
supplier_name            string nullable
notes                    text nullable
timestamps
```

**Lógica:** Cada vez que se registra un tratamiento, **descuenta del stock** de la supply correspondiente.

---

### 3.8 Aplicadores y equipos formales — `field_applicators` + `field_equipment`

**Por qué:** Vinai registra aplicadores con licencia ROPO y equipos de aplicación por campaña. Esto es **obligatorio en el cuaderno oficial** para productos fitosanitarios. Actualmente Agro365 tiene `crews`/`crew_members` pero no el registro formal de aplicadores con nº de licencia.

**Tabla nueva: `field_applicators`**
```sql
id                       bigint PK
viticulturist_id         FK → users
campaign_id              FK → campaigns  nullable   -- null = aplicador permanente
name                     string
ropo_number              string                     -- nº ROPO obligatorio
ropo_category            enum: basic, qualified, fumigator, pilot
ropo_expiry_date         date nullable
is_advisor               boolean default false      -- también actúa como asesor
advisor_license          string nullable
active                   boolean default true
timestamps
```

**Tabla nueva: `field_equipment`**
```sql
id                       bigint PK
viticulturist_id         FK → users
name                     string
equipment_type           enum: sprayer, spreader, irrigation,
                               tractor, harvester, pruner, other
registration_number      string nullable
purchase_date            date nullable
last_inspection_date     date nullable
next_inspection_date     date nullable
inspection_entity        string nullable
active                   boolean default true
notes                    text nullable
timestamps
```

---

### 3.9 Explotación SIEX/CUE — `exploitations`

**Por qué:** En España, el **Cuaderno de Explotación Único (CUE)** digital es de envío obligatorio al MAPA desde 2022. Vinai tiene el módulo completo para gestionar la explotación y hacer el envío. Sin esto, el cuaderno no puede considerarse oficial.

**Tabla nueva: `exploitations`**
```sql
id                       bigint PK
viticulturist_id         FK → users
rea_code                 string nullable           -- código REA (Registro Explotaciones Agrarias)
siex_exploitation_id     string nullable           -- ID SIEX
exploitation_name        string
exploitation_type        string nullable
holder_name              string
holder_nif               string
representative_name      string nullable
representative_nif       string nullable
is_ecological            boolean default false
is_integrated_production boolean default false
is_quality_scheme        boolean default false
quality_scheme_desc      string nullable
notes                    text nullable
active                   boolean default true
timestamps
```

**Tabla nueva: `exploitation_dgcs`** (Declaración de Gestión de Cultivos por parcela)
```sql
id                       bigint PK
exploitation_id          FK → exploitations
plot_id                  FK → plots
plot_planting_id         FK → plot_plantings  nullable
dgc_code                 string nullable
dgc_area_ha              decimal(10,4)
system_of_exploitation   string nullable        -- secano/regadío
system_of_cultivation    string nullable        -- convencional/ecológico/integrado
irrigation_system_type   string nullable
planting_year            integer nullable
geometry                 geometry/json nullable  -- coordenadas GeoJSON
active                   boolean default true
timestamps
```

**Tabla nueva: `cue_exports`** (Historial de envíos CUE)
```sql
id                       bigint PK
exploitation_id          FK → exploitations
campaign_year            integer
period_type              enum: quarterly, annual
from_date                date
to_date                  date
status                   enum: draft, generated, sent, accepted, rejected
payload_json             json nullable
response_json            json nullable
generated_at             datetime nullable
sent_at                  datetime nullable
accepted_at              datetime nullable
error_message            text nullable
file_path                string nullable
timestamps
```

---

### 3.10 Asesorías técnicas — `advisory_memberships`

**Por qué:** Obligatorio para Producción Integrada y algunas DOs. El asesor técnico debe estar registrado en el cuaderno.

**Tabla nueva: `advisory_memberships`**
```sql
id                       bigint PK
viticulturist_id         FK → users
campaign_id              FK → campaigns  nullable
advisor_name             string
license_number           string                  -- nº colegiado/licencia
specialty                enum: phytosanitary, agronomy, oenology, other
company_name             string nullable
phone                    string nullable
email                    string nullable
active                   boolean default true
timestamps
```

---

### 3.11 Autorizaciones comerciales — `commercial_authorizations`

**Por qué:** DO, certificaciones orgánicas, autorizaciones de plantación, derechos de replantación — todo esto son autorizaciones con fecha de vigencia.

**Tabla nueva: `commercial_authorizations`**
```sql
id                       bigint PK
viticulturist_id         FK → users
exploitation_id          FK → exploitations  nullable
authorization_type       enum: do_registration,          -- inscripción DO
                               organic_certification,    -- certificación ecológica
                               planting_right,           -- derecho de plantación
                               replanting_right,         -- derecho de replantación
                               integrated_production,    -- producción integrada
                               other
authorization_code       string nullable
description              string nullable
issuing_body             string nullable               -- organismo emisor
issue_date               date
expiry_date              date nullable
document_file            string nullable
active                   boolean default true
notes                    text nullable
timestamps
```

---

### 3.12 Entorno de parcela — `plot_environments`

**Por qué:** Registro de condiciones ambientales específicas de la parcela por campaña (zonas protegidas, distancias a captaciones de agua, zonas de exclusión). Obligatorio en muchos programas de producción integrada.

**Tabla nueva: `plot_environments`**
```sql
id                       bigint PK
campaign_id              FK → campaigns
plot_id                  FK → plots
plot_planting_id         FK → plot_plantings  nullable
viticulturist_id         FK → users
water_intake_nearby      boolean default false     -- hay captación de agua cercana
water_intake_distance_m  decimal(8,2) nullable     -- distancia en metros
protected_zone_total     boolean default false     -- zona protegida total
protected_zone_partial   boolean default false     -- zona protegida parcial
protection_zone_type     string nullable           -- tipo de zona (N2000, LIC, etc.)
buffer_zone_m            decimal(8,2) nullable     -- zona tampón requerida (m)
notes                    text nullable
timestamps
```

---

### 3.13 Documentos de campaña — `campaign_documents`

**Por qué:** Vinai permite adjuntar documentos a la campaña (facturas de insumos, informes de laboratorio, certificados). Actualmente hay archivos adjuntos en actividades individuales pero no en la campaña como contenedor.

**Tabla nueva: `campaign_documents`**
```sql
id                       bigint PK
campaign_id              FK → campaigns
viticulturist_id         FK → users
name                     string
document_type            enum: invoice, certificate, lab_report,
                               authorization, map, other
file_path                string
file_size                integer nullable
notes                    text nullable
timestamps
```

---

### 3.14 Firma de campaña (validación oficial) — en `campaigns`

**Por qué:** Vinai tiene firma de validación a mitad y final de campaña. Esto es para que el cuaderno pueda ser presentado como documento oficial con valor legal.

**Campos a añadir en `campaigns`:**
```
mid_validation_signed    boolean    default false  -- firma intermedia
mid_validation_date      datetime   nullable
mid_validation_user_id   FK → users nullable
final_validation_signed  boolean    default false  -- firma final de campaña
final_validation_date    datetime   nullable
final_validation_user_id FK → users nullable
pdf_path                 string     nullable        -- PDF generado del cuaderno completo
locked_at                datetime   nullable        -- cuando se cierra la campaña
```

---

## 4. Priorización

### PRIORIDAD 1 — MVP Cuaderno Oficial
*Sin esto el cuaderno no puede ser presentado como oficial*

| # | Mejora | Esfuerzo | Impacto |
|---|--------|----------|---------|
| 1 | Estimaciones: campos de muestreo (racimos/planta, peso, yemas) | Bajo | Alto |
| 2 | Estimaciones: múltiples por campaña (eliminar unique, añadir ronda) | Bajo | Alto |
| 3 | Fitosanitarios: IPM flags (control biológico, seguimiento plaga, etc.) | Bajo | Alto |
| 4 | Aplicadores formales (nº ROPO) + Equipos de campo | Medio | Alto |
| 5 | Fenología (budbreak, floración, envero, vendimia) | Medio | Alto |
| 6 | Explotación SIEX/REA + DGCs | Alto | Alto |
| 7 | Firma de campaña (validación intermedia + final + PDF) | Alto | Alto |

### PRIORIDAD 2 — Cuaderno Completo
*Mejora la calidad del dato y la usabilidad*

| # | Mejora | Esfuerzo | Impacto |
|---|--------|----------|---------|
| 8 | Cosecha comercializada (entregas múltiples) | Medio | Alto |
| 9 | Análisis de residuos (lab + resultados vs MRL) | Medio | Alto |
| 10 | Tratamientos post-vendimia (tipo de actividad) | Bajo | Medio |
| 11 | Estado sanitario detallado en vendimia (botritis, oídio...) | Bajo | Medio |
| 12 | Gestión de residuos agrícolas (restos poda, orujos) | Medio | Medio |
| 13 | Asesorías técnicas por campaña | Bajo | Medio |
| 14 | Documentos de campaña | Bajo | Medio |
| 15 | Firma en campaigns (lock + PDF) | Medio | Medio |

### PRIORIDAD 3 — Avanzado
*Para cumplimiento avanzado y diferenciación competitiva*

| # | Mejora | Esfuerzo | Impacto |
|---|--------|----------|---------|
| 16 | Insumos/almacén con stock real | Alto | Medio |
| 17 | Compras de insumos | Medio | Medio |
| 18 | Consumo energético (huella carbono) | Medio | Bajo |
| 19 | Autorizaciones comerciales (DO, eco, plantación) | Medio | Medio |
| 20 | Entorno de parcela (zonas protegidas, captaciones) | Bajo | Bajo |
| 21 | Mejoras menores en plots y perfil | Bajo | Bajo |
| 22 | CUE exports (envío digital MAPA) | Muy Alto | Alto |

---

## 5. Lo que queda para el rol Bodega (futuro)

Los siguientes módulos de Vinai se reservan para cuando se implemente el rol Bodega:

- Procesos de vinificación (fermentación, crianza, embotellado)
- Análisis de mosto ampliado (SO₂, acidez tartárica/málica, acidez volátil)
- Tratamientos de almacenamiento (sulfuroso, conservantes)
- Tratamientos de transporte (temperatura, aditivos)
- Contenedores de bodega (barricas con historial, depósitos)
- Notas de cata y puntuación
- Clasificaciones DO/DOC y submarcas geográficas
- Supervisión/auditorías de bodega

---

## 6. Relaciones clave del cuaderno completo

```
Campaign (año)
  ├─ PlotPlanting (plantación)
  │   ├─ PhenologyObservation     → fenología por estadio
  │   ├─ EstimatedYield (×N)      → estimaciones pre-vendimia (multi-ronda)
  │   ├─ AgriculturalActivity
  │   │   ├─ PhytosanitaryTreatment  → fitosanitarios (+ IPM flags)
  │   │   ├─ Fertilization           → abonado
  │   │   ├─ Irrigation              → riego
  │   │   ├─ CulturalWork            → labores (poda → yemas/ha)
  │   │   ├─ Observation             → plagas/enfermedades
  │   │   ├─ PostHarvestTreatment    → tratamientos otoño/post-vendimia [NUEVO]
  │   │   └─ EnergyUsage             → consumo asociado [NUEVO]
  │   ├─ Harvest
  │   │   ├─ estado sanitario detallado  [NUEVO campos]
  │   │   └─ MarketedHarvest (×N)    → entregas/albaranes [NUEVO]
  │   ├─ ResidueAnalysis             → análisis lab residuos [NUEVO]
  │   └─ ResidueManagement           → gestión restos poda/marc [NUEVO]
  │
  ├─ FieldApplicator (×N)            → aplicadores ROPO [NUEVO]
  ├─ FieldEquipment (×N)             → equipos de aplicación [NUEVO]
  ├─ AdvisoryMembership (×N)         → asesores técnicos [NUEVO]
  ├─ CampaignDocument (×N)           → documentos adjuntos [NUEVO]
  ├─ PlotEnvironment (×N)            → entorno parcela [NUEVO]
  └─ CueExport                       → envío digital CUE/SIEX [NUEVO]

Exploitation (explotación SIEX)      [NUEVO]
  ├─ ExploitationDgc (×N)            → declaración por parcela
  └─ CommercialAuthorization (×N)    → DO, eco, derechos plantación

Supply (almacén viticultor)          [NUEVO]
  └─ SupplyPurchase (×N)             → compras con factura
```

---

*Documento de referencia — proyecto Agro365 — rama migracion*
