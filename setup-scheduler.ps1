# Script para configurar Laravel Scheduler en Windows Task Scheduler
# Ejecutar como Administrador

Write-Host "=== Configuración de Laravel Scheduler ===" -ForegroundColor Green

# Obtener rutas
$projectPath = $PSScriptRoot
$phpPath = (Get-Command php -ErrorAction SilentlyContinue).Source

if (-not $phpPath) {
    Write-Host "ERROR: PHP no encontrado en PATH" -ForegroundColor Red
    Write-Host "Por favor, asegúrate de que PHP esté instalado y en el PATH" -ForegroundColor Yellow
    exit 1
}

Write-Host "Ruta del proyecto: $projectPath" -ForegroundColor Cyan
Write-Host "Ruta de PHP: $phpPath" -ForegroundColor Cyan

# Nombre de la tarea
$taskName = "Agro365_Laravel_Scheduler"
$taskDescription = "Ejecuta Laravel Scheduler cada minuto para Agro365"

# Comando a ejecutar
$command = "`"$phpPath`""
$arguments = "artisan schedule:run"
$workingDirectory = $projectPath

# Eliminar tarea existente si existe
Write-Host "`nVerificando si existe tarea anterior..." -ForegroundColor Yellow
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Write-Host "Eliminando tarea existente..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue
}

# Crear la acción
$action = New-ScheduledTaskAction -Execute $command -Argument $arguments -WorkingDirectory $workingDirectory

# Crear el trigger (cada minuto)
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 365)

# Configurar la tarea para ejecutarse aunque el usuario no esté conectado
$principal = New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType Interactive -RunLevel Highest

# Configuración de la tarea
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable:$false

# Registrar la tarea
try {
    Write-Host "`nCreando tarea programada..." -ForegroundColor Yellow
    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description $taskDescription -Force | Out-Null
    
    Write-Host "`n✅ Tarea programada creada exitosamente!" -ForegroundColor Green
    Write-Host "`nDetalles de la tarea:" -ForegroundColor Cyan
    Write-Host "  Nombre: $taskName" -ForegroundColor White
    Write-Host "  Descripción: $taskDescription" -ForegroundColor White
    Write-Host "  Frecuencia: Cada minuto" -ForegroundColor White
    Write-Host "  Comando: $command $arguments" -ForegroundColor White
    Write-Host "  Directorio: $workingDirectory" -ForegroundColor White
    
    Write-Host "`n📋 Para verificar la tarea:" -ForegroundColor Yellow
    Write-Host "  Get-ScheduledTask -TaskName '$taskName'" -ForegroundColor White
    
    Write-Host "`n📋 Para eliminar la tarea:" -ForegroundColor Yellow
    Write-Host "  Unregister-ScheduledTask -TaskName '$taskName' -Confirm:`$false" -ForegroundColor White
    
    Write-Host "`n✅ El scheduler de Laravel ahora se ejecutará automáticamente cada minuto." -ForegroundColor Green
    Write-Host "   El comando de limpieza se ejecutará cada hora." -ForegroundColor Green
    
} catch {
    Write-Host "`n❌ Error al crear la tarea: $_" -ForegroundColor Red
    Write-Host "`n💡 Asegúrate de ejecutar este script como Administrador:" -ForegroundColor Yellow
    Write-Host "   Right-click PowerShell → Ejecutar como administrador" -ForegroundColor White
    exit 1
}

