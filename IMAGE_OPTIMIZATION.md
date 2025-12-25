# Guía de Optimización de Imágenes

Esta guía explica cómo optimizar las imágenes para mejorar el rendimiento del sitio web y cumplir con las recomendaciones de Lighthouse/PageSpeed Insights.

## Problemas Identificados

1. **`/videos/agro365_demo.webp`**: 
   - Tamaño actual: ~4227 KiB
   - Dimensiones originales: 1272x881px
   - Se muestra en: 582x403px (móvil) a 1200px (desktop)
   - **Ahorro estimado: 4189 KiB**

2. **`/images/logo.png`**:
   - Tamaño actual: ~99.6 KiB
   - Dimensiones originales: 1600x1452px
   - Se muestra en: 88x80px
   - **Ahorro estimado: 99.3 KiB**

## Solución Implementada

Se ha implementado soporte para imágenes responsivas usando `srcset` y `sizes` en:
- Componente `optimized-image.blade.php`
- Vista `welcome.blade.php`

## Pasos para Optimizar las Imágenes

### 1. Optimizar `agro365_demo.webp`

Necesitas crear versiones en diferentes tamaños:

```bash
# Instalar herramientas (si no las tienes)
# Windows: Usar ImageMagick o herramientas online
# Linux/Mac: 
# brew install imagemagick webp  # Mac
# sudo apt-get install imagemagick webp  # Linux

# Redimensionar y comprimir la imagen demo
# Versión para móvil (582px de ancho)
magick convert public/videos/agro365_demo.webp -resize 582x -quality 85 -strip public/videos/agro365_demo-582w.webp

# Versión para tablet (800px de ancho)
magick convert public/videos/agro365_demo.webp -resize 800x -quality 85 -strip public/videos/agro365_demo-800w.webp

# Versión para desktop (1200px de ancho)
magick convert public/videos/agro365_demo.webp -resize 1200x -quality 85 -strip public/videos/agro365_demo-1200w.webp

# Optimizar la imagen original también
magick convert public/videos/agro365_demo.webp -quality 85 -strip public/videos/agro365_demo_optimized.webp
# Luego reemplazar el original si es necesario
```

**Alternativa usando herramientas online:**
1. Usa [Squoosh](https://squoosh.app/) o [TinyPNG](https://tinypng.com/)
2. Sube `agro365_demo.webp`
3. Redimensiona a 582px, 800px, 1200px y 1272px
4. Ajusta la calidad a 85%
5. Descarga las versiones optimizadas

### 2. Optimizar `logo.png`

```bash
# Crear versión @2x (para pantallas retina)
magick convert public/images/logo.png -resize 320x290 -quality 90 -strip public/images/logo@2x.png

# Optimizar el logo original (reducir a tamaño real de uso)
magick convert public/images/logo.png -resize 160x145 -quality 90 -strip public/images/logo_optimized.png

# O crear versión WebP (mejor compresión)
magick convert public/images/logo.png -quality 90 -strip public/images/logo.webp
magick convert public/images/logo.png -resize 320x290 -quality 90 -strip public/images/logo@2x.webp
```

**Usando herramientas online:**
1. Sube `logo.png` a [Squoosh](https://squoosh.app/)
2. Redimensiona a 160x145px (tamaño de uso)
3. Crea versión @2x a 320x290px
4. Ajusta calidad a 90%
5. Considera convertir a WebP para mejor compresión

### 3. Verificar Resultados

Después de optimizar, verifica los tamaños:

```bash
# Windows PowerShell
Get-ChildItem public/videos/agro365_demo*.webp | Select-Object Name, @{Name="Size(KB)";Expression={[math]::Round($_.Length/1KB,2)}}

Get-ChildItem public/images/logo*.* | Select-Object Name, @{Name="Size(KB)";Expression={[math]::Round($_.Length/1KB,2)}}
```

**Objetivos:**
- `agro365_demo-582w.webp`: < 200 KiB
- `agro365_demo-800w.webp`: < 400 KiB
- `agro365_demo-1200w.webp`: < 800 KiB
- `logo.png` o `logo.webp`: < 20 KiB
- `logo@2x.png` o `logo@2x.webp`: < 40 KiB

## Configuración de Caché

Los headers de caché ya están configurados en `.htaccess`:
- Imágenes: 1 año de caché
- Cache-Control: `max-age=31536000, public, immutable`

## Uso en Código

### Imagen con srcset (ya implementado en welcome.blade.php)

```blade
<img 
    src="{{ asset('videos/agro365_demo.webp') }}" 
    srcset="{{ asset('videos/agro365_demo-582w.webp') }} 582w,
            {{ asset('videos/agro365_demo-800w.webp') }} 800w,
            {{ asset('videos/agro365_demo-1200w.webp') }} 1200w,
            {{ asset('videos/agro365_demo.webp') }} 1272w"
    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 800px, 1200px"
    alt="Demo interactiva"
    width="1200"
    height="800"
    loading="eager"
    decoding="async"
    fetchpriority="high"
>
```

### Logo con densidad de píxeles

```blade
<img 
    src="{{ asset('images/logo.png') }}" 
    srcset="{{ asset('images/logo.png') }} 1x, {{ asset('images/logo@2x.png') }} 2x"
    alt="Agro365 Logo"
    width="160"
    height="80"
    fetchpriority="high"
>
```

### Usando el componente optimized-image

```blade
<x-optimized-image
    src="{{ asset('videos/agro365_demo.webp') }}"
    :srcset="[
        'videos/agro365_demo-582w.webp' => '582w',
        'videos/agro365_demo-800w.webp' => '800w',
        'videos/agro365_demo-1200w.webp' => '1200w',
        'videos/agro365_demo.webp' => '1272w'
    ]"
    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 800px, 1200px"
    alt="Demo interactiva"
    :width="1200"
    :height="800"
    :priority="true"
/>
```

## Herramientas Recomendadas

1. **ImageMagick**: Para redimensionar y comprimir desde línea de comandos
2. **Squoosh**: Herramienta web de Google para optimización
3. **TinyPNG**: Compresión online de PNG/WebP
4. **Sharp**: Para automatización en Node.js (si usas scripts)
5. **cwebp**: Herramienta oficial de Google para convertir a WebP

## Script de Automatización (Opcional)

Puedes crear un script para automatizar la optimización:

```bash
#!/bin/bash
# optimize-images.sh

# Optimizar demo
magick convert public/videos/agro365_demo.webp -resize 582x -quality 85 -strip public/videos/agro365_demo-582w.webp
magick convert public/videos/agro365_demo.webp -resize 800x -quality 85 -strip public/videos/agro365_demo-800w.webp
magick convert public/videos/agro365_demo.webp -resize 1200x -quality 85 -strip public/videos/agro365_demo-1200w.webp

# Optimizar logo
magick convert public/images/logo.png -resize 160x145 -quality 90 -strip public/images/logo_optimized.png
magick convert public/images/logo.png -resize 320x290 -quality 90 -strip public/images/logo@2x.png

echo "Imágenes optimizadas correctamente"
```

## Verificación Post-Optimización

1. Ejecuta Lighthouse en Chrome DevTools
2. Verifica que el ahorro estimado se haya aplicado
3. Comprueba que las imágenes se cargan correctamente en diferentes dispositivos
4. Verifica que el LCP (Largest Contentful Paint) ha mejorado

## Notas Importantes

- **No elimines las imágenes originales** hasta verificar que todo funciona
- **Mantén backups** de las imágenes originales
- **Prueba en diferentes dispositivos** para asegurar que las imágenes se cargan correctamente
- **Verifica que los tamaños de archivo** sean menores después de la optimización

