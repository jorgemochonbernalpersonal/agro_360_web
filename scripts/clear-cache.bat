@echo off
echo ========================================
echo   LIMPIANDO CACHES DE LARAVEL/LIVEWIRE
echo ========================================
echo.

echo [1/6] Limpiando optimizaciones...
php artisan optimize:clear

echo [2/6] Limpiando vistas compiladas...
php artisan view:clear

echo [3/6] Limpiando cache de aplicacion...
php artisan cache:clear

echo [4/6] Limpiando cache de configuracion...
php artisan config:clear

echo [5/6] Limpiando cache de rutas...
php artisan route:clear

echo [6/6] Limpiando cache de eventos...
php artisan event:clear

echo.
echo ========================================
echo   ¡TODAS LAS CACHES LIMPIADAS!
echo ========================================
echo.
echo Ahora:
echo   1. Refresca tu navegador con Ctrl+F5
echo   2. O cierra y abre la pestaña
echo.
pause

