# Guía de Despliegue - Agro365

## 📋 Checklist Pre-Despliegue

### 1. Migraciones
- [x] Todas las migraciones ejecutadas
- [x] Tabla de sesiones creada
- [x] Seeders ejecutados (si aplica)

### 2. Configuración
- [ ] Archivo `.env` creado desde `env.production.example`
- [ ] `APP_KEY` generado: `php artisan key:generate`
- [ ] Variables de entorno configuradas
- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE_COOKIE=true`

### 3. Base de Datos
- [ ] Usuario de BD creado (no usar `postgres`)
- [ ] Contraseña segura configurada
- [ ] Backup automático configurado
- [ ] Migraciones ejecutadas: `php artisan migrate --force`

### 4. Email
- [ ] Hostinger SMTP configurado
- [ ] Credenciales de email en `.env`
- [ ] Prueba de envío de email

### 5. HTTPS
- [ ] Certificado SSL instalado
- [ ] Redirección HTTP → HTTPS configurada
- [ ] `APP_URL` con `https://`

### 6. Permisos
- [ ] `storage/` escribible: `chmod -R 775 storage`
- [ ] `bootstrap/cache/` escribible: `chmod -R 775 bootstrap/cache`

### 7. Scheduler
- [ ] Cron configurado: `* * * * * cd /ruta/proyecto && php artisan schedule:run`
- [ ] O Task Scheduler en Windows

### 8. Optimización
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `composer install --optimize-autoloader --no-dev`
- [ ] `npm run build`

---

## 🚀 Pasos de Despliegue

### 1. Preparar el Servidor

```bash
# Instalar dependencias
composer install --optimize-autoloader --no-dev

# Construir assets
npm install
npm run build
```

### 2. Configurar Entorno

```bash
# Copiar archivo de ejemplo
cp env.production.example .env

# Generar APP_KEY
php artisan key:generate

# Editar .env con tus valores
nano .env  # o tu editor preferido
```

### 3. Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders (si aplica)
php artisan db:seed --force
```

### 4. Optimizar

```bash
# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Configurar Scheduler

**Linux/Mac (Crontab):**
```bash
crontab -e
# Agregar:
* * * * * cd /ruta/a/agro365_web && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler):**
- Usar el script `setup-scheduler.ps1` como administrador

### 6. Verificar

```bash
# Verificar configuración
php artisan config:show session
php artisan config:show mail

# Probar scheduler
php artisan schedule:list

# Verificar rutas
php artisan route:list
```

---

## 📝 Archivos Importantes

- `env.production.example` - Plantilla de configuración para producción
- `SECURITY_CONFIG.md` - Documentación de seguridad
- `SCHEDULER_SETUP.md` - Configuración del scheduler
- `docker-compose.yml` - Configuración de Mailhog (solo desarrollo)

---

## ⚠️ Variables Críticas

Estas variables DEBEN estar configuradas correctamente:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=  # Generar nuevo
SESSION_SECURE_COOKIE=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
DB_PASSWORD=  # Contraseña segura
```

---

## 🔒 Seguridad en Producción

1. **NUNCA** subas `.env` al repositorio
2. **SIEMPRE** usa HTTPS
3. **VERIFICA** que `APP_DEBUG=false`
4. **USA** contraseñas fuertes para BD y email
5. **CONFIGURA** firewall del servidor
6. **HABILITA** backups automáticos

---

## 📊 Monitoreo

### Ver sesiones activas
```sql
SELECT COUNT(*) FROM sessions 
WHERE last_activity > EXTRACT(EPOCH FROM NOW() - INTERVAL '180 minutes');
```

### Ver logs
```bash
tail -f storage/logs/laravel.log
```

### Verificar scheduler
```bash
php artisan schedule:list
php artisan schedule:test
```

---

## 🆘 Troubleshooting

### Error: "Table sessions already exists"
```bash
# Marcar migración como ejecutada
php artisan tinker
>>> DB::table('migrations')->insert(['migration' => '2025_12_16_193254_create_sessions_table', 'batch' => DB::table('migrations')->max('batch') + 1]);
```

### Error: "APP_KEY not set"
```bash
php artisan key:generate
```

### Scheduler no funciona
```bash
# Verificar cron
crontab -l

# Probar manualmente
php artisan schedule:run
```

### Emails no se envían
```bash
# Verificar configuración
php artisan tinker
>>> config('mail.mailers.smtp')

# Probar envío
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

---

## 📞 Soporte

Para problemas o dudas, revisa:
- `SECURITY_CONFIG.md` - Configuración de seguridad
- `SCHEDULER_SETUP.md` - Configuración del scheduler
- Logs en `storage/logs/laravel.log`

