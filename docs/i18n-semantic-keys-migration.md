# i18n — Migración a Semantic Keys

> **Estado:** Pendiente. Iniciar tras publicación de la app móvil.
> **Fecha de decisión:** 2026-05-28
> **Motivación:** Internacionalización profesional, escalabilidad de equipo, integración con plataformas de traducción.

---

## Por qué migrar

El sistema actual usa **Spanish-as-key**: la clave ES el texto en español.

```php
__("Añadir viticultor")   // key y fallback son el mismo string
```

### Problema 1 — Fragilidad
Si un redactor mejora un texto en español, la key cambia → todas las traducciones (EN, CA, EU, GL) pierden esa entrada. La cobertura regresa sin que nadie lo note.

### Problema 2 — Género gramatical
El español tiene género (Activa/Activo/Activas/Activos). El inglés no. Resultado: 323 colisiones donde 4 keys españolas mapean a 1 sola key en inglés. Imposible mantener concordancia gramatical si se usa English-as-key sin variantes explícitas.

### Problema 3 — No escala en equipo
Agencias de traducción, herramientas como Lokalise/Crowdin, y redactores externos esperan keys semánticas estables. Con Spanish-as-key el flujo es confuso y propenso a errores.

### Problema 4 — Internacionalización futura
Si el producto crece a Portugal, Francia, Italia, Chile, Argentina — necesitas keys que no cambien con el contenido editorial.

---

## Estado actual del sistema (baseline 2026-05-28)

| Locale | Keys | Cobertura | Estado |
|--------|------|-----------|--------|
| ES (source) | 11,878 | 100% | ✅ Completo |
| EN | 11,878 | 100% | ✅ Completo |
| CA | 4,410 | 37.1% | 🟡 Parcial |
| EU | 4,606 | 38.8% | 🟡 Parcial |
| GL | 3,536 | 29.8% | 🟡 Parcial |

Archivos con llamadas de traducción: **1,222 archivos**, **21,766 llamadas** `__()` / `trans()` / `@lang()`.

---

## Convención de semantic keys (propuesta)

### Estructura
```
{módulo}.{submódulo}.{elemento}[.{variante}]
```

### Ejemplos por categoría

```
# Acciones comunes
common.actions.save
common.actions.cancel
common.actions.delete
common.actions.edit
common.actions.add
common.actions.confirm
common.actions.back

# Estados con género
common.status.active.m          → "Activo"
common.status.active.f          → "Activa"
common.status.active.m_pl       → "Activos"
common.status.active.f_pl       → "Activas"
common.status.archived.m        → "Archivado"
common.status.archived.f        → "Archivada"

# Módulos de viticultor
plots.sigpac.total_area         → "Área total"
plots.actions.add               → "Añadir parcela"
plots.fields.variety            → "Variedad"
growers.actions.add             → "Añadir viticultor"
growers.actions.assign          → "Asignar viticultor"
certifications.status.active_f  → "Activa"
notebook.treatments.add         → "Añadir tratamiento"

# Módulos de bodega
winery.containers.add           → "Añadir contenedor"
winery.containers.register_ok   → "Contenedor registrado correctamente."
winery.wine.type.red            → "Tinto"
winery.bottling.auth.created_ok → "Autorización de embotellado creada correctamente."

# Mensajes de sistema
flash.saved_ok                  → "Guardado correctamente."
flash.deleted_ok                → "Eliminado correctamente."
flash.error_generic             → "Ha ocurrido un error."

# Navegación
nav.dashboard                   → "Panel"
nav.plots                       → "Parcelas"
nav.notebook                    → "Cuaderno"
```

---

## Las 4 fases de la migración

### Fase 1 — Convención y preparación (1–2 días)
- Revisar y aprobar la convención de nomenclatura
- Crear el documento de keys canónicas para módulos comunes (`common.*`, `flash.*`, `nav.*`)
- Configurar herramienta de traducción (Lokalise recomendado, alternativa: Tolgee self-hosted)

