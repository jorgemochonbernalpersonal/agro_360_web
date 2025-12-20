# Estrategia de Testing - Agro365

## 📋 Resumen

Esta estrategia divide los tests en dos categorías principales:
- **Tests Unitarios**: Para la campaña 2024 (datos históricos)
- **Tests E2E (Cypress)**: Para la campaña 2025 (datos activos)

## 🎯 Usuario de Prueba

**Email**: `bernalmochonjorge@gmail.com`  
**Contraseña**: `cocoteq22`  
**Rol**: Viticultor

Este usuario tiene datos completos para ambos tipos de tests.

## 📊 Datos del Usuario de Prueba

### Campañas
- **Campaña 2024**: Inactiva (para tests unitarios)
- **Campaña 2025**: Activa (para tests E2E con Cypress)

### Datos Generados
- ✅ 4 Parcelas
- ✅ 4 Plantaciones
- ✅ 2 Cuadrillas
- ✅ 3 Máquinas
- ✅ 19 Actividades agrícolas en 2024
- ✅ 19 Actividades agrícolas en 2025
- ✅ 5 Productos fitosanitarios

### Tipos de Actividades
- Tratamientos fitosanitarios
- Fertilizaciones
- Riegos
- Trabajos culturales
- Observaciones

## 🧪 Tests Unitarios - Campaña 2024

**Archivo**: `tests/Unit/Models/Campaign2024Test.php`

### Propósito
Verificar la lógica de negocio con datos históricos (campaña 2024 inactiva).

### Tests Incluidos
1. ✅ Verificar que la campaña 2024 existe y está inactiva
2. ✅ Verificar que tiene actividades asociadas
3. ✅ Verificar que las actividades tienen datos relacionados
4. ✅ Verificar que se puede activar la campaña
5. ✅ Verificar que las actividades están asociadas con parcelas
6. ✅ Verificar estadísticas de la campaña
7. ✅ Verificar rango de fechas de la campaña

### Ejecución
```bash
php artisan test --filter Campaign2024Test
```

## 🌐 Tests E2E (Cypress) - Campaña 2025

**Archivo**: `cypress/e2e/viticulturist/campaign-2025.cy.js`

### Propósito
Verificar el flujo completo del usuario con datos activos (campaña 2025 activa).

### Tests Incluidos
1. ✅ Verificar que la campaña 2025 se muestra como activa
2. ✅ Ver detalles de la campaña 2025 con actividades
3. ✅ Navegar al cuaderno digital desde la campaña
4. ✅ Crear nueva actividad en la campaña 2025
5. ✅ Filtrar actividades por campaña 2025
6. ✅ Ver parcelas asociadas con actividades de la campaña
7. ✅ Verificar estructura completa de datos

### Ejecución
```bash
npm run cypress:open
# O
npm run cypress:run
```

## 🔄 Crear/Actualizar Datos de Prueba

### Ejecutar el Seeder Completo
```bash
php artisan db:seed --class=CompleteTestUserSeeder
```

Este seeder:
- Crea o actualiza el usuario `bernalmochonjorge@gmail.com`
- Crea campañas 2024 y 2025
- Crea parcelas, plantaciones, cuadrillas, maquinaria
- Crea actividades agrícolas para ambas campañas
- Crea productos fitosanitarios

### Notas Importantes
- El seeder usa `firstOrCreate`, por lo que es seguro ejecutarlo múltiples veces
- Los datos se crean en una transacción, si falla algo, se revierte todo
- Los productos fitosanitarios son globales (no tienen `viticulturist_id`)

## 📁 Estructura de Archivos

```
agro365_web/
├── database/
│   └── seeders/
│       ├── CompleteTestUserSeeder.php  # Seeder principal
│       └── CypressTestUserSeeder.php   # Seeder simple (legacy)
├── tests/
│   └── Unit/
│       └── Models/
│           └── Campaign2024Test.php    # Tests unitarios 2024
└── cypress/
    └── e2e/
        └── viticulturist/
            ├── campaigns.cy.js         # Tests generales
            └── campaign-2025.cy.js     # Tests específicos 2025
```

## ✅ Coherencia de la Estrategia

### ¿Por qué separar 2024 y 2025?

1. **Tests Unitarios (2024)**:
   - Datos históricos son ideales para probar lógica de negocio
   - No requieren interacción del usuario
   - Más rápidos de ejecutar
   - Prueban relaciones y cálculos

2. **Tests E2E (2025)**:
   - Datos activos son ideales para probar flujos completos
   - Requieren interacción del usuario
   - Prueban la interfaz y la experiencia de usuario
   - Verifican que todo funciona en conjunto

### Ventajas
- ✅ Separación clara de responsabilidades
- ✅ Tests más rápidos (unitarios) y completos (E2E)
- ✅ Datos realistas para ambos escenarios
- ✅ Fácil mantenimiento y actualización

## 🚀 Próximos Pasos

1. Ejecutar tests unitarios para verificar que todo funciona
2. Ejecutar tests de Cypress para verificar flujos E2E
3. Agregar más tests según sea necesario
4. Mantener el seeder actualizado cuando se agreguen nuevas funcionalidades

## 📝 Notas Adicionales

- Los tests unitarios usan `RefreshDatabase` para limpiar la BD entre tests
- Los tests de Cypress usan el usuario real con datos persistentes
- El seeder puede ejecutarse en cualquier momento para resetear/actualizar datos

