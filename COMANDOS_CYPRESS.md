# Comandos para Ejecutar Tests de Cypress

## ⚠️ IMPORTANTE: Iniciar el Servidor Primero

**ANTES de ejecutar cualquier comando de Cypress, el servidor Laravel DEBE estar corriendo en `http://127.0.0.1:8000`**

### 📝 Nota sobre Variables de Entorno

**Cypress NO modifica tu `.env` de desarrollo:**

-   Cypress lee `.env.cypress` directamente para ejecutar comandos Artisan
-   Tu `.env` de desarrollo permanece intacto
-   El servidor Laravel SÍ necesita leer el `.env` cuando inicia, por eso los scripts hacen backup/restore temporal

### Paso 1: Iniciar el Servidor (Obligatorio)

**Opción A: Script Automático (Recomendado)**

```bash
scripts\start-server-for-cypress.bat
```

Este script:

-   Hace backup de tu `.env` actual
-   Copia temporalmente `.env.cypress` a `.env` (solo para que Laravel lo lea)
-   Inicia el servidor Laravel en `http://127.0.0.1:8000`
-   Al detener el servidor (Ctrl+C), restaura automáticamente tu `.env` original
-   Deja el servidor corriendo (mantén esta terminal abierta)

**Opción B: Manual**

1. Copia `.env.cypress` a `.env` (temporalmente, para que Laravel lo lea):

    ```bash
    copy .env.cypress .env
    ```

2. Inicia el servidor (mantén esta terminal abierta):

    ```bash
    php artisan serve
    ```

3. **IMPORTANTE:** Después de los tests, restaura tu `.env` original:
    ```bash
    # Restaura tu .env de desarrollo manualmente
    ```

### Paso 2: Ejecutar Tests (En otra terminal)

Una vez que el servidor esté corriendo, abre **otra terminal** y ejecuta:

## 🚀 Comandos Principales

### Modo Interactivo (Recomendado para Desarrollo)

```bash
npm run cypress:open
```

Abre la interfaz gráfica de Cypress para seleccionar y ejecutar tests individualmente.

### Modo Headless (CI/CD)

```bash
npm run cypress:run
```

Ejecuta todos los tests sin interfaz gráfica (modo headless).

### Modo Headless con Navegador Visible

```bash
npm run cypress:run:headed
```

Ejecuta los tests mostrando el navegador en pantalla.

### Script Completo Automático

```bash
npm run cypress:test
```

Ejecuta el script completo que:

-   Configura la BD de test automáticamente
-   Ejecuta migraciones y seeders
-   Ejecuta los tests
-   Restaura el `.env` original

**Nota:** Este script NO inicia el servidor, solo ejecuta los tests. El servidor debe estar corriendo previamente.

### Alias para Tests E2E

```bash
npm run test:e2e
```

Equivalente a `cypress:run`.

## 🎯 Ejecutar Test Específico

```bash
npm run cypress:run -- --spec "cypress/e2e/viticulturist/nombre-del-test.cy.js"
```

**Ejemplos:**

```bash
npm run cypress:run -- --spec "cypress/e2e/viticulturist/auth.cy.js"
npm run cypress:run -- --spec "cypress/e2e/viticulturist/dashboard.cy.js"
npm run cypress:run -- --spec "cypress/e2e/viticulturist/campaigns.cy.js"
```

## 📁 Ejecutar Tests por Directorio

Puedes ejecutar todos los tests de un directorio específico usando patrones glob. Esto es útil para ejecutar solo los tests relacionados con una funcionalidad específica.

### Todos los tests de un directorio

```bash
# Todos los tests de viticulturist (headless)
npm run cypress:run -- --spec "cypress/e2e/viticulturist/**/*.cy.js"

# Todos los tests de viticulturist (con navegador visible)
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/**/*.cy.js"

# Modo interactivo (seleccionar desde la UI)
npm run cypress:open
```

### Ejemplos por funcionalidad específica

```bash
# Solo tests de autenticación
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/auth.cy.js"

# Solo tests de dashboard
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/dashboard.cy.js"

# Solo tests de personal y equipos
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/personal.cy.js"

# Solo tests de parcelas
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/plots.cy.js"

# Solo tests de productos fitosanitarios
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/phytosanitary-products.cy.js"

# Solo tests de campañas (todos los que empiezan con "campaign")
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/campaign*.cy.js"

# Solo tests de cuaderno digital
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/digital-notebook*.cy.js"

# Solo tests de facturación
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/invoices.cy.js"

# Solo tests de maquinaria
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/machinery.cy.js"
```

### Múltiples archivos o patrones

```bash
# Múltiples archivos específicos
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/personal.cy.js" --spec "cypress/e2e/viticulturist/plots.cy.js"

# Múltiples archivos usando patrón (autenticación y dashboard)
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/{auth,dashboard}.cy.js"

# Parcelas y productos fitosanitarios
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/{plots,phytosanitary-products}.cy.js"

# Todos los tests de campañas y cuaderno digital
npm run cypress:run:headed -- --spec "cypress/e2e/viticulturist/campaign*.cy.js" --spec "cypress/e2e/viticulturist/digital-notebook*.cy.js"
```

### Estructura de directorios disponible

```
cypress/e2e/
└── viticulturist/
    ├── auth.cy.js
    ├── campaign-2025.cy.js
    ├── campaigns.cy.js
    ├── clients.cy.js
    ├── crews.cy.js
    ├── dashboard.cy.js
    ├── digital-notebook-activities.cy.js
    ├── digital-notebook.cy.js
    ├── invoices.cy.js
    ├── machinery.cy.js
    ├── personal.cy.js
    ├── phytosanitary-products.cy.js
    ├── plots.cy.js
    ├── sidebar.cy.js
    ├── subscription.cy.js
    └── toast-notifications.cy.js
```

## 📋 Flujo Completo de Ejecución

1. **Terminal 1 - Iniciar servidor:**

    ```bash
    scripts\start-server-for-cypress.bat
    ```

    (Mantén esta terminal abierta)

2. **Terminal 2 - Ejecutar tests:**

    ```bash
    npm run cypress:run:headed
    # o
    npm run cypress:open
    # o
    npm run cypress:run
    ```

3. **Al terminar:** Detén el servidor en Terminal 1 con `Ctrl+C`

## 📝 Notas Importantes

-   ⚠️ **El servidor Laravel DEBE estar corriendo** en `http://127.0.0.1:8000` antes de ejecutar cualquier comando de Cypress
-   **Cypress NO modifica tu `.env`** - lee directamente `.env.cypress` para sus comandos
-   **El servidor Laravel** necesita leer el `.env` cuando inicia, por eso los scripts hacen backup/restore temporal
-   Cypress configura automáticamente la BD de test en `before:run` usando variables de `.env.cypress`
-   El servidor Laravel debe usar la misma BD de test que Cypress (`agro365_test`)
-   La BD de test está definida en `.env.cypress`
-   Si ves el error "Cypress could not verify that this server is running", significa que el servidor no está activo
-   **Usa el script automático** (`start-server-for-cypress.bat`) para evitar modificar manualmente el `.env`

## 🔗 Referencias

Para más detalles, consulta:

-   `CYPRESS_RUN_GUIDE.md` - Guía completa de ejecución
-   `CYPRESS_SETUP.md` - Configuración y estructura de tests
-   `TEST_EXECUTION_GUIDE.md` - Guía general de ejecución de tests
