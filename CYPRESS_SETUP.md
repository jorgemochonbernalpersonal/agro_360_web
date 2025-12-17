# Configuración de Cypress para Tests E2E

## Instalación

Cypress ya está instalado en el proyecto. Solo necesitas asegurarte de que las dependencias estén actualizadas:

```bash
npm install
```

## Estructura de Tests

Los tests están organizados por funcionalidad del rol de Viticultor:

```
cypress/
├── e2e/
│   └── viticulturist/
│       ├── auth.cy.js              # Tests de autenticación
│       ├── dashboard.cy.js         # Tests del dashboard
│       ├── campaigns.cy.js         # Tests de campañas
│       ├── crews.cy.js             # Tests de cuadrillas
│       ├── machinery.cy.js         # Tests de maquinaria
│       └── digital-notebook.cy.js  # Tests del cuaderno digital
├── fixtures/
│   └── viticulturist.json         # Datos de prueba
└── support/
    ├── commands.js                 # Comandos personalizados
    └── e2e.js                      # Configuración global
```

## Comandos Disponibles

### Ejecutar Tests

```bash
# Abrir interfaz gráfica de Cypress
npm run cypress:open

# Ejecutar tests en modo headless
npm run cypress:run

# Ejecutar tests con navegador visible
npm run cypress:run:headed

# Ejecutar tests E2E (alias)
npm run test:e2e
```

## Comandos Personalizados

### Login

```javascript
// Login como viticultor (usa credenciales por defecto)
cy.loginAsViticulturist()

// Login con credenciales específicas
cy.loginAsViticulturist('email@example.com', 'password')

// Login como cualquier rol
cy.loginAs('viticulturist', 'email@example.com', 'password')
```

### Livewire

```javascript
// Esperar a que Livewire termine de cargar
cy.waitForLivewire()

// Click en un botón Livewire
cy.clickLivewire('button[wire\\:click="save"]')

// Llenar un campo Livewire
cy.fillLivewireField('name', 'Valor del campo')

// Seleccionar una opción en un select Livewire
cy.selectLivewireOption('plot_id', '1')
```

### Mensajes

```javascript
// Verificar mensaje flash (busca en .glass-card)
cy.shouldSeeFlashMessage('Operación exitosa')

// Verificar mensaje de error en un campo
cy.shouldSeeError('email', 'El email es requerido')
```

## Selectores Comunes

### Formularios

```javascript
// Inputs con wire:model
cy.get('input[wire\\:model="name"]')
cy.get('input[wire\\:model="name"]#name') // Con ID específico

// Selects con wire:model
cy.get('select[wire\\:model="plot_id"]')
cy.get('select[wire\\:model.live="yearFilter"]')

// Textareas
cy.get('textarea[wire\\:model="description"]')
```

### Filtros

```javascript
// Inputs de búsqueda
cy.get('input[wire\\:model.live.debounce.300ms="search"]')

// Selects de filtro
cy.get('select[wire\\:model.live="typeFilter"]')
```

### Botones y Enlaces

```javascript
// Botones de acción
cy.contains('button', 'Guardar')
cy.contains('Nueva Campaña').click()

// Enlaces
cy.get('a[href*="create"]')
```

### Navegación

```javascript
// Sidebar
cy.get('#sidebar').contains('Parcelas').click()

// Logout
cy.get('form[action*="logout"]').submit()
```

## Configuración

El archivo `cypress.config.js` está configurado para:

- **Base URL**: `http://127.0.0.1:8000`
- **Viewport**: 1280x720
- **Video**: Habilitado (grabación de tests)
- **Screenshots**: Habilitado en fallos
- **Timeouts**: 10 segundos por defecto

## Requisitos Previos

Antes de ejecutar los tests, asegúrate de:

1. **Servidor Laravel corriendo**:
   ```bash
   php artisan serve
   ```

2. **Base de datos configurada**:
   - Verifica que `phpunit.xml` tenga la configuración correcta
   - Ejecuta migraciones: `php artisan migrate`

3. **Usuario de prueba creado** (IMPORTANTE):
   ```bash
   # Opción 1: Usar el seeder
   php artisan db:seed --class=ViticulturistTestUserSeeder
   
   # Opción 2: Crear manualmente en tinker
   php artisan tinker
   ```
   ```php
   \App\Models\User::create([
       'name' => 'Test Viticulturist',
       'email' => 'viticulturist@example.com',
       'password' => bcrypt('password'),
       'role' => 'viticulturist',
       'email_verified_at' => now(),
   ]);
   ```
   
   El usuario debe tener:
   - Email: `viticulturist@example.com`
   - Password: `password`
   - Rol: `viticulturist`
   - Email verificado

## Ejecutar Tests Específicos

```bash
# Ejecutar solo tests de autenticación
npx cypress run --spec "cypress/e2e/viticulturist/auth.cy.js"

# Ejecutar solo tests de campañas
npx cypress run --spec "cypress/e2e/viticulturist/campaigns.cy.js"
```

## Debugging

### Ver logs en consola

```javascript
cy.log('Mensaje de debug')
cy.window().then((win) => {
  console.log('Estado de Livewire:', win.Livewire)
})
```

### Pausar ejecución

```javascript
cy.pause() // Pausa la ejecución para inspeccionar
```

### Capturas de pantalla

```javascript
cy.screenshot('nombre-de-la-captura')
```

## Mejores Prácticas

1. **Usar comandos personalizados**: Reutiliza comandos para mantener los tests DRY
2. **Esperar a Livewire**: Siempre usa `cy.waitForLivewire()` después de interacciones
3. **Datos de prueba**: Usa fixtures para datos consistentes
4. **Selectores**: Prefiere `data-cy` attributes sobre clases CSS
5. **Timeouts**: Ajusta timeouts según la complejidad de las operaciones

## Troubleshooting

### Tests fallan por timeout

- Aumenta `defaultCommandTimeout` en `cypress.config.js`
- Verifica que el servidor Laravel esté corriendo
- Verifica que Livewire esté cargado correctamente

### Errores de Livewire

- Los errores de Livewire están siendo ignorados en `e2e.js`
- Si necesitas capturar errores específicos, ajusta el handler de excepciones

### Problemas de autenticación

- Verifica que el usuario de prueba exista en la base de datos
- Verifica que las rutas de login sean correctas
- Revisa los logs de Laravel para errores de autenticación

