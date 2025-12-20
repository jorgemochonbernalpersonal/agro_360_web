# Guía de Ejecución de Tests

## ✅ Estado Actual

### Tests Unitarios - Campaña 2024
**Estado**: ✅ Todos pasando (7/7 tests, 278 aserciones)

**Ejecutar**:
```bash
php artisan test --filter Campaign2024Test
```

**Resultado esperado**:
```
PASS  Tests\Unit\Models\Campaign2024Test
✓ campaign 2024 exists and is inactive
✓ campaign 2024 has activities
✓ campaign 2024 activities have related data
✓ campaign 2024 can be activated
✓ campaign 2024 activities are associated with plots
✓ campaign 2024 statistics are correct
✓ campaign 2024 date range is correct

Tests:    7 passed (278 assertions)
```

## 🔧 Configuración de Base de Datos

Los tests usan la base de datos de desarrollo (`agro365`) con `RefreshDatabase`, que:
- ✅ Ejecuta migraciones antes de cada test
- ✅ Limpia la base de datos después de cada test
- ✅ Asegura que los tests no afecten datos de desarrollo

**Configuración en `phpunit.xml`**:
```xml
<env name="DB_CONNECTION" value="mariadb"/>
<env name="DB_DATABASE" value="agro365"/>
<env name="DB_HOST" value="127.0.0.1"/>
```

## 🧪 Tests Unitarios Disponibles

### Campaign2024Test
- Verifica existencia y estado de campaña 2024
- Verifica actividades asociadas
- Verifica relaciones y datos relacionados
- Verifica activación de campaña
- Verifica estadísticas
- Verifica rangos de fechas

**Ejecutar todos los tests unitarios**:
```bash
php artisan test tests/Unit
```

**Ejecutar todos los tests**:
```bash
php artisan test
```

## 🌐 Tests E2E con Cypress

### Preparación
1. Asegúrate de que el servidor Laravel esté corriendo:
```bash
php artisan serve
```

2. Ejecuta el seeder para crear datos de prueba:
```bash
php artisan db:seed --class=CompleteTestUserSeeder
```

### Ejecutar Tests de Cypress

**Modo interactivo** (recomendado para desarrollo):
```bash
npm run cypress:open
```

**Modo headless** (para CI/CD):
```bash
npm run cypress:run
```

**Ejecutar test específico**:
```bash
npm run cypress:run -- --spec "cypress/e2e/viticulturist/campaign-2025.cy.js"
```

### Tests E2E Disponibles

#### campaign-2025.cy.js
- Verifica que la campaña 2025 se muestra como activa
- Verifica detalles de la campaña con actividades
- Navega al cuaderno digital
- Crea nueva actividad
- Filtra actividades por campaña
- Verifica estructura completa de datos

## 📝 Notas Importantes

### Orden de Creación en Seeder
El seeder `CompleteTestUserSeeder` ahora crea los productos fitosanitarios **antes** de las actividades, para que las actividades fitosanitarias puedan tener tratamientos asociados.

### Datos de Prueba
- **Usuario**: `bernalmochonjorge@gmail.com` / `cocoteq22`
- **Campaña 2024**: Inactiva (para tests unitarios)
- **Campaña 2025**: Activa (para tests E2E)

### Troubleshooting

**Error: "could not find driver"**
- Verifica que PHP tenga la extensión `pdo_mysql` o `pdo_mariadb`
- Ejecuta: `php -m | findstr pdo`

**Error: "Base de datos no existe"**
- Los tests usan `RefreshDatabase` que crea las tablas automáticamente
- Asegúrate de que la base de datos `agro365` exista

**Error en Cypress: "Cannot connect to server"**
- Asegúrate de que el servidor Laravel esté corriendo en `http://127.0.0.1:8000`
- Verifica la configuración en `cypress.config.js`

## 🚀 Próximos Pasos

1. ✅ Tests unitarios funcionando
2. ⏳ Ejecutar tests de Cypress
3. ⏳ Agregar más tests según necesidades
4. ⏳ Configurar CI/CD para ejecutar tests automáticamente

