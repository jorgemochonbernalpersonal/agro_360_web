@echo off
REM =============================================================================
REM Fix: Morphing Error + Datos Reales NASA
REM =============================================================================

echo.
echo ========================================
echo   Fix Urgente: Remote Sensing
echo ========================================
echo.
echo Corrigiendo 3 problemas:
echo  1. Error de morphing (wire:key faltantes)
echo  2. Datos mock en vez de reales
echo  3. Historico funcionando raro
echo.

REM ---------------------------------
REM ARCHIVOS A DESPLEGAR
REM ---------------------------------
set FILE1=resources\views\livewire\viticulturist\remote-sensing\dashboard.blade.php
set FILE2=config\services.php

echo [1/3] Verificando archivos locales...
echo.

if not exist "%FILE1%" (
    echo ERROR: No se encuentra %FILE1%
    pause
    exit /b 1
)
echo   [OK] %FILE1%

if not exist "%FILE2%" (
    echo ERROR: No se encuentra %FILE2%
    pause
    exit /b 1
)
echo   [OK] %FILE2%

echo.
echo Archivos verificados.
echo.

REM ---------------------------------
REM VERIFICAR .ENV LOCAL
REM ---------------------------------
echo [2/3] Verificando configuracion .env...
echo.

findstr /C:"NASA_EARTHDATA_MOCK=false" .env >nul
if errorlevel 1 (
    echo WARNING: .env no tiene NASA_EARTHDATA_MOCK=false
    echo Se recomienda agregarlo.
) else (
    echo   [OK] NASA_EARTHDATA_MOCK=false configurado
)

findstr /C:"OPEN_METEO_MOCK=false" .env >nul
if errorlevel 1 (
    echo WARNING: .env no tiene OPEN_METEO_MOCK=false
) else (
    echo   [OK] OPEN_METEO_MOCK=false configurado
)

findstr /C:"NASA_EARTHDATA_USERNAME" .env >nul
if errorlevel 1 (
    echo WARNING: Falta NASA_EARTHDATA_USERNAME en .env
) else (
    echo   [OK] NASA_EARTHDATA_USERNAME configurado
)

findstr /C:"NASA_EARTHDATA_PASSWORD" .env >nul
if errorlevel 1 (
    echo WARNING: Falta NASA_EARTHDATA_PASSWORD en .env
) else (
    echo   [OK] NASA_EARTHDATA_PASSWORD configurado
)

echo.

REM ---------------------------------
REM LIMPIAR CACHES LOCALES
REM ---------------------------------
echo [3/3] Limpiando caches locales...
echo.

php artisan config:clear
if errorlevel 1 (
    echo ERROR: Fallo al limpiar config cache
) else (
    echo   [OK] Config cache limpiado
)

php artisan view:clear
if errorlevel 1 (
    echo ERROR: Fallo al limpiar view cache
) else (
    echo   [OK] View cache limpiado
)

php artisan cache:clear
if errorlevel 1 (
    echo ERROR: Fallo al limpiar application cache
) else (
    echo   [OK] Application cache limpiado
)

php artisan optimize:clear
if errorlevel 1 (
    echo WARNING: optimize:clear fallo
) else (
    echo   [OK] Optimize cache limpiado
)

echo.
echo ========================================
echo   VERIFICACION LOCAL COMPLETA
echo ========================================
echo.
echo Cambios realizados:
echo.
echo 1. FIXED: Error morphing
echo    - Agregados wire:key en elementos dinamicos
echo    - Recomendaciones, tabs, historico
echo.
echo 2. FIXED: Datos mock
echo    - config/services.php ahora usa filter_var FILTER_VALIDATE_BOOLEAN
echo    - Default cambiado de true a 'false'
echo    - Respeta .env correctamente
echo.
echo 3. FIXED: Historico raro
echo    - wire:key agregado a todo el contenido
echo    - Cada periodo tiene su propio key
echo    - Evita conflictos al cambiar fechas
echo.
echo PROXIMO PASO:
echo   - Prueba localmente (php artisan serve)
echo   - Si funciona, despliega a produccion
echo   - En produccion, ejecuta tambien:
echo       php artisan config:clear
echo       php artisan view:clear
echo       php artisan cache:clear
echo.
echo VERIFICACION EN PRODUCCION:
echo   1. Abre Remote Sensing Dashboard
echo   2. Cambia de periodo en Historico varias veces
echo   3. NO deberia dar error de morphing
echo   4. Datos deben ser reales (verifica con NASA)
echo.
pause
