# Configuración Automática del Scheduler

## ⚠️ Requisito: Ejecutar como Administrador

El script necesita permisos de administrador para crear la tarea programada.

## Opción 1: Script Automático (Recomendado)

1. **Abre PowerShell como Administrador:**
   - Presiona `Win + X`
   - Selecciona "Windows PowerShell (Administrador)" o "Terminal (Administrador)"

2. **Navega al proyecto:**
   ```powershell
   cd C:\Users\jorge\Desktop\cue\agro365_web
   ```

3. **Ejecuta el script:**
   ```powershell
   .\setup-scheduler.ps1
   ```

4. **Verifica que se creó:**
   ```powershell
   Get-ScheduledTask -TaskName "Agro365_Laravel_Scheduler"
   ```

## Opción 2: Configuración Manual

Si prefieres configurarlo manualmente:

1. Abre **Task Scheduler** (Programador de tareas)
   - Presiona `Win + R`
   - Escribe: `taskschd.msc`
   - Presiona Enter

2. Crea una tarea básica:
   - Click en "Crear tarea básica..." en el panel derecho
   - Nombre: `Agro365_Laravel_Scheduler`
   - Descripción: `Ejecuta Laravel Scheduler cada minuto`

3. Configura el trigger:
   - Selecciona "Cuando se inicia el equipo"
   - Luego en "Editar", cambia a:
     - Repetir cada: `1 minuto`
     - Duración: `Indefinidamente`

4. Configura la acción:
   - Acción: "Iniciar un programa"
   - Programa/script: `C:\tools\php84\php.exe`
   - Agregar argumentos: `artisan schedule:run`
   - Iniciar en: `C:\Users\jorge\Desktop\cue\agro365_web`

5. Configuración adicional:
   - Marca "Ejecutar con los privilegios más altos"
   - En "Condiciones", desmarca "Iniciar la tarea solo si el equipo está conectado a la alimentación de CA"

6. Guarda la tarea

## Verificar que Funciona

### Ver la tarea:
```powershell
Get-ScheduledTask -TaskName "Agro365_Laravel_Scheduler"
```

### Ver el historial:
1. Abre Task Scheduler
2. Busca la tarea "Agro365_Laravel_Scheduler"
3. Click derecho → "Historial"

### Probar manualmente:
```powershell
# Ejecutar el scheduler manualmente
cd C:\Users\jorge\Desktop\cue\agro365_web
php artisan schedule:run

# Ver qué comandos están programados
php artisan schedule:list
```

## Eliminar la Tarea

Si necesitas eliminar la tarea:

```powershell
Unregister-ScheduledTask -TaskName "Agro365_Laravel_Scheduler" -Confirm:$false
```

O desde Task Scheduler:
1. Busca la tarea
2. Click derecho → Eliminar

## ¿Cómo Funciona?

1. **Task Scheduler** ejecuta `php artisan schedule:run` cada minuto
2. Laravel verifica qué comandos deben ejecutarse
3. Si el comando `users:cleanup-unverified` está programado para ese momento, se ejecuta
4. El comando elimina usuarios no verificados de hace más de 24 horas

## Notas Importantes

- ✅ La tarea se ejecuta aunque no estés logueado
- ✅ El consumo de recursos es mínimo
- ✅ El comando de limpieza solo se ejecuta cada hora
- ⚠️ Asegúrate de que PHP esté en el PATH o usa la ruta completa

