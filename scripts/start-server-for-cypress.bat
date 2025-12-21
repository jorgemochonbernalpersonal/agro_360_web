@echo off
echo ========================================
echo Iniciando servidor Laravel para Cypress
echo ========================================
echo.

REM Verificar que .env.cypress existe
if not exist .env.cypress (
    echo [ERROR] No se encuentra .env.cypress
    echo Crea el archivo .env.cypress con la configuracion de BD de test
    exit /b 1
)

REM Guardar .env actual si existe
if exist .env (
    copy .env .env.backup >nul 2>&1
    echo [OK] .env guardado como .env.backup
)

REM Usar .env.cypress
copy .env.cypress .env >nul 2>&1
echo [OK] Usando .env.cypress para el servidor
echo.

echo ========================================
echo Iniciando servidor en http://127.0.0.1:8000
echo ========================================
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

php artisan serve --host=127.0.0.1 --port=8000

REM Restaurar .env original al salir
if exist .env.backup (
    copy .env.backup .env >nul 2>&1
    del .env.backup >nul 2>&1
    echo.
    echo [OK] .env restaurado
)

