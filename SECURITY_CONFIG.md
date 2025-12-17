# Configuración de Seguridad - Agro365

## ✅ Implementaciones de Seguridad

### 1. Tokens CSRF
- ✅ Protección automática de Laravel/Livewire
- ✅ Regeneración en logout y bloqueo de login
- ✅ Headers X-XSRF-Token configurados

### 2. Remember Tokens
- ✅ Campo `remember_token` en User (oculto)
- ✅ Funcionalidad "Recordarme" en login
- ✅ Útil para usuarios trabajando desde campo

### 3. Regeneración de Sesión
- ✅ En login: `session()->regenerate()`
- ✅ En logout: `session()->invalidate()` + `regenerateToken()`
- ✅ Al bloquear login sin verificar: `regenerateToken()`

### 4. Rate Limiting (Throttling)
- ✅ Login: **5 intentos por minuto por IP**
- ✅ Verificación de email: **6 intentos por minuto**

### 5. Configuración de Sesiones
- ✅ Driver: `database` (escalable, mejor para multi-usuario)
- ✅ Lifetime: **180 minutos (3 horas)** - ideal para usuarios en campo
- ✅ HttpOnly: `true` (previene XSS)
- ✅ SameSite: `lax` (previene CSRF)
- ✅ Tabla de sesiones: `sessions` (ya creada)

### 6. Verificación de Email
- ✅ Obligatoria antes de iniciar sesión
- ✅ Eliminación automática después de 24 horas sin verificar

---

## 📋 Configuración Recomendada

### Desarrollo (.env)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=180
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
APP_ENV=local
APP_DEBUG=true
```

### Producción (.env)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=180
SESSION_SECURE_COOKIE=true  # ⚠️ OBLIGATORIO
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
APP_ENV=production
APP_DEBUG=false
```

---

## 🔒 Características de Seguridad

### Rate Limiting en Login
- **5 intentos por minuto** por dirección IP
- Después de 5 intentos fallidos, el usuario debe esperar 60 segundos
- Mensaje claro: "Demasiados intentos. Por favor, intenta de nuevo en X segundos."

### Protección contra Session Fixation
- Regeneración de ID de sesión en cada login
- Invalidación completa en logout
- Regeneración de token CSRF en operaciones críticas

### Protección CSRF
- Tokens automáticos en todos los formularios
- Regeneración después de operaciones sensibles
- Headers X-XSRF-Token para peticiones AJAX

### Cookies Seguras
- `http_only: true` - No accesibles desde JavaScript
- `same_site: lax` - Protección contra CSRF
- `secure: true` en producción - Solo HTTPS

---

## 📊 Tabla de Sesiones

La tabla `sessions` almacena:
- `id`: ID único de la sesión
- `user_id`: Usuario asociado (nullable)
- `ip_address`: Dirección IP del usuario
- `user_agent`: Navegador/dispositivo
- `payload`: Datos de la sesión (encriptados)
- `last_activity`: Timestamp de última actividad

**Ventajas:**
- ✅ Sesiones compartidas entre dispositivos
- ✅ Mejor para auditoría
- ✅ Escalable con múltiples servidores
- ✅ Limpieza automática de sesiones expiradas

---

## 🛡️ Buenas Prácticas Implementadas

1. **Regeneración de Sesión**: En cada login para prevenir session fixation
2. **Invalidación Completa**: En logout para cerrar todas las sesiones
3. **Rate Limiting**: Previene ataques de fuerza bruta
4. **Verificación de Email**: Asegura que solo usuarios válidos accedan
5. **Eliminación Automática**: Usuarios no verificados se eliminan después de 24h
6. **Tokens Ocultos**: `remember_token` y `password` no se serializan

---

## ⚠️ Importante para Producción

### Checklist de Seguridad

- [ ] `SESSION_SECURE_COOKIE=true` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] `APP_DEBUG=false` en `.env`
- [ ] HTTPS configurado en el servidor
- [ ] `APP_KEY` generado y seguro
- [ ] Base de datos con credenciales seguras
- [ ] Firewall configurado
- [ ] Backups regulares de la base de datos

### Verificación

```bash
# Verificar configuración de sesiones
php artisan tinker
>>> config('session.driver')
>>> config('session.lifetime')
>>> config('session.secure')
```

---

## 🔍 Monitoreo

### Ver sesiones activas
```sql
SELECT * FROM sessions 
WHERE last_activity > EXTRACT(EPOCH FROM NOW() - INTERVAL '180 minutes')
ORDER BY last_activity DESC;
```

### Limpiar sesiones expiradas
Laravel limpia automáticamente las sesiones expiradas mediante el "lottery" system (2% de probabilidad en cada request).

Para limpiar manualmente:
```bash
php artisan session:gc
```

---

## 📝 Notas Adicionales

- **Lifetime de 180 minutos**: Ideal para usuarios trabajando en campo que pueden tener interrupciones
- **Remember Tokens**: Útiles para dispositivos móviles, pero se invalidan al cambiar contraseña
- **Rate Limiting**: Basado en IP, por lo que usuarios detrás de un proxy/NAT compartirán límites
- **Sesiones en BD**: Mejor rendimiento con Redis en alta concurrencia, pero database es suficiente para la mayoría de casos

---

## 🚀 Mejoras Futuras (Opcionales)

1. **Auditoría de Sesiones**: Log de inicios de sesión y actividad sospechosa
2. **Sesiones Múltiples**: Detectar y notificar cuando un usuario inicia sesión desde múltiples dispositivos
3. **Redis para Sesiones**: Para mejor rendimiento en alta concurrencia
4. **2FA (Two-Factor Authentication)**: Para roles administrativos
5. **IP Whitelisting**: Para acceso administrativo

