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

### Fase 2 — EN como primer idioma real (próximo sprint)

- [ ] Seeders: añadir traducción `en` a los 4 modelos de catálogo (Pest, GrapeVariety, MachineryType, ContainerType)
- [ ] Auditar `en.json`: eliminar entradas donde valor = clave en español (no aportan nada)
- [ ] Revisar secciones clave de la UI en inglés: dashboard, cuaderno de campo, SILICIE, PAC

### Fase 3 — CA como segundo idioma real

- [ ] Seeders: añadir traducción `ca` a los 4 modelos de catálogo
- [ ] Auditar `ca.json`: eliminar entradas sin traducir (valor = español)
- [ ] Revisar secciones clave en catalán

### Fase 4 — Panel de admin para traducciones de catálogos

- [ ] CRUD en el panel admin para editar traducciones `es`/`en`/`ca` de Pest, GrapeVariety, MachineryType, ContainerType
- [ ] Sin este panel, cada nuevo registro del catálogo requiere tocar código o BD directamente

### Fase 5 — EU / GL (solo si hay demanda real)

- [ ] Seeders con `eu` y `gl` para catálogos
- [ ] Auditar JSON de EU y GL
- [ ] Mantener con fallback a `es` hasta entonces

---

## Reglas para nuevas features

1. **Todo string visible al usuario usa `__()`** — sin hardcodear español en blade ni PHP.
2. **Todo modelo con datos de catálogo usa `HasTranslations`** — mínimo con `es` + `en` en el seeder.
3. **Los PDFs y exportaciones reciben explícitamente `app()->setLocale('es')`** antes de renderizar.
4. **Las claves JSON son el texto en español** — nunca incluir `__()` ni comillas escapadas en la clave.
5. **Antes de añadir una clave al JSON**, buscar si ya existe para evitar duplicados.
