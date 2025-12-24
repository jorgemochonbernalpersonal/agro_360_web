# Guía de Gestión de Colas en Producción

## Cómo Detener las Colas en Producción

### Opción 1: Reiniciar Workers (Recomendado - Termina después del job actual)

Este comando hace que los workers terminen después de procesar el job actual:

```bash
php artisan queue:restart
```

**Ventajas:**
- Los jobs actuales terminan correctamente
- No pierdes jobs en proceso
- Los workers se detienen de forma segura

**Desventajas:**
- Los workers seguirán ejecutándose hasta terminar el job actual (puede tardar hasta 10 minutos si hay un job largo)

### Opción 2: Detener el Schedule Temporalmente

Si usas el schedule de Laravel (que ejecuta `queue:work` cada minuto), puedes comentarlo temporalmente:

**En producción, edita `routes/console.php`:**

```php
// Comentar temporalmente esta línea:
// Schedule::command('queue:work --stop-when-empty --max-time=50')
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->runInBackground();
```

Luego haz commit y push:

```bash
git add routes/console.php
git commit -m "Temporal: Detener procesamiento de colas"
git push
```

**Después de desplegar, los workers dejarán de ejecutarse automáticamente.**

### Opción 3: Matar Procesos Manualmente (Solo si es urgente)

Si necesitas detener inmediatamente:

```bash
# Encontrar procesos de queue:work
ps aux | grep "queue:work"

# Matar procesos (reemplaza PID con el número del proceso)
kill -9 PID
```

⚠️ **Advertencia:** Esto puede dejar jobs en estado inconsistente.

## Verificar Estado de las Colas

### Ver Jobs Pendientes

```bash
php artisan queue:monitor
```

O consultar directamente la base de datos:

```sql
SELECT COUNT(*) as pending FROM jobs WHERE queue = 'default';
```

### Ver Jobs Fallidos

```bash
php artisan queue:failed
```

### Ver Detalles de un Job Fallido

```bash
php artisan queue:failed:show {id}
```

## Limpiar Jobs

### Limpiar Jobs Fallidos

```bash
# Ver jobs fallidos
php artisan queue:failed

# Reintentar un job fallido específico
php artisan queue:retry {id}

# Reintentar todos los jobs fallidos
php artisan queue:retry all

# Eliminar un job fallido
php artisan queue:forget {id}

# Eliminar todos los jobs fallidos
php artisan queue:flush
```

### Limpiar Jobs Antiguos de la Tabla `jobs`

Si hay muchos jobs pendientes que ya no necesitas:

```sql
-- Ver cuántos jobs hay
SELECT COUNT(*) FROM jobs;

-- Eliminar todos los jobs pendientes (¡CUIDADO!)
TRUNCATE TABLE jobs;

-- O eliminar solo los antiguos (más de 1 día)
DELETE FROM jobs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
```

## Reiniciar las Colas Después de Detenerlas

### Opción 1: Descomentar el Schedule

Si comentaste el schedule, descoméntalo:

```php
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

### Opción 2: Ejecutar Manualmente

```bash
# Ejecutar una vez (procesa todos los jobs pendientes y termina)
php artisan queue:work --stop-when-empty

# Ejecutar en background (se queda corriendo)
nohup php artisan queue:work > /dev/null 2>&1 &
```

## Solución para Jobs que se Quedan en Pendiente

Si los jobs se quedan en pendiente mucho tiempo, puede ser porque:

1. **El worker no está corriendo**: Verifica con `ps aux | grep queue:work`
2. **Hay un error en el job**: Revisa `php artisan queue:failed`
3. **El job está bloqueado**: Los jobs pueden quedar bloqueados si un worker muere

### Desbloquear Jobs Bloqueados

```bash
# Laravel automáticamente desbloquea jobs después de `retry_after` segundos (configurado en config/queue.php)
# Pero puedes forzarlo ejecutando:
php artisan queue:restart
```

O manualmente en la base de datos:

```sql
-- Ver jobs bloqueados (con reserved_at pero sin procesar)
SELECT * FROM jobs WHERE reserved_at > 0 AND reserved_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 MINUTE));

-- Desbloquear jobs (marcar como no reservados)
UPDATE jobs SET reserved_at = 0, attempts = attempts + 1 WHERE reserved_at > 0 AND reserved_at < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 MINUTE));
```

## Comandos Útiles para Producción

```bash
# Ver estado general
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reiniciar workers (termina después del job actual)
php artisan queue:restart

# Procesar jobs manualmente una vez
php artisan queue:work --stop-when-empty --max-time=300

# Ver logs de la cola
tail -f storage/logs/laravel.log | grep -i queue
```

## Recomendación para tu Caso

Si los jobs se quedan en pendiente porque hay un error (como el de `total_area`):

1. **Detener las colas temporalmente:**
   ```bash
   php artisan queue:restart
   ```

2. **Limpiar jobs fallidos antiguos:**
   ```bash
   php artisan queue:flush
   ```

3. **Desplegar el código corregido** (con `area` en lugar de `total_area`)

4. **Reiniciar las colas:**
   - Si usas schedule, ya se reiniciará automáticamente
   - O ejecuta: `php artisan queue:work --stop-when-empty`

5. **Verificar que funciona:**
   ```bash
   php artisan queue:monitor
   ```

