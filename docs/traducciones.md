# Estrategia de Traducciones — Agro365

## Principios

- **ES + EN** son ciudadanos de primera clase. Deben funcionar perfectamente en toda la app.
- **CA** es tercera prioridad real (Cataluña = mayor región vinícola exportadora de España).
- **EU / GL** se mantienen con fallback a español. Sin inversión adicional hasta que haya demanda real.
- **Documentos oficiales** (PAC, SILICIE, registros, PDFs, exportaciones) siempre en **español**, independientemente del locale de la UI. Es un requisito legal.
- Ofrecer 5 idiomas a medias es peor que ofrecer 2 bien hechos.

---

## Estado actual

| Área | ES | EN | CA | EU | GL |
|------|----|----|----|----|-----|
| JSON UI (`resources/lang/`) | ✅ completo | ⚠️ ~24 claves sin traducir | ⚠️ ~212 claves sin traducir | ⚠️ ~212 claves sin traducir | ⚠️ ~213 claves sin traducir |
| Validaciones (`lang/*/validation.php`) | ✅ | ✅ creado | ✅ creado | ✅ creado | ✅ creado |
| Catálogos BD (Spatie) | ✅ seeders | ❌ vacío | ❌ vacío | ❌ vacío | ❌ vacío |
| Fallback Spatie cuando falta traducción | — | ❌ devuelve vacío | ❌ devuelve vacío | ❌ devuelve vacío | ❌ devuelve vacío |
| Documentos/PDFs anclados a ES | ❌ pendiente | — | — | — | — |
| Panel admin traducciones catálogos | ❌ no existe | — | — | — | — |

### Modelos con `spatie/laravel-translatable`
- `Pest` — name, description, symptoms, lifecycle, prevention_methods
- `GrapeVariety` — name, description
- `MachineryType` — name
- `ContainerType` — name, description

Todos los seeders solo tienen `es`. Al cambiar de idioma, los campos quedan en blanco.

---

## Roadmap

### Fase 1 — Estabilizar ✅

- [x] Añadir fallback a `es` en los 4 modelos Spatie para que nunca aparezca un campo vacío
- [x] Anclar generación de documentos/PDFs/exportaciones al locale `es` independientemente del locale de sesión

### Fase 2 — EN como primer idioma real ✅

- [x] Seeders: añadir traducción `en` a los 4 modelos de catálogo (Pest, GrapeVariety, MachineryType, ContainerType)
- [x] Auditar `en.json`: eliminadas 7.062 claves sin traducir (valor = clave en español) — quedan 4.840 traducciones reales
- [x] Revisar secciones clave de la UI en inglés (dashboard, cuaderno de campo, SILICIE, PAC)
  - Dashboard, cuaderno de campo y PAC: ✅ sin gaps reales. Acrónimos (NDVI, PAC, SILICIE) son internacionales, no necesitan traducción.
  - SILICIE e INFOVI: **decisión explícita de no traducir** — son sistemas regulatorios exclusivos de España (Orden HAC/1505/2024, AICA). Un usuario anglófono en España los usa igualmente en contexto español. El trabajo no justifica el beneficio.: dashboard, cuaderno de campo, SILICIE, PAC

### Fase 3 — CA como segundo idioma real ✅

- [x] Seeders: añadir traducción `ca` a los 4 modelos de catálogo (Garnatxa Negra, Carinyena, Macabeu, Verematadora, Desbrossadora, Bóta, Àmfora...)
- [x] Auditar `ca.json`: eliminadas 7.304 claves sin traducir — quedan 4.386 traducciones reales
- [x] Revisión UI catalán: mismo resultado que EN — secciones principales correctas, SILICIE/INFOVI decisión ya tomada

### Fase 4 — Panel de admin para traducciones de catálogos ✅

- [x] Ruta `admin.catalogs.index` → `/admin/catalogs`
- [x] Livewire `Admin\Catalogs\Index` con tabs por modelo, tabla ES/EN/CA y modal de edición
- [x] Entrada en el menú admin con icono `language`
- [x] Soporta todos los campos de Pest (5 campos), GrapeVariety (2), MachineryType (1), ContainerType (2)

### Fase 5 — EU / GL ✅

- [x] Seeders con `eu` y `gl` para los 4 catálogos (Mahats-artziboa, Armiarma gorria, Traktore, Upela, Barrica...)
- [x] eu.json: eliminadas 7.106 claves sin traducir — quedan 4.584 traducciones reales
- [x] gl.json: eliminadas 8.180 claves sin traducir — quedan 3.509 traducciones reales
- [x] Panel admin actualizado para mostrar los 5 locales (es/en/ca/eu/gl)

---

## Reglas para nuevas features

1. **Todo string visible al usuario usa `__()`** — sin hardcodear español en blade ni PHP.
2. **Todo modelo con datos de catálogo usa `HasTranslations`** — mínimo con `es` + `en` en el seeder.
3. **Los PDFs y exportaciones reciben explícitamente `app()->setLocale('es')`** antes de renderizar.
4. **Las claves JSON son el texto en español** — nunca incluir `__()` ni comillas escapadas en la clave.
5. **Antes de añadir una clave al JSON**, buscar si ya existe para evitar duplicados.
