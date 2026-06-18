# Sitemap y SEO — Agro365

## Cómo se sirve el sitemap

El sitemap está disponible en `/sitemap.xml` mediante **dos mecanismos** que conviven:

1. **Estático** — `public/sitemap.xml`, generado por el comando `php artisan sitemap:generate`.
   El servidor web lo sirve directamente (antes de llegar a PHP) si el archivo existe.
   Está **gitignored** (no se versiona): cada entorno genera el suyo.
2. **Dinámico** — ruta `GET /sitemap.xml` → `SitemapController@index`
   (`routes/web.php:130`). Renderiza la vista `resources/views/sitemap.blade.php`
   con las URLs de `SitemapService`. **Cachea las URLs 24h** (`sitemap.urls`).

> El XML **no contiene precios**: solo URLs, prioridad, `changefreq` y `lastmod`.
> Cambiar precios en la landing **no rompe** el sitemap.

## Componentes

| Archivo | Rol |
|---------|-----|
| `app/Services/SitemapService.php` | Fuente de verdad: lista de URLs, prioridades, `changefreq` y fechas `lastmod` |
| `app/Console/Commands/GenerateSitemap.php` | Comando `sitemap:generate` → escribe `public/sitemap.xml`. Fuerza `https://` si `APP_ENV=production` |
| `app/Http/Controllers/SitemapController.php` | Sirve el sitemap dinámico, cachea URLs 24h |
| `resources/views/sitemap.blade.php` | Plantilla XML (con soporte de imágenes) |
| `public/robots.txt` | Declara `Sitemap: https://agro365.es/sitemap.xml`, bloquea áreas privadas |

## Fechas `lastmod` (importante)

Las fechas son **manuales**, en `SitemapService::LASTMOD` (no salen de `filemtime()`,
porque cada deploy reescribe los timestamps de los `.blade` y Google acaba ignorando un
`lastmod` que comparten 50 URLs).

**Regla:** actualizar a mano la fecha de una página SOLO cuando su contenido cambia de
verdad (no en cada deploy). Lo que no aparezca en `LASTMOD` hereda `FALLBACK_DATE`.

## SEO orgánico (structured data)

Los precios que ve Google (rich snippets) viven en el **JSON-LD** de las vistas, no en el
sitemap:

- `resources/views/welcome.blade.php` — schemas `SoftwareApplication`, `Organization`,
  `LocalBusiness`, `Service`, `ItemList`, `HowTo`, etc. (incluye las `Offer` con precios).
- `resources/views/content/precios.blade.php` — `SoftwareApplication` + `FAQPage`.

Al cambiar precios hay que mantener sincronizados estos JSON-LD y las `meta description`.

## Procedimiento al cambiar contenido con impacto SEO (p. ej. precios)

1. Actualizar precios en las vistas (UI + JSON-LD + `meta description`).
2. Subir la fecha `lastmod` de las páginas afectadas en `SitemapService::LASTMOD`
   (normalmente `''` = home y `precios`).
3. En **producción**, tras desplegar:
   ```bash
   php artisan sitemap:generate   # regenera public/sitemap.xml con el dominio de prod
   php artisan cache:clear        # invalida la caché de 24h de la ruta dinámica
   ```
   Si el pipeline de deploy ya ejecuta `sitemap:generate`, no hace falta manual.

> ⚠️ No commitear `public/sitemap.xml` generado en local: tendría URLs `127.0.0.1`.
> Está gitignored precisamente por esto.
