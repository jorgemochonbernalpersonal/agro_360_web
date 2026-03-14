# Rol Denominación de Origen (DO) — Especificación funcional

## Concepto

La **Denominación de Origen** es el cuarto rol del sistema. A diferencia de Winery, Viticulturist y Producer que gestionan su propia explotación, la DO actúa como **regulador territorial** con visibilidad y control sobre todas las bodegas, viticultores y productores adscritos a la denominación.

Es un rol de **operativa completa**: no solo supervisa, sino que crea viticultores, califica vinos, emite contraetiquetas, inspecciona y sanciona.

---

## Jerarquía de roles

```
DO (Denominación de Origen)
├── Crea y gestiona Viticultores DO
│   └── Los asigna a Bodegas adscritas
├── Supervisa Bodegas (lectura de todos sus datos)
├── Supervisa Viticultores propios de cada Bodega (lectura)
└── Opera procesos regulatorios propios
```

---

## Modelo de permisos — Viticultores DO vs Viticultores propios

La DO puede crear viticultores a nivel de denominación ("Viticultores DO"). Cuando una bodega adscrita recibe uno de estos viticultores asignados:

| Entidad | Bodega (permiso) | DO (permiso) |
|---|---|---|
| Viticultor DO (creado por la DO) | Solo lectura | CRUD completo |
| Viticultor propio (creado por la bodega) | CRUD completo | Lectura (supervisión) |

### Comportamiento de la asignación

Cuando la DO asigna un Viticultor DO a una bodega:

- La bodega **hereda automáticamente** las parcelas, plantaciones, vendimias y datos del viticultor
- La bodega **solo puede ver**, no editar ni crear datos sobre ese viticultor
- La bodega **puede usar** esos datos en sus procesos (recepciones, trazabilidad) en modo lectura
- La DO **mantiene el CRUD** completo sobre el viticultor y todos sus datos

### Campo técnico

```
viticultores
├── owner_type: 'do' | 'bodega'
├── owner_id: ID de la DO o de la bodega
├── denominacion_id: FK → denominaciones
└── ...
```

Scope de permisos: si `owner_type = 'do'`, la bodega asignada tiene permisos de solo lectura. Si `owner_type = 'bodega'`, la bodega tiene CRUD completo. La DO siempre tiene lectura sobre ambos tipos.

---

## Estructura de módulos

### Niveles de acceso

| Etiqueta | Significado |
|---|---|
| Lectura | Solo visualización y dashboards |
| Escritura | Lectura + crear, editar, eliminar |
| Acción | Aprobar, rechazar, sancionar, calificar |
| Propio DO | CRUD completo sobre entidades creadas por la DO |

---

## 1. Gestión de la denominación

### Censo (6 items)

| Item | Acceso |
|---|---|
| Bodegas registradas | Escritura |
| Viticultores registrados | Escritura |
| Productores registrados | Escritura |
| Altas / bajas / modificaciones | Acción |
| Variedades admitidas | Escritura |
| Mapa territorial DO | Lectura |

### Viticultores DO (10 items)

| Item | Acceso |
|---|---|
| Crear / editar viticultores | Propio DO |
| Parcelas | Propio DO |
| Plantaciones | Propio DO |
| SIGPAC | Propio DO |
| Gestión territorial | Propio DO |
| Teledetección | Propio DO |
| Asignación a bodegas | Propio DO |
| Cuaderno de campo | Lectura |
| Tratamientos y labores | Lectura |
| Cumplimiento cuaderno | Lectura |

> La DO crea los viticultores, sus parcelas y plantaciones. Luego los asigna a bodegas adscritas. El cuaderno de campo lo registra el viticultor o la bodega, la DO solo supervisa.

### Campañas (7 items)

| Item | Acceso |
|---|---|
| Campañas de vendimia | Escritura |
| Declaraciones de cosecha | Lectura |
| Aforos por bodega | Lectura |
| Aforos por viticultor | Lectura |
| Rendimientos por parcela | Lectura |
| Cupos y límites de producción | Escritura |
| Previsiones agregadas | Lectura |

---

## 2. Supervisión de bodegas y viticultores

### Supervisión bodegas (8 items)

| Item | Acceso |
|---|---|
| Panel de bodegas | Lectura |
| Vendimias por bodega | Lectura |
| Elaboración y vinos | Lectura |
| Trazabilidad por bodega | Lectura |
| Embotellado y expediciones | Lectura |
| Recepciones de uva | Lectura |
| Análisis de calidad | Lectura |
| Viticultores por bodega | Lectura |

### Supervisión viticultores (6 items)

| Item | Acceso |
|---|---|
| Panel de viticultores | Lectura |
| Parcelas y plantaciones | Lectura |
| Cuaderno de campo | Lectura |
| Registros y cumplimiento | Lectura |
| Recursos | Lectura |
| Cosecha comercializada | Lectura |

> La DO ve todos los datos de todas las bodegas y viticultores (propios y de bodega) dentro de su denominación.

---

## 3. Operativa regulatoria

### Calificación (7 items)

| Item | Acceso |
|---|---|
| Panel de cata | Acción |
| Análisis de laboratorio | Escritura |
| Calificación de vinos | Acción |
| Descalificación / retirada | Acción |
| Añadas | Escritura |
| Fichas técnicas DO | Escritura |
| Histórico de calificaciones | Lectura |

