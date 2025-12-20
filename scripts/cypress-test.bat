@echo off
echo ========================================
echo Configurando Cypress con BD de test
echo ========================================
echo.

REM Guardar .env actual
if exist .env (
    copy .env .env.backup
    echo [OK] .env guardado como .env.backup
) else (
    echo [ERROR] No se encuentra .env
    exit /b 1
)

REM Usar .env.cypress
if exist .env.cypress (
    copy .env.cypress .env
    echo [OK] Usando .env.cypress
) else (
    echo [ERROR] No se encuentra .env.cypress
    copy .env.backup .env
    exit /b 1
)

echo.
echo ========================================
echo Reseteando y migrando BD de test
echo ========================================
php artisan migrate:fresh --force
if %errorlevel% neq 0 (
    echo [ERROR] Error en migraciones
    copy .env.backup .env
    del .env.backup
    exit /b 1
)

echo.
echo ========================================
echo Ejecutando seeders
echo ========================================
php artisan db:seed --force
if %errorlevel% neq 0 (
    echo [ERROR] Error en seeders
    copy .env.backup .env
    del .env.backup
    exit /b 1
)

echo.
echo ========================================
echo Creando usuario de prueba para Cypress
echo ========================================
php artisan db:seed --class=CypressTestUserSeeder --force
if %errorlevel% neq 0 (
    echo [WARNING] Error creando usuario de prueba (puede que ya exista)
)

echo.
echo ========================================
echo Ejecutando tests de Cypress
echo ========================================
npm run cypress:run

echo.
echo ========================================
echo Restaurando .env original
echo ========================================
copy .env.backup .env
del .env.backup
echo [OK] .env restaurado

