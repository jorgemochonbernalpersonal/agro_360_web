# Guía de Compilación y Despliegue de Assets

Esta guía explica cómo compilar los assets (CSS/JS) y subirlos a git para desplegar en Hostinger, donde no está disponible npm.

## ¿Por qué necesitamos esto?

Hostinger no tiene npm instalado, por lo que no podemos ejecutar `npm run build` en el servidor. La solución es compilar los assets localmente y subir los archivos compilados al repositorio git.

## Pasos para Compilar y Subir el Build

### 1. Compilar los Assets

Primero, asegúrate de tener todas las dependencias instaladas y compila los assets:

```bash
cd agro365_web
npm install
npm run build
```

Esto generará los archivos compilados en `public/build/`.

### 2. Verificar que se Generaron los Archivos

Verifica que la carpeta `public/build` existe y contiene los archivos:

```bash
# Windows
dir public\build

# Linux/Mac
ls -la public/build
```

Deberías ver archivos como:

-   `manifest.json`
-   `assets/` (carpeta con archivos CSS y JS compilados)

### 3. Añadir los Archivos a Git

Añade los archivos compilados y el `package-lock.json` (si tiene cambios):

```bash
# Añadir los archivos compilados
git add public/build

# Añadir package-lock.json si tiene cambios
git add package-lock.json

# Añadir .gitignore (si lo modificaste)
git add .gitignore

# Verificar qué se va a subir
git status
```

### 4. Hacer Commit y Push

```bash
# Hacer commit
git commit -m "Build: Añadir assets compilados para producción (Hostinger)"

# Subir a git
git push
```

## Comandos Completos en una Sola Ejecución

Si prefieres ejecutar todo de una vez:

```bash
cd agro365_web
npm install
npm run build
git add public/build package-lock.json .gitignore
git commit -m "Build: Añadir assets compilados para producción"
git push
```

## Actualizar Assets Después de Cambios

Cada vez que modifiques archivos CSS o JavaScript, debes recompilar y subir:

```bash
cd agro365_web
npm run build
git add public/build
git commit -m "Build: Actualizar assets"
git push
```

## Archivos que se Suben a Git

✅ **SÍ se suben:**

-   `package.json` - Configuración de dependencias
-   `package-lock.json` - Versiones exactas de dependencias (garantiza builds reproducibles)
-   `public/build/` - Assets compilados (CSS/JS minificados)
-   `.gitignore` - Configuración actualizada

❌ **NO se suben:**

-   `node_modules/` - Demasiado grande, se instala con `npm install`
-   `public/hot` - Solo para desarrollo local con Vite HMR

## Configuración del .gitignore

El archivo `.gitignore` está configurado para:

-   ✅ **Incluir** `public/build/` (comentado para permitir subirlo)
-   ❌ **Ignorar** `public/hot` (solo desarrollo local)
-   ❌ **Ignorar** `node_modules/` (se instala con npm)

## Despliegue en Hostinger (SSH)

Una vez que hagas `git push`, en Hostinger (vía SSH) debes seguir estos pasos:

### 1. Actualizar Código desde Git

```bash
cd /ruta/a/tu/proyecto/agro365_web
git pull
```

### 2. Instalar/Actualizar Dependencias de PHP

Si has modificado `composer.json` o hay nuevas dependencias:

```bash
composer install --optimize-autoloader --no-dev
```

> **Nota:** `--no-dev` excluye dependencias de desarrollo (más rápido y seguro en producción)

### 3. Ejecutar Migraciones (Si hay nuevas tablas)

**⚠️ IMPORTANTE:** Si has creado nuevas migraciones o tablas, ejecuta:

```bash
php artisan migrate --force
```

> **Nota:** El flag `--force` es necesario en producción para evitar confirmaciones interactivas.

### 4. Limpiar Cachés

Limpia las cachés para que los cambios se reflejen:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Optimizar para Producción

Regenera las cachés optimizadas:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Verificar Permisos (Si es necesario)

Asegúrate de que las carpetas tienen los permisos correctos:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Comandos Completos en una Sola Ejecución

