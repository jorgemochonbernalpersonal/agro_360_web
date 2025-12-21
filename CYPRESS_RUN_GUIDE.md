# Guía para Ejecutar Tests de Cypress

## ⚠️ IMPORTANTE: Configuración del Servidor

**El servidor Laravel DEBE usar la misma BD de test que Cypress.**

### Problema Común

Si el servidor Laravel está corriendo con `.env` (BD de desarrollo), los tests fallarán porque:
- Cypress crea usuarios en `agro365_test`
- El servidor busca usuarios en `agro365` (desarrollo)
- Resultado: Login falla

### Solución: Usar Script de Inicio

**Opción 1: Script Automático (Recomendado)**

```bash
# Inicia el servidor con BD de test
scripts\start-server-for-cypress.bat

# En otra terminal, ejecuta Cypress
npm run cypress:run
```

**Opción 2: Manual**

1. Copia `.env.cypress` a `.env`:
```bash
copy .env.cypress .env
```

2. Inicia el servidor:
```bash
php artisan serve
```

3. Ejecuta Cypress:
```bash
npm run cypress:run
```

4. Restaura `.env` original después:
```bash
# Restaura tu .env de desarrollo
```

## 🚀 Ejecución Completa

### Paso 1: Preparar BD de Test

Cypress lo hace automáticamente en `before:run`, pero puedes hacerlo manualmente:

```bash
# Asegúrate de usar .env.cypress
copy .env.cypress .env

# Limpiar y migrar
php artisan migrate:fresh --force

# Seeders base
php artisan db:seed --force

# Usuarios de prueba
php artisan db:seed --class=CypressTestUserSeeder --force
```

### Paso 2: Iniciar Servidor con BD de Test

```bash
# Opción A: Script automático
scripts\start-server-for-cypress.bat

# Opción B: Manual
php artisan serve
```

### Paso 3: Ejecutar Tests

```bash
# Modo headless (CI/CD)
npm run cypress:run

# Modo interactivo (desarrollo)
npm run cypress:open

# Test específico
npm run cypress:run -- --spec "cypress/e2e/viticulturist/plots.cy.js"
```

## ✅ Verificación

### Verificar que el servidor usa BD de test:

1. Con el servidor corriendo, verifica en los logs o ejecuta:
```bash
php artisan tinker
>>> config('database.connections.mariadb.database')
# Debe mostrar: "agro365_test"
```

### Verificar usuarios en BD de test:

```bash
php artisan tinker
>>> \App\Models\User::where('email', 'like', '%@test.com')->get(['email', 'name', 'role'])
# Debe mostrar los usuarios de prueba
```

## 🔧 Configuración Requerida

### `.env.cypress` debe tener:

```env
APP_ENV=testing
DB_CONNECTION=mariadb
DB_DATABASE=agro365_test
DB_HOST=127.0.0.1
DB_USERNAME=root
DB_PASSWORD=
```

### Usuarios Creados Automáticamente:

- `viticulturist@test.com` / `password` (con acceso beta)
- `winery@test.com` / `password` (con acceso beta)
- `supervisor@test.com` / `password` (con acceso beta)

## 🐛 Troubleshooting

### Error: "Login failed - invalid credentials"

**Causa**: El servidor está usando BD de desarrollo, no la de test.

**Solución**:
1. Detén el servidor (Ctrl+C)
2. Copia `.env.cypress` a `.env`
3. Reinicia el servidor
4. Vuelve a ejecutar los tests

### Error: "User beta access expired"

**Causa**: El usuario no tiene acceso beta configurado.

**Solución**: El seeder `CypressTestUserSeeder` otorga acceso beta automáticamente. Verifica que se ejecute correctamente.

### Error: "Base de datos no encontrada"

**Causa**: La BD `agro365_test` no existe.

**Solución**:
```bash
mysql -u root -e "CREATE DATABASE agro365_test;"
```

## 📝 Notas

- **NUNCA** ejecutes tests con el servidor usando BD de desarrollo
- **SIEMPRE** verifica que el servidor use `agro365_test` antes de ejecutar tests
- Los usuarios de prueba se crean automáticamente antes de cada suite de tests
- La BD se limpia automáticamente después de los tests