### Fase 2 — Transformar los JSON (1 día, automatizable)
Script Node.js que:
1. Lee el mapeo `es_key → semantic_key` (definido manualmente para los 11,878 keys)
2. Genera nuevos `es.json`, `en.json`, `ca.json`, `eu.json`, `gl.json` con semantic keys
3. Preserva todas las traducciones existentes

```js
// Ejemplo de mapeo manual
const keyMap = {
  "Añadir viticultor": "growers.actions.add",
  "Área total":        "plots.sigpac.total_area",
  "Activa":            "common.status.active.f",
  // ...11,878 entradas
};
```

### Fase 3 — Actualizar el código (2–3 semanas)

**Automatizable (script regex):** ~21,000 llamadas con strings literales
```bash
# Busca __('Añadir viticultor') → reemplaza por __('growers.actions.add')
```

**Manual — 60 modelos con `__($variable)`:**
```php
// ANTES
array_map(fn ($v) => __($v), static::WINE_TYPE_LABELS)

// DESPUÉS
array_map(fn ($v) => __('winery.wine.type.' . $v), static::WINE_TYPE_LABELS)
// y en es.json: "winery.wine.type.tinto" → "Tinto"
```

**Manual — 118 concatenaciones:**
```php
// ANTES
__('Detalles del usuario ') . $this->user->name

// DESPUÉS (opción A — parámetro)
__('users.details.title', ['name' => $this->user->name])

// DESPUÉS (opción B — separar)
__('users.details.prefix') . ' ' . $this->user->name
```

**Manual — 323 colisiones de género:**
Cada colisión requiere revisar el contexto en blade y asignar la variante correcta (`active.m` vs `active.f`).

### Fase 4 — QA y tests (1–2 semanas)
- Actualizar `TranslationCoverageTest.php` con nuevos baselines
- Tests visuales: recorrer UI en ES y EN buscando strings sin traducir o con género incorrecto
- Test de regresión completo antes de merge a main

---

## Esfuerzo estimado

| Tarea | Tipo | Tiempo |
|-------|------|--------|
| Definir convención + aprobar | Manual | 1–2 días |
| Mapeo de 11,878 keys | Manual + script | 1–2 semanas |
| Transformar JSON | Script | 1 día |
| Actualizar 21k llamadas literales | Script + revisión | 2–3 días |
| Corregir 60 modelos con `__($var)` | Manual | 1–2 semanas |
| Corregir 118 concatenaciones | Manual | 1 semana |
| Resolver 323 colisiones de género | Manual | 2–3 semanas |
| QA + tests | Manual | 1–2 semanas |
| **TOTAL** | | **6–10 semanas** |

---

## Estrategia de transición recomendada

**No migrar de golpe. Migrar módulo a módulo.**

```
Paso 1: Nuevos módulos → ya en semantic keys desde el primer día
Paso 2: Módulos legacy → rama dedicada + QA por módulo
Paso 3: Merge progresivo a main, sin freeze de features
```

### Orden sugerido para legacy (de menor a mayor riesgo)
1. `common.*` — acciones, estados, flash messages (alto impacto, alta reusabilidad)
2. `nav.*` — navegación
3. Módulos de bodega (winery)
4. Módulos de viticultor (plots, notebook, certifications...)
5. Módulos de supervisor y admin

---

## Herramientas recomendadas

| Herramienta | Uso | Coste |
|-------------|-----|-------|
| **Lokalise** | Gestión de traducciones, colaboración con traductores | ~$120/mes |
| **Tolgee** | Alternativa self-hosted open source | Gratis (self-host) |
| **i18n Ally** (VS Code) | Visualización de keys en el editor | Gratis |
| Script Node.js propio | Automatizar transformación de JSON y PHP | Propio |

---

## Lo que NO cambia con esta migración

- La lógica de la aplicación
- Los tests de feature y unit
- El comportamiento de Livewire/Alpine
- La estructura de la base de datos
- El flujo de CI/CD

Solo cambian: los archivos JSON de traducciones y los strings dentro de las llamadas `__()`.

---

## Prerequisito antes de empezar

Tener la app móvil publicada y estable. La migración requiere concentración total — no es compatible con desarrollo paralelo intensivo de features.