```bash
cd /ruta/a/tu/proyecto/agro365_web
git pull
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Checklist de Despliegue

Después de `git pull`, verifica:

-   [ ] ¿Hay cambios en `composer.json`? → Ejecuta `composer install --optimize-autoloader --no-dev`
-   [ ] ¿Hay nuevas migraciones? → Ejecuta `php artisan migrate --force`
-   [ ] ¿Cambiaste configuración en `.env`? → Ejecuta `php artisan config:clear` y `php artisan config:cache`
-   [ ] ¿Modificaste rutas? → Ejecuta `php artisan route:clear` y `php artisan route:cache`
-   [ ] ¿Cambiaste vistas? → Ejecuta `php artisan view:clear` y `php artisan view:cache`

### ¿Cuándo Ejecutar Cada Comando?

| Cambio Realizado          | Comandos Necesarios                                                  |
| ------------------------- | -------------------------------------------------------------------- |
| Solo código PHP           | `git pull`                                                           |
| Nuevas dependencias PHP   | `git pull` + `composer install --optimize-autoloader --no-dev`       |
| Nuevas migraciones/tablas | `git pull` + `php artisan migrate --force`                           |
| Cambios en `.env`         | `git pull` + `php artisan config:clear` + `php artisan config:cache` |
| Cambios en rutas          | `git pull` + `php artisan route:clear` + `php artisan route:cache`   |
| Cambios en vistas         | `git pull` + `php artisan view:clear` + `php artisan view:cache`     |
| Cambios en CSS/JS         | `git pull` (los assets ya vienen compilados)                         |

### Verificar que Todo Funciona

```bash
# Verificar que las migraciones están al día
php artisan migrate:status

# Verificar configuración
php artisan config:show app

# Ver logs si hay errores
tail -f storage/logs/laravel.log
```

## 🆘 Solución de Problemas Comunes

### Error: "Table 'sessions' already exists"

Si obtienes este error al ejecutar `php artisan migrate --force`, significa que la tabla ya existe pero Laravel no la tiene registrada en la tabla `migrations`.

**Solución:** Marca la migración como ejecutada sin ejecutarla:

```bash
php artisan tinker
```

Luego ejecuta en tinker:

```php
DB::table('migrations')->insert([
    'migration' => '2025_12_20_013117_create_sessions_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
```

O directamente desde la línea de comandos:

```bash
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2025_12_20_013117_create_sessions_table', 'batch' => DB::table('migrations')->max('batch') + 1]);"
```

Después de esto, vuelve a ejecutar:

```bash
php artisan migrate --force
```

### Error: "APP_KEY not set"

```bash
php artisan key:generate
```

### Error: "Class not found" o "Autoload error"

```bash
composer dump-autoload
composer install --optimize-autoloader --no-dev
```

### Los cambios no se reflejan

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Notas Importantes

1. **Siempre compila antes de hacer push** si has modificado CSS/JS
2. **No subas `node_modules/`** - es innecesario y muy pesado
3. **El `package-lock.json` es importante** - garantiza que las versiones sean consistentes
4. **Verifica que `public/build` existe** antes de hacer commit

## Solución de Problemas

### Error: "No se encuentra la carpeta build"

-   Ejecuta `npm run build` primero
-   Verifica que no haya errores en la compilación

### Error: "Los assets no se cargan en producción"

-   Verifica que `public/build/manifest.json` existe
-   Asegúrate de que `public/build` está en git: `git ls-files public/build`

### Los cambios no se reflejan

-   Limpia la caché del navegador
-   Verifica que el `manifest.json` tiene los nuevos hashes de archivos

## Estructura de Archivos

```
agro365_web/
├── package.json          ✅ Subido a git
├── package-lock.json     ✅ Subido a git
├── public/
│   ├── build/           ✅ Subido a git (assets compilados)
│   │   ├── manifest.json
│   │   └── assets/
│   └── hot             ❌ Ignorado (solo desarrollo)
└── node_modules/        ❌ Ignorado (se instala con npm)
```
