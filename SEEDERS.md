# Comandos de Seeders - Agro365

Este documento contiene todos los comandos individuales para ejecutar los seeders del proyecto.

## 📋 Índice

- [Seeders de Datos Base](#seeders-de-datos-base)
- [Seeders de Usuarios de Prueba](#seeders-de-usuarios-de-prueba)
- [Seeders de Datos de Usuario Específico](#seeders-de-datos-de-usuario-específico)
- [Seeders de Actividades y Datos de Prueba](#seeders-de-actividades-y-datos-de-prueba)
- [Seeder Principal](#seeder-principal)
- [Orden Recomendado de Ejecución](#orden-recomendado-de-ejecución)

---

## 🌍 Seeders de Datos Base

### Comunidades Autónomas
```bash
php artisan db:seed --class=AutonomousCommunitySeeder
```

### Provincias
```bash
php artisan db:seed --class=ProvinceSeeder
```

### Municipios
```bash
php artisan db:seed --class=MunicipalitySeeder
```

### Usos SIGPAC
```bash
php artisan db:seed --class=SigpacUseSeeder
```

### Variedades de Uva
```bash
php artisan db:seed --class=GrapeVarietySeeder
```

### Tipos de Maquinaria
```bash
php artisan db:seed --class=MachineryTypeSeeder
```

### Sistemas de Conducción
```bash
php artisan db:seed --class=TrainingSystemSeeder
```

### Impuestos (IVA, IGIC)
```bash
php artisan db:seed --class=TaxSeeder
```

### Plagas
```bash
php artisan db:seed --class=PestSeeder
```

---

## 👤 Seeders de Usuarios de Prueba

### Usuario para Tests de Cypress
```bash
php artisan db:seed --class=CypressTestUserSeeder
```

### Usuario Viticultor de Prueba
```bash
php artisan db:seed --class=ViticulturistTestUserSeeder
```

### Usuario Completo de Prueba
```bash
php artisan db:seed --class=CompleteTestUserSeeder
```

---

## 📊 Seeders de Datos de Usuario Específico

### Poblar Datos para un Usuario Específico
```bash
php artisan db:seed --class=SeedUserDataSeeder -- --user=ID_USUARIO
```

**Ejemplo:**
```bash
php artisan db:seed --class=SeedUserDataSeeder -- --user=9
```

**Nota:** Reemplaza `ID_USUARIO` con el ID numérico del usuario para el que quieres poblar datos.

---

## 🔄 Seeders de Actividades y Datos de Prueba

### Actividades del Cuaderno Digital
```bash
php artisan db:seed --class=DigitalNotebookActivitiesSeeder
```

### Historial de Auditoría
```bash
php artisan db:seed --class=AuditHistorySeeder
```

### Historial de Auditoría de Parcelas
```bash
php artisan db:seed --class=PlotAuditHistorySeeder
```

---

## 🚀 Seeder Principal

### Ejecutar Todos los Seeders Base
```bash
php artisan db:seed --class=DatabaseSeeder
```

O simplemente:
```bash
php artisan db:seed
```

**Nota:** El `DatabaseSeeder` ejecuta automáticamente los siguientes seeders en orden:
- `AutonomousCommunitySeeder`
- `ProvinceSeeder`
- `MunicipalitySeeder`
- `SigpacUseSeeder`
- `GrapeVarietySeeder`
- `MachineryTypeSeeder`
- `TrainingSystemSeeder`
- `TaxSeeder`

---

## 📝 Orden Recomendado de Ejecución

### 1. Primero: Datos Base (Geografía y Catálogos)
```bash
php artisan db:seed --class=AutonomousCommunitySeeder
php artisan db:seed --class=ProvinceSeeder
php artisan db:seed --class=MunicipalitySeeder
php artisan db:seed --class=SigpacUseSeeder
php artisan db:seed --class=GrapeVarietySeeder
php artisan db:seed --class=MachineryTypeSeeder
php artisan db:seed --class=TrainingSystemSeeder
php artisan db:seed --class=TaxSeeder
php artisan db:seed --class=PestSeeder
```

O ejecutar todos a la vez:
```bash
php artisan db:seed --class=DatabaseSeeder
```

### 2. Segundo: Usuarios de Prueba
```bash
php artisan db:seed --class=CypressTestUserSeeder
php artisan db:seed --class=ViticulturistTestUserSeeder
php artisan db:seed --class=CompleteTestUserSeeder
```

### 3. Tercero: Datos de Usuario Específico (opcional)
```bash
php artisan db:seed --class=SeedUserDataSeeder -- --user=ID_USUARIO
```

### 4. Cuarto: Actividades y Datos de Prueba (opcional)
```bash
php artisan db:seed --class=DigitalNotebookActivitiesSeeder
php artisan db:seed --class=AuditHistorySeeder
php artisan db:seed --class=PlotAuditHistorySeeder
```

---

## ⚠️ Notas Importantes

- **Orden de dependencias:** Algunos seeders dependen de otros. Por ejemplo, `ProvinceSeeder` requiere que `AutonomousCommunitySeeder` se haya ejecutado primero.
- **Datos de usuario:** El seeder `SeedUserDataSeeder` requiere que exista un usuario con el ID especificado.
- **Ambiente de desarrollo:** Estos seeders están diseñados para desarrollo y testing. No ejecutar en producción sin revisar.
- **Reseteo de base de datos:** Si necesitas empezar desde cero:
  ```bash
  php artisan migrate:fresh
  php artisan db:seed
  ```

---

## 🔧 Comandos Útiles Relacionados

### Ver todos los seeders disponibles
```bash
php artisan db:seed --help
```

### Ejecutar seeders con confirmación
```bash
php artisan db:seed --force
```

### Migrar y seedear en un solo comando
```bash
php artisan migrate:fresh --seed
```

---

**Última actualización:** 2024

