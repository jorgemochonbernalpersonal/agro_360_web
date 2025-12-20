# Configuración de Base de Datos para Tests

## Crear Base de Datos de Test

Necesitas crear la base de datos de test manualmente. Ejecuta este comando SQL en tu cliente de base de datos (phpMyAdmin, MySQL Workbench, etc.):

```sql
CREATE DATABASE IF NOT EXISTS `agro365_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `agro365_test`.* TO 'agro365'@'%';
FLUSH PRIVILEGES;
```

O si tienes acceso root, puedes ejecutar desde la terminal:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS agro365_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON agro365_test.* TO 'agro365'@'%';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

## Alternativa: Usar la Base de Datos Existente

Si no puedes crear una base de datos separada, puedes usar la base de datos de desarrollo (`agro365`) para los tests. Los tests usan `RefreshDatabase`, que:
- Ejecuta las migraciones antes de cada test
- Limpia la base de datos después de cada test
- Asegura que los tests no afecten los datos de desarrollo

**⚠️ ADVERTENCIA**: Si usas la base de datos de desarrollo, asegúrate de que no tengas datos importantes, ya que `RefreshDatabase` puede limpiar las tablas.

## Verificar Configuración

Una vez creada la base de datos, ejecuta:

```bash
php artisan test --filter Campaign2024Test
```

Los tests deberían ejecutarse correctamente.

