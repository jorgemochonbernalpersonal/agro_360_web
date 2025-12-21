# Usuarios de Prueba para Cypress

## 📋 Resumen

Cypress ahora crea automáticamente usuarios de prueba genéricos en la base de datos de test antes de ejecutar los tests. Esto asegura que:

1. ✅ Los tests no dependen de usuarios existentes en la BD de desarrollo
2. ✅ Cada ejecución de tests comienza con una BD limpia
3. ✅ Los usuarios se crean automáticamente antes de cada suite de tests
4. ✅ Los usuarios se eliminan después de los tests

## 🔧 Configuración

### Base de Datos de Test

Cypress usa una base de datos separada configurada en `.env.cypress`:

```env
DB_DATABASE=agro365_test
DB_HOST=127.0.0.1
DB_USERNAME=root
DB_PASSWORD=
```

### Usuarios Genéricos Creados

El seeder `CypressTestUserSeeder` crea los siguientes usuarios:

| Email | Password | Rol | Uso |
|-------|----------|-----|-----|
| `viticulturist@test.com` | `password` | viticulturist | Tests principales |
| `winery@test.com` | `password` | winery | Tests de bodega (si se necesitan) |
| `supervisor@test.com` | `password` | supervisor | Tests de supervisor (si se necesitan) |

## 🚀 Flujo de Ejecución

1. **Antes de ejecutar tests** (`before:run`):
   - Se ejecuta `migrate:fresh` para limpiar la BD de test
   - Se ejecutan los seeders base (comunidades, provincias, etc.)
   - Se ejecuta `CypressTestUserSeeder` para crear usuarios genéricos

2. **Durante los tests**:
   - Los tests usan `cy.loginAsViticulturist()` que por defecto usa `viticulturist@test.com`
   - Puedes especificar otro usuario: `cy.loginAsViticulturist('winery@test.com', 'password')`

3. **Después de ejecutar tests** (`after:run`):
   - Se limpia la BD de test con `migrate:fresh`

## 📝 Uso en Tests

### Login por defecto (viticulturist)

```javascript
beforeEach(() => {
  cy.loginAsViticulturist() // Usa viticulturist@test.com / password
  cy.visit('/plots')
  cy.waitForLivewire()
})
```

### Login con usuario específico

```javascript
beforeEach(() => {
  cy.loginAsViticulturist('winery@test.com', 'password')
  cy.visit('/winery/dashboard')
  cy.waitForLivewire()
})
```

### Crear usuarios dinámicos (opcional)

Si necesitas crear usuarios específicos durante un test:

```javascript
it('should test with custom user', () => {
  cy.createTestUser({
    name: 'Custom User',
    email: 'custom@test.com',
    role: 'viticulturist'
  }).then((user) => {
    cy.loginAsViticulturist(user.email, user.password)
    // ... continuar con el test
  })
})
```

## 🔍 Verificación

Para verificar que los usuarios se crean correctamente:

```bash
# Conectar a la BD de test
mysql -u root agro365_test

# Ver usuarios creados
SELECT email, name, role FROM users WHERE email LIKE '%@test.com';
```

## ⚠️ Notas Importantes

1. **BD Separada**: Los tests NUNCA deben usar la BD de desarrollo
2. **Usuarios Genéricos**: Los usuarios tienen emails genéricos (`@test.com`) para evitar conflictos
3. **Limpieza Automática**: La BD se limpia antes y después de los tests
4. **No Dependencias**: Los tests no dependen de datos existentes en la BD de desarrollo

## 🐛 Troubleshooting

### Error: "Usuario no encontrado"
- Verifica que `.env.cypress` existe y está configurado correctamente
- Verifica que la BD `agro365_test` existe
- Ejecuta manualmente: `php artisan db:seed --class=CypressTestUserSeeder --force`

### Error: "Login failed"
- Verifica que el usuario existe en la BD de test
- Verifica que el password es correcto (`password` por defecto)
- Verifica que `email_verified_at` no es null

### Error: "Base de datos no encontrada"
- Crea la BD: `CREATE DATABASE agro365_test;`
- Verifica que `.env.cypress` tiene la configuración correcta

