# 🕐 Configuración de Cron Jobs en Hostinger

## 📋 **Guía Paso a Paso para Agro365**

---

## ⚡ **CONFIGURACIÓN RÁPIDA (5 minutos)**

### **Paso 1: Acceder al Panel de Cron Jobs**

1. Entra a **hPanel de Hostinger**
2. En el menú lateral, ve a **Avanzado**
3. Click en **Cron Jobs**

![Ubicación en el menú](https://hostinger.com/tutorials/wp-content/uploads/sites/2/2021/05/accessing-cron-jobs.png)

---

### **Paso 2: Crear Nuevo Cron Job**

Click en el botón **+ Crear Nuevo Cron Job**

---

### **Paso 3: Configurar Frecuencia**

Selecciona **"Cada minuto"** o configura manualmente:

```
Minuto:    *
Hora:      *
Día:       *
Mes:       *
Día Sem:   *
```

O selecciona en el dropdown: **"Common Settings" → "Every minute"**

---

### **Paso 4: Configurar Comando**

#### **🔍 OPCIÓN A: Instalación en Raíz del Dominio**

```bash
/usr/bin/php /home/u337373605/domains/agro365.es/public_html/artisan schedule:run >> /dev/null 2>&1
```

#### **🔍 OPCIÓN B: Si Laravel está en subcarpeta**

```bash
/usr/bin/php /home/u337373605/domains/agro365.es/public_html/laravel/artisan schedule:run >> /dev/null 2>&1
```

#### **🔍 OPCIÓN C: Versión específica de PHP**

```bash
/usr/bin/php8.3 /home/u337373605/domains/agro365.es/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

### **Paso 5: Guardar**

Click en **"Crear"** o **"Guardar"**

---

## 🔍 **CÓMO ENCONTRAR TU RUTA EXACTA**

### **Método 1: Usando SSH** ⭐ Recomendado

```bash
# Conectar por SSH
ssh u337373605@agro365.es

# Ir al directorio del sitio
cd domains/agro365.es/public_html

# Ver ruta completa
pwd
# Resultado: /home/u337373605/domains/agro365.es/public_html

# Verificar que artisan existe
ls -la artisan
# Debe mostrar: -rwxr-xr-x 1 ... artisan
```

### **Método 2: Usando File Manager**

1. Ve a **hPanel** → **File Manager**
2. Navega hasta tu instalación de Laravel
3. La ruta se muestra en la barra superior
4. Asegúrate de ver el archivo `artisan` en esa carpeta

### **Método 3: Crear archivo PHP de prueba**

Crea `path-finder.php` en tu raíz web:

```php
<?php
echo "Ruta completa: " . __DIR__ . "\n";
echo "Archivo artisan existe: " . (file_exists(__DIR__ . '/artisan') ? 'SÍ' : 'NO');
```

Accede: `https://agro365.es/path-finder.php`

---

## 📧 **CONFIGURACIÓN DE NOTIFICACIONES**

### **Sin Email (Recomendado)**
```bash
/usr/bin/php /ruta/artisan schedule:run >> /dev/null 2>&1
```

### **Con Email de Errores**
```bash
/usr/bin/php /ruta/artisan schedule:run 2>&1 | mail -s "Cron Agro365" info@agro365.es
```

### **Con Log en Archivo**
```bash
/usr/bin/php /ruta/artisan schedule:run >> /home/u337373605/cron.log 2>&1
```

---

## ⚠️ **SOLUCIÓN DE PROBLEMAS**

### **Problema 1: "PHP no encontrado"**

Prueba diferentes rutas de PHP:

```bash
# Opción 1 - PHP predeterminado
/usr/bin/php

# Opción 2 - PHP 8.3
/usr/bin/php8.3

# Opción 3 - PHP 8.2
/usr/bin/php8.2

# Opción 4 - CloudLinux
/opt/alt/php83/usr/bin/php

# Opción 5 - CloudLinux alternativo
/usr/local/bin/php
```

**Para verificar cuál tienes:**
```bash
which php
php -v
```

---

### **Problema 2: "Artisan no encontrado"**

Verifica la ruta:
```bash
# En SSH
cd /home/u337373605/domains/agro365.es/public_html
ls -la artisan
```

Si no existe:
```bash
# Buscar artisan
find /home/u337373605 -name "artisan" -type f
```

---

### **Problema 3: "Permisos denegados"**

```bash
# Dar permisos de ejecución
chmod +x /ruta/completa/artisan
chmod -R 755 /ruta/completa/storage
chmod -R 755 /ruta/completa/bootstrap/cache
```

---

### **Problema 4: "No se ejecuta el schedule"**

Verifica que el comando funciona manualmente:
```bash
cd /home/u337373605/domains/agro365.es/public_html
php artisan schedule:list
php artisan schedule:run
```

---

## ✅ **VERIFICACIÓN**

### **1. Verificar en el Panel de Hostinger**

Después de 1-2 minutos, el panel mostrará:
```
Última ejecución: hace 1 minuto
Próxima ejecución: en 0 minutos
Estado: ✅ Activo
```

---

### **2. Verificar Logs de Laravel**

```bash
# En SSH
tail -f storage/logs/laravel.log
```

Deberías ver líneas como:
```
[2026-02-15 02:00:00] local.INFO: Running scheduled command: ...
```

---

### **3. Verificar Tareas Programadas**

```bash
php artisan schedule:list
```

Salida esperada:
```
0 2 * * * remote-sensing:update-all  Next Due: 10 hours from now
0 3 * * 1 remote-sensing:clean-old-data  Next Due: 2 days from now
```

---

### **4. Ejecutar Manualmente para Probar**

```bash
php artisan schedule:run
```

Si todo está bien, verá:
```
No scheduled commands are ready to run.
```
(Es normal fuera de horario programado)

---

## 🎯 **CONFIGURACIÓN FINAL**

### **Configuración Completa en Hostinger:**

```
┌─────────────────────────────────────────────────────────────┐
│  📋 Cron Job - Laravel Scheduler                            │
├─────────────────────────────────────────────────────────────┤
│  Tipo:       Comando personalizado                          │
│  Frecuencia: * * * * * (Cada minuto)                        │
│                                                             │
│  Comando:                                                   │
│  /usr/bin/php /home/u337373605/domains/agro365.es/        │
│  public_html/artisan schedule:run >> /dev/null 2>&1        │
│                                                             │
│  Email:      (vacío - sin notificaciones)                   │
│  Estado:     ✅ Activo                                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 **QUÉ SE EJECUTARÁ AUTOMÁTICAMENTE**

Con esta configuración, Laravel ejecutará automáticamente:

### **Diario a las 2:00 AM:**
```bash
✅ remote-sensing:update-all
   - Actualiza datos NDVI de todas las parcelas
   - Con 2 segundos de delay entre cada una
   - Respeta rate limits de NASA API
```

### **Lunes a las 3:00 AM:**
```bash
✅ remote-sensing:clean-old-data --days=365
   - Elimina datos de remote sensing >1 año
   - Mantiene base de datos optimizada
```

### **Cada Minuto:**
```bash
✅ queue:work --stop-when-empty --max-time=50
   - Procesa jobs en cola
   - Notificaciones, emails, etc.
```

---

## 🔐 **SEGURIDAD**

### **Proteger el endpoint (si usas HTTP fallback)**

Si usas un servicio externo de cron, protege la ruta:

```php
// routes/api.php
Route::get('/cron', function () {
    // Verificar IP o token
    $allowedIPs = ['IP_DEL_SERVICIO_CRON'];
    
    if (!in_array(request()->ip(), $allowedIPs)) {
        abort(403);
    }
    
    Artisan::call('schedule:run');
    return response()->json(['status' => 'OK']);
})->middleware('throttle:60,1');
```

---

## 🆘 **ALTERNATIVA: Sin Acceso a Cron Jobs**

Si tu plan de Hostinger NO incluye cron jobs:

### **Opción 1: Servicio Externo Gratuito**

#### **EasyCron** (https://www.easycron.com)
- ✅ Gratis hasta 20 tareas
- ✅ Ejecución cada minuto
- ✅ Historial de ejecuciones

**Configuración:**
```
URL: https://agro365.es/api/cron
Método: GET
Frecuencia: */1 * * * * (cada minuto)
```

#### **cron-job.org** (https://cron-job.org)
- ✅ Completamente gratis
- ✅ Sin límite de tareas
- ✅ Notificaciones por email

**Configuración:**
```
URL: https://agro365.es/api/cron
Ejecución: Cada 1 minuto
```

---

### **Opción 2: Actualizar Plan de Hosting**

Contacta con Hostinger para actualizar a:
- **Business Plan** - Incluye cron jobs
- **Cloud Hosting** - Cron jobs + más recursos

---

## 📞 **SOPORTE**

### **Si algo no funciona:**

1. **Revisa logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

2. **Ejecuta manualmente:**
   ```bash
   php artisan schedule:run
   ```

3. **Verifica configuración:**
   ```bash
   php artisan schedule:list
   ```

4. **Contacta Hostinger:**
   - Live Chat en hPanel
   - Email: support@hostinger.com

---

## ✅ **CHECKLIST FINAL**

Antes de considerar que está todo listo:

- [ ] Cron job creado en hPanel
- [ ] Frecuencia: cada minuto (`* * * * *`)
- [ ] Comando correcto con ruta completa
- [ ] Estado: Activo
- [ ] Esperado 2-3 minutos
- [ ] Verificado "Última ejecución" en panel
- [ ] Revisado logs de Laravel
- [ ] Ejecutado `php artisan schedule:list`
- [ ] Confirmado que no hay errores

---

## 🎉 **RESULTADO ESPERADO**

Una vez configurado correctamente:

```
✅ Cron ejecutándose cada minuto
✅ Laravel scheduler activo
✅ Datos de remote sensing actualizándose a las 2 AM
✅ Limpieza automática cada lunes
✅ Queue procesándose automáticamente
✅ Sin intervención manual necesaria
```

---

## 📝 **NOTAS IMPORTANTES**

1. **El cron debe ejecutarse CADA MINUTO** - Laravel scheduler decide qué comandos correr
2. **NO programes los comandos directamente** - Usa `schedule:run`
3. **NO uses `schedule:work`** - Es para desarrollo local
4. **Los horarios se basan en timezone del servidor** - Verifica con `date` en SSH

---

## 🚀 **¡LISTO PARA PRODUCCIÓN!**

Con esta configuración:
- 🛰️ Tus parcelas se actualizarán automáticamente
- 📊 La base de datos se mantendrá limpia
- 🔄 Los jobs en cola se procesarán
- 📧 Las notificaciones se enviarán

**Sin necesidad de intervención manual** ✨