### Contraetiquetas (5 items)

| Item | Acceso |
|---|---|
| Solicitudes | Acción |
| Emisión y numeración | Escritura |
| Stock de contraetiquetas | Escritura |
| Trazabilidad de etiquetas | Lectura |
| Embotellado autorizado | Acción |

### Control e inspección (7 items)

| Item | Acceso |
|---|---|
| Plan de control anual | Escritura |
| Inspecciones programadas | Escritura |
| Actas de inspección | Escritura |
| Incumplimientos detectados | Acción |
| Expedientes sancionadores | Acción |
| Resoluciones | Acción |
| Alertas de incumplimiento | Lectura |

### Normativa DO (7 items)

| Item | Acceso |
|---|---|
| Pliego de condiciones | Escritura |
| Reglamento interno | Escritura |
| Autorizaciones de plantación | Acción |
| Autorizaciones de elaboración | Acción |
| Certificaciones ecológicas DO | Lectura |
| INFOVI | Escritura |
| Comunicaciones MAPAMA | Escritura |

### Territorio (5 items)

| Item | Acceso |
|---|---|
| SIGPAC consolidado | Lectura |
| Teledetección regional | Lectura |
| Zonificación DO | Escritura |
| Mapas de variedades | Lectura |
| Evolución de superficie | Lectura |

---

## 4. Administración DO

### Estadísticas (9 items)

| Item | Acceso |
|---|---|
| Dashboard general | Lectura |
| Producción por campaña | Lectura |
| Superficie y plantaciones | Lectura |
| Comercialización interior | Lectura |
| Exportación por mercado | Lectura |
| Rendimientos medios | Lectura |
| Evolución histórica | Lectura |
| Informes para CCAA | Escritura |
| Memoria anual | Escritura |

### Negocio DO (7 items)

| Item | Acceso |
|---|---|
| Cuotas de inscripción | Escritura |
| Tasas y liquidaciones | Escritura |
| Facturas DO | Escritura |
| VeriFactu | Escritura |
| Presupuesto anual | Escritura |
| Estadísticas financieras | Lectura |
| Promoción y marketing | Escritura |

### Sistema (4 items)

| Item | Acceso |
|---|---|
| Usuarios y permisos DO | Escritura |
| Documentos DO | Escritura |
| Comunicaciones masivas | Escritura |
| Configuración | Escritura |

---

## Navegación

La DO **no necesita switch Bodega/Viñedo** porque su perspectiva es siempre transversal — ve todo desde arriba.

### Sidebar / Rail

Un rail directo con los capítulos agrupados por bloques:

```
Rail DO
┌────────────────┐
│   [Logo]       │  → do.dashboard
├────────────────┤
│  [Dashboard]   │  squares-2x2
│  [Calendario]  │  calendar-days
│  [Notif.]      │  bell
├────────────────┤
│  [Censo]       │  users
│  [Vit. DO]     │  leaf (propio DO)
│  [Campañas]    │  flag
├────────────────┤
│  [Bodegas]     │  building (supervisión)
│  [Viticultores]│  eye (supervisión)
├────────────────┤
│  [Calificación]│  star
│  [Contraetiq.] │  tag
│  [Control]     │  shield-check
│  [Normativa]   │  scale
│  [Territorio]  │  map
├────────────────┤
│  [Estadísticas]│  chart-bar
│  [Negocio DO]  │  calculator
├────────────────┤
│  [Config]      │  cog
└────────────────┘
```

### Rutas

Todas bajo `/do/`:

```
/do/census
/do/growers
/do/campaigns
/do/oversight/wineries
/do/oversight/growers
/do/qualification
/do/labels
/do/inspection
/do/regulation
/do/territory
/do/statistics
/do/finance
/do/settings
```

> Excepción de rutas planas: supervisión usa `/do/oversight/wineries` y `/do/oversight/growers` porque agrupa dos vistas relacionadas bajo un concepto común.

### Responsive

| Breakpoint | Navegación |
|---|---|
| **Desktop** (≥1024px) | Rail fijo con iconos agrupados por bloques. Sin switch — todos los capítulos visibles. |
| **Tablet** (768–1023px) | Rail colapsable (hamburguesa). |
| **Móvil** (<768px) | Bottom tab bar con los 4 bloques principales (Gestión, Supervisión, Regulatoria, Admin). Cada bloque abre menú fullscreen con acordeón. |

---

## Pricing (referencia)

| Bodegas | Mensual | Anual |
|---|---|---|
| Hasta 25 | 149€ | 1.400€/año |
| 26 – 50 | 249€ | 2.350€/año |
| 51 – 75 | 349€ | 3.300€/año |
| 76 – 100 | 449€ | 4.250€/año |
| +100 | A negociar | A negociar |

Bodegas adscritas sin coste adicional para ellas. La DO paga por tramo de bodegas.

---

## Resumen numérico

| Bloque | Capítulos | Items |
|---|---|---|
| Gestión de la denominación | 3 | 23 |
| Supervisión | 2 | 14 |
| Operativa regulatoria | 5 | 31 |
| Administración | 3 | 20 |
| **Total** | **13** | **88** |

| Tipo de acceso | Items |
|---|---|
| Lectura | 34 |
| Escritura | 29 |
| Acción | 15 |
| Propio DO | 10 |
