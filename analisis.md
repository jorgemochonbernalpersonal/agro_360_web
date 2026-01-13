# Análisis Técnico - Agro365 Web

> **Documento de Referencia Técnica**  
> Este documento proporciona un análisis completo del stack tecnológico, arquitectura, y estructura del proyecto Agro365.  
> Generado mediante análisis del código fuente. Ver sección "Verificación de Datos" para detalles de veracidad.

---

## 📋 Resumen Ejecutivo

**Agro365** es una plataforma web SaaS para la gestión integral de explotaciones agrícolas y vitivinícolas. La aplicación permite a viticultores, bodegas, supervisores y administradores gestionar parcelas, campañas agrícolas, tratamientos fitosanitarios, facturación, teledetección satelital y cumplimiento normativo PAC (Política Agraria Común).

---

## 🛠️ Stack Tecnológico

### Backend

-   **Framework**: Laravel 12.0
-   **Lenguaje**: PHP 8.2+
-   **Base de Datos**: MariaDB 11.8.3
-   **ORM**: Eloquent ORM
-   **Autenticación**: Laravel Breeze (multi-rol)
-   **Colas/Jobs**: Laravel Queue System
-   **Cache**: Laravel Cache (configurable)
-   **Logs**: Laravel Pail, Rap2hpoutre Laravel Log Viewer

### Frontend

-   **Framework UI**: Livewire 3.7 (componentes reactivos sin JavaScript)
-   **UI Components**: Livewire Flux 2.9 (sistema de componentes moderno)
-   **CSS Framework**: Tailwind CSS 4.0
-   **Build Tool**: Vite 7.0
-   **JavaScript Libraries**:
    -   Axios 1.11.0 (peticiones HTTP)
    -   Leaflet 1.9.4 (mapas interactivos)
-   **Volt**: Livewire Volt 1.10 (componentes funcionales)

### Integraciones y Servicios Externos

-   **Pagos**: PayPal SDK 3.0 (suscripciones mensuales/anuales)
-   **PDF**: DomPDF 3.1 (generación de informes)
-   **Excel**: Maatwebsite Excel 3.1 (exportación de datos)
-   **Monitoreo**: Sentry Laravel 4.20 (error tracking)
-   **SEO**: Spatie Laravel Sitemap 7.3 (sitemaps dinámicos)
-   **APIs Externas**:
    -   NASA Earthdata API (imágenes satelitales, NDVI)
    -   SIGPAC (Sistema de Información Geográfica de Parcelas Agrícolas)

### Testing

-   **Unit/Feature Tests**: PHPUnit 11.5.3
-   **E2E Tests**: Cypress 15.8.1
-   **Mocking**: Mockery 1.6
-   **Faker**: FakerPHP 1.23

### Desarrollo

-   **Code Style**: Laravel Pint 1.24
-   **Docker**: Docker Compose (MariaDB, MailHog, phpMyAdmin)
-   **Package Manager**: Composer (PHP), NPM (JavaScript)

---

## 🏗️ Arquitectura

### Patrón Arquitectónico

La aplicación sigue una **arquitectura MVC (Model-View-Controller) mejorada** con elementos de **arquitectura por capas**:

1. **Capa de Presentación**: Livewire Components + Blade Templates
2. **Capa de Lógica de Negocio**: Services + Models
3. **Capa de Datos**: Eloquent ORM + Migrations
4. **Capa de Integración**: HTTP Clients + Jobs

### Estructura de Directorios

```
app/
├── Console/          # Comandos Artisan personalizados
├── Enums/            # Enumeraciones del dominio
├── Exports/          # Exportadores de datos (CSV, XML, Excel)
├── Helpers/          # Funciones auxiliares
├── Http/             # Controladores HTTP tradicionales
├── Jobs/             # Jobs de cola asíncronos
├── Livewire/         # Componentes Livewire (122 archivos)
│   ├── Admin/        # Componentes de administración
│   ├── Auth/         # Autenticación
│   ├── Plots/        # Gestión de parcelas
│   ├── Viticulturist/# Dashboard y funcionalidades viticultor
│   └── ...
├── Mail/             # Clases de correo electrónico
├── Models/           # Modelos Eloquent (64 modelos)
├── Notifications/    # Notificaciones del sistema
├── Observers/        # Observadores de modelos
├── Policies/         # Políticas de autorización
├── Providers/        # Service Providers
├── Rules/            # Reglas de validación personalizadas
└── Services/         # Servicios de lógica de negocio
    ├── RemoteSensing/# Servicios de teledetección
    ├── Exporters/     # Exportadores especializados
    └── Validators/    # Validadores de negocio
```

### Modelos de Dominio Principales

-   **User**: Usuarios del sistema (multi-rol)
-   **Plot**: Parcelas agrícolas
-   **Campaign**: Campañas agrícolas (temporadas)
-   **AgriculturalActivity**: Actividades agrícolas (tratamientos, riegos, etc.)
-   **PhytosanitaryTreatment**: Tratamientos fitosanitarios
-   **Harvest**: Cosechas/vendimias
-   **Invoice**: Facturación
-   **Client**: Clientes (bodegas, cooperativas)
-   **Subscription**: Suscripciones de usuarios
-   **Payment**: Pagos realizados
-   **OfficialReport**: Informes oficiales (PAC)
-   **PlotRemoteSensing**: Datos de teledetección por parcela

### Sistema de Roles

La aplicación implementa un **sistema multi-rol** con 4 roles principales:

1. **Admin**: Administración completa del sistema
2. **Supervisor**: Supervisión de múltiples viticultores
3. **Winery**: Bodegas que gestionan viticultores
4. **Viticulturist**: Viticultores (rol principal)

Cada rol tiene su propio dashboard y permisos específicos definidos mediante **Policies**.

---

## 🔄 Flujos Principales

### 1. Autenticación y Autorización

-   **Registro**: Registro con verificación de email
-   **Login**: Autenticación con Laravel Breeze
-   **Cambio de Contraseña Forzado**: Sistema de seguridad para usuarios nuevos
-   **Impersonación**: Admins pueden impersonar usuarios
-   **Middleware**: `auth`, `verified`, `password.changed`

### 2. Gestión de Parcelas

-   Creación/edición de parcelas con geometría SIGPAC
-   Asociación de plantaciones (variedades, sistemas de conducción)
-   Visualización en mapas interactivos (Leaflet)
-   Integración con códigos SIGPAC oficiales

### 3. Cuaderno de Campo Digital

-   Registro de actividades agrícolas:
    -   Tratamientos fitosanitarios
    -   Fertilizaciones
    -   Riegos
    -   Trabajos culturales
    -   Observaciones
-   Validación de períodos de carencia
-   Cumplimiento normativo PAC
-   Generación de informes oficiales

### 4. Teledetección y Análisis

-   Integración con NASA Earthdata API
-   Cálculo de índices NDVI
-   Comparación interanual
-   Alertas de riego
-   Análisis fenológico
-   Historial de imágenes satelitales

### 5. Facturación

-   Gestión de clientes (bodegas, cooperativas)
-   Creación de facturas
-   Agrupación de facturas
-   Exportación a Excel/PDF
-   Trazabilidad completa

### 6. Suscripciones y Pagos

-   Planes: Mensual (8€) y Anual (90€)
-   Integración PayPal (sandbox/producción)
-   Gestión de suscripciones activas
-   Historial de pagos

---

## 🗄️ Base de Datos

### Motor

-   **Producción**: MariaDB 11.8.3
-   **Desarrollo**: MariaDB 11.8.3
-   **Charset**: UTF-8 (utf8mb4_unicode_ci)
-   **Collation**: utf8mb4_unicode_ci
-   **Connection Pooling**: Habilitado
-   **Foreign Key Constraints**: Habilitadas

### Migraciones

-   **Total**: 146 migraciones
-   **Estrategia**: Versionado incremental con timestamps
-   **Foreign Keys**: Habilitadas con `onDelete()` y `onUpdate()` cascades
-   **Índices**: Implementados en campos de búsqueda y relaciones
-   **Soft Deletes**: `deleted_at` en modelos críticos

### Modelos Principales

-   **64 modelos Eloquent**
-   **Relaciones**: BelongsTo, HasMany, HasManyThrough, BelongsToMany, MorphTo, MorphMany
-   **Soft Deletes**: Implementado en modelos críticos (`deleted_at`)
-   **Auditoría**: Modelos de log de auditoría para cambios importantes
    -   `PlotAuditLog`, `InvoiceAuditLog`, `AgriculturalActivityAuditLog`
-   **Factories**: 10 factories para testing
-   **Seeders**: 17 seeders para datos iniciales

---

## 🎨 Frontend y UI/UX

### Componentes Livewire

-   **122 componentes Livewire** organizados por funcionalidad
-   **Concerns/Traits**: Reutilización de lógica común
    -   `WithRoleBasedFields`: Campos según rol
    -   `WithToastNotifications`: Notificaciones toast
    -   `WithUserFilters`: Filtros de usuario
    -   `WithViticulturistValidation`: Validaciones específicas

### Diseño

-   **Tailwind CSS 4.0**: Utility-first CSS
-   **Livewire Flux**: Componentes UI modernos y accesibles
-   **Responsive**: Diseño adaptativo
-   **Dark Mode**: Soporte (si está configurado)

### Interactividad

-   **Livewire**: Reactividad sin JavaScript explícito
-   **Alpine.js**: Incluido con Livewire para interacciones ligeras
-   **Axios**: Peticiones AJAX cuando es necesario
-   **Leaflet**: Mapas interactivos para visualización de parcelas

---

## 🔧 Servicios y Lógica de Negocio

### Servicios Principales

1. **RemoteSensing Services**:

    - `NasaEarthdataService`: Integración con API NASA
    - `NdviCalculator`: Cálculo de índices NDVI
    - `WeatherService`: Datos meteorológicos
    - `PhenologyService`: Análisis fenológico
    - `IrrigationRecommendationService`: Recomendaciones de riego
    - `AlertService`: Sistema de alertas
    - `YearComparisonService`: Comparación interanual

2. **Business Services**:

    - `OfficialReportService`: Generación de informes oficiales
    - `DashboardAlertsService`: Alertas del dashboard
    - `InventoryAnalyticsService`: Análisis de inventario
    - `ViticulturistCacheService`: Cache de datos de viticultores
    - `SitemapService`: Generación de sitemaps dinámicos

3. **Validators**:

    - `PacComplianceValidator`: Validación cumplimiento PAC
    - `PacEligibilityValidator`: Elegibilidad PAC
    - `PlantingRightsValidator`: Validación derechos de plantación
    - `WithdrawalPeriodValidator`: Validación períodos de carencia

4. **Exporters**:
    - `SiexCsvExporter`: Exportación CSV SIEX
    - `SiexXmlExporter`: Exportación XML SIEX

---

## 📡 APIs e Integraciones

### APIs Externas

1. **NASA Earthdata API**

    **Servicio**: `NasaEarthdataService`

    - Obtención de imágenes satelitales (Landsat, Sentinel)
    - Cálculo de índices de vegetación (NDVI)
    - Historial temporal de imágenes
    - Filtrado por coordenadas y fechas
    - Descarga y almacenamiento de imágenes

    **Endpoints utilizados**:

    - Búsqueda de imágenes: `/search`
    - Descarga de datos: `/download`
    - Metadata: `/metadata`

2. **SIGPAC (Sistema de Información Geográfica de Parcelas Agrícolas)**

    **Integración**:

    - Códigos oficiales de parcelas (`SigpacCode` model)
    - Validación de geometrías (GeoJSON)
    - Uso de suelo (`SigpacUse` model)
    - Multipart plots (`MultipartPlotSigpac` model)
    - Visualización en mapas Leaflet

    **Datos técnicos**:

    - Formato: GeoJSON para geometrías
    - Sistema de coordenadas: EPSG:4326 (WGS84)
    - Validación: Verificación de polígonos válidos

3. **PayPal API**

    **SDK**: `srmklive/paypal` 3.0

    - Procesamiento de pagos (sandbox/producción)
    - Gestión de suscripciones recurrentes
    - Webhooks de notificaciones
    - Planes: Mensual (8€), Anual (90€)

    **Configuración**:

    - Modo: `PAYPAL_MODE` (sandbox/live)
    - Credenciales: `PAYPAL_SANDBOX_CLIENT_ID`, `PAYPAL_LIVE_CLIENT_ID`
    - Moneda: EUR
    - Locale: es_ES

### Endpoints Internos

**Health Check**: `/health`

-   Método: GET
-   Autenticación: No requerida
-   Respuesta: JSON con estado de BD y timestamp
-   Códigos: 200 (OK), 503 (Service Unavailable)

**Sitemap**: `/sitemap.xml`

-   Método: GET
-   Autenticación: No requerida
-   Generación: Dinámica vía `SitemapService`
-   Contenido: URLs públicas, páginas SEO, blog

**Verificación de Informes**: `/verify-report/{code}`

-   Método: GET
-   Autenticación: No requerida (público)
-   Parámetro: `code` (verification_code del informe)
-   Respuesta: Vista con detalles del informe verificado

### Integraciones HTTP

**Cliente HTTP**: Guzzle (incluido en Laravel)

-   Timeout configurable
-   Retry logic para APIs externas
-   Error handling y logging

---

## 🧪 Testing

### Estrategia de Testing

-   **Tests Unitarios**: Lógica de negocio con datos históricos (campaña 2024)
-   **Tests E2E (Cypress)**: Flujos completos con datos activos (campaña 2025)

### Configuración

-   **PHPUnit**: Configurado para MariaDB de test
-   **Cypress**: Base de datos separada (`agro365_test`)
-   **Fixtures**: Datos de prueba estructurados
-   **Usuarios de Prueba**: Definidos en seeders

### Cobertura

-   **84 archivos de test** en `tests/`
-   **16 tests E2E** en `cypress/e2e/`
-   Tests organizados por funcionalidad y rol

---

## 🚀 Despliegue y DevOps

### Docker

-   **docker-compose.yml**: Configuración para desarrollo
    -   MariaDB 11.8.3
    -   MailHog (testing de emails)
    -   phpMyAdmin

### Scripts

-   **Composer Scripts**:

    -   `setup`: Instalación inicial completa
    -   `dev`: Desarrollo con hot-reload
    -   `test`: Ejecución de tests

-   **NPM Scripts**:
    -   `dev`: Vite dev server
    -   `build`: Build de producción
    -   `cypress:open`: Cypress interactivo
    -   `cypress:run`: Cypress headless

### Optimizaciones de Producción

-   **Vite Build**:

    -   Code splitting (vendor chunks)
    -   Minificación con Terser
    -   Eliminación de console.log
    -   Sourcemaps desactivados
    -   Assets inline (< 4KB)

-   **Laravel**:
    -   Cache de configuración
    -   Cache de rutas
    -   Cache de vistas
    -   Optimización de autoloader

---

## 🔒 Seguridad

### Implementaciones Técnicas

**Autenticación**:

-   **Framework**: Laravel Breeze 2.3
-   **Driver**: Eloquent User Provider
-   **Password Hashing**: Bcrypt (configurable rounds)
-   **Remember Token**: Tokens encriptados en base de datos
-   **Email Verification**: `MustVerifyEmail` interface
-   **Middleware**: `auth`, `verified`, `password.changed`

**Autorización**:

-   **Policies**: 5 políticas por modelo (`PlotPolicy`, `InvoicePolicy`, etc.)
-   **Gates**: Gates personalizados para operaciones específicas
-   **Role-Based**: Sistema multi-rol con permisos granulares
-   **Middleware**: `can:` para verificación de políticas

**Validación**:

-   **Form Requests**: Validación centralizada
-   **Custom Rules**: 2 reglas personalizadas (`app/Rules/`)
-   **Service Validators**: 4 validadores de negocio
    -   `PacComplianceValidator`
    -   `PacEligibilityValidator`
    -   `PlantingRightsValidator`
    -   `WithdrawalPeriodValidator`

**Auditoría**:

-   **Logs de Auditoría**: 3 modelos de log (`PlotAuditLog`, `InvoiceAuditLog`, `AgriculturalActivityAuditLog`)
-   **Security Logger**: Servicio dedicado (`SecurityLogger`)
-   **Observers**: 8 observadores de modelos para eventos críticos
-   **Sentry**: Integración para tracking de errores en producción

### Características de Seguridad

**Contraseñas**:

-   Cambio forzado para usuarios nuevos (`password_must_reset`)
-   Middleware `password.changed` bloquea acceso hasta cambio
-   Validación de fortaleza de contraseña

**Email**:

-   Verificación obligatoria (`MustVerifyEmail`)
-   Middleware `verified` protege rutas
-   Tokens de verificación con expiración

**Protección CSRF**:

-   Tokens CSRF en todos los formularios
-   Verificación automática en requests POST/PUT/DELETE
-   Excepciones configuradas para APIs externas

**Sanitización**:

-   Eloquent Mass Assignment Protection (`$fillable`, `$guarded`)
-   Validación de tipos y formatos
-   Escapado automático en Blade templates

**Validaciones de Negocio**:

-   Períodos de carencia fitosanitarios
-   Cumplimiento normativo PAC
-   Derechos de plantación
-   Elegibilidad de subvenciones

**Impersonación**:

-   Sistema de impersonación para admins
-   Session flag `impersonating` para bypass de validaciones
-   Logging de acciones durante impersonación

---

## 📊 SEO y Marketing

### Páginas SEO

-   **50+ páginas de contenido SEO** optimizadas
-   **Sitemap dinámico** generado automáticamente
-   **Páginas regionales** por Denominación de Origen
-   **Blog** con contenido técnico
-   **Páginas de servicios** por sector

### Contenido

-   Software para viticultores
-   Software para bodegas
-   Cuaderno de campo digital
-   Trazabilidad agrícola
-   Gestión de vendimia
-   Registro de fitosanitarios
-   Subvenciones PAC

---

## 📈 Escalabilidad y Rendimiento

### Sistema de Cache

**Driver por defecto**: `database` (configurable vía `CACHE_STORE`)

**Estrategias implementadas**:

-   **Cache de Base de Datos**: Tabla `cache` con TTL por clave
-   **Cache de Geometrías**: Parcelas y municipios (TTL: 24 horas)
    -   Clave: `plot_geometries_{plot_id}` o `municipality_geometries_{municipality_id}_user_{user_id}`
-   **Cache de Viticultores**: IDs visibles/editables (TTL: 1 hora)
    -   Patrón de claves: `viticulturist_cache:{type}:{viticulturist_id}:winery_{winery_id}`
-   **Cache de Configuración**: Laravel config cache
-   **Cache de Rutas**: Route cache para producción
-   **Cache de Vistas**: View cache compilado

**Drivers soportados**: `database`, `file`, `redis`, `memcached`, `array`, `dynamodb`, `octane`

**Limpieza de cache**:

-   Manual: `ViticulturistCacheService::clearCache($viticulturistId)`
-   Global: `Cache::flush()` (requiere driver compatible)

### Sistema de Colas (Queue)

**Driver por defecto**: `database` (configurable vía `QUEUE_CONNECTION`)

**Configuración técnica**:

-   **Tabla**: `jobs` (serialización JSON en `payload`)
-   **Retry After**: 90 segundos (configurable vía `DB_QUEUE_RETRY_AFTER`)
-   **After Commit**: `false` (ejecución inmediata)
-   **Failed Jobs**: Tabla `failed_jobs` (driver: `database-uuids`)

**Jobs implementados**:

1. **GenerateOfficialReportJob**:

    - Tries: 3 intentos
    - Timeout: 600 segundos (10 minutos)
    - Backoff: [60, 120, 300] segundos (reintentos escalonados)
    - Procesa: Generación de informes oficiales PAC

2. **UpdatePlotNdviJob**:

    - Procesa: Actualización NDVI de parcela individual
    - Integración: NASA Earthdata API

3. **UpdateAllPlotsNdviJob**:
    - Procesa: Actualización masiva de NDVI
    - Programado: Domingos 06:00 (scheduler)

**Procesamiento de colas**:

-   **Scheduler**: `queue:work --stop-when-empty --max-time=50` cada minuto
-   **Sin solapamiento**: `withoutOverlapping()` previene ejecuciones concurrentes
-   **Background**: `runInBackground()` para no bloquear scheduler

**Monitoreo**:

-   `php artisan queue:monitor`: Estado de colas
-   `php artisan queue:failed`: Jobs fallidos
-   `php artisan queue:restart`: Reinicio graceful

### Optimizaciones de Base de Datos

-   **Lazy Loading**: Carga diferida de relaciones Eloquent
-   **Eager Loading**: `with()`, `load()` para pre-carga de relaciones
-   **Database Indexing**: Índices en campos de búsqueda frecuente
-   **Query Optimization**: Uso de `select()` específicos, `chunk()` para grandes datasets
-   **Connection Pooling**: MariaDB con charset `utf8mb4_unicode_ci`

### Optimizaciones Frontend

-   **Vite Code Splitting**: Separación de vendor chunks (axios, livewire)
-   **Tree Shaking**: Eliminación de código no utilizado
-   **Asset Inlining**: Assets < 4KB inlineados
-   **Minificación**: Terser con eliminación de `console.log`
-   **Sourcemaps**: Desactivados en producción

### Tareas Programadas (Scheduler)

**Configuración**: `routes/console.php`

**Tareas activas**:

1. **Queue Worker**: Cada minuto

    ```php
    Schedule::command('queue:work --stop-when-empty --max-time=50')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
    ```

2. **Limpieza de usuarios no verificados**: Diario 03:00

    ```php
    Schedule::command('users:delete-unverified', ['--hours' => 24])
        ->dailyAt('03:00')
        ->withoutOverlapping()
        ->onOneServer();
    ```

3. **Limpieza de logs**: Diario 02:00

    ```php
    Schedule::command('logs:cleanup')
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->onOneServer();
    ```

4. **Actualización NDVI masiva**: Semanal (domingo 06:00)
    ```php
    Schedule::job(new UpdateAllPlotsNdviJob(), 'remote-sensing')
        ->weeklyOn(0, '06:00')
        ->withoutOverlapping()
        ->onOneServer();
    ```

**Características técnicas**:

-   `withoutOverlapping()`: Previene ejecuciones concurrentes
-   `onOneServer()`: Ejecución única en multi-servidor
-   `runInBackground()`: No bloquea scheduler principal

---

## 🔄 Flujos de Trabajo

### Onboarding

-   Sistema de onboarding progresivo
-   Checklist de pasos esenciales:
    1. Revisar campaña activa
    2. Crear parcela
    3. Añadir productos fitosanitarios
    4. Registrar primera actividad

### Campañas Agrícolas

-   Gestión por temporadas (años)
-   Activación/desactivación de campañas
-   Historial de actividades por campaña
-   Estadísticas y reportes

### Cumplimiento Normativo

-   Validación automática de cumplimiento PAC
-   Generación de informes oficiales
-   Verificación de períodos de carencia
-   Trazabilidad completa de tratamientos

---

## 📝 Documentación Adicional

El proyecto incluye documentación técnica detallada:

-   `BUILD_DEPLOYMENT.md`: Guía de despliegue
-   `DEPLOYMENT.md`: Proceso de despliegue
-   `TESTING_STRATEGY.md`: Estrategia de testing
-   `PAYPAL_SETUP.md`: Configuración de pagos
-   `QUEUE_MANAGEMENT.md`: Gestión de colas
-   `SCHEDULER_SETUP.md`: Configuración de tareas programadas
-   `SECURITY_CONFIG.md`: Configuración de seguridad
-   `IMAGE_OPTIMIZATION.md`: Optimización de imágenes
-   `CYPRESS_SETUP.md`: Configuración de Cypress
-   `CYPRESS_RUN_GUIDE.md`: Guía de ejecución de tests E2E

---

## 🎯 Características Destacadas

1. **Cuaderno de Campo Digital**: Registro completo y normativo de actividades
2. **Teledetección**: Análisis NDVI e imágenes satelitales
3. **Multi-rol**: Sistema flexible para diferentes tipos de usuarios
4. **Facturación Integrada**: Gestión completa de facturación
5. **Cumplimiento PAC**: Validación y generación de informes oficiales
6. **Suscripciones SaaS**: Modelo de negocio con PayPal
7. **Mapas Interactivos**: Visualización SIGPAC con Leaflet
8. **Exportación de Datos**: SIEX, Excel, PDF
9. **Sistema de Alertas**: Notificaciones inteligentes
10. **Onboarding Guiado**: Experiencia de usuario optimizada

---

## 🔮 Tecnologías y Patrones Utilizados

-   **MVC**: Modelo-Vista-Controlador
-   **Repository Pattern**: Implícito en Services
-   **Observer Pattern**: Observadores de modelos
-   **Service Layer**: Lógica de negocio en servicios
-   **Policy Pattern**: Autorización basada en políticas
-   **Factory Pattern**: Factories para testing
-   **Strategy Pattern**: Exportadores y validadores
-   **Dependency Injection**: Container de Laravel
-   **Event-Driven**: Eventos y listeners
-   **Queue Pattern**: Procesamiento asíncrono

---

## 📦 Dependencias Clave

### Producción

-   `laravel/framework`: ^12.0
-   `livewire/livewire`: ^3.7
-   `livewire/flux`: ^2.9
-   `srmklive/paypal`: ^3.0
-   `barryvdh/laravel-dompdf`: ^3.1
-   `maatwebsite/excel`: ^3.1
-   `sentry/sentry-laravel`: ^4.20
-   `spatie/laravel-sitemap`: ^7.3

### Desarrollo

-   `laravel/breeze`: ^2.3
-   `laravel/pint`: ^1.24
-   `phpunit/phpunit`: ^11.5.3
-   `cypress`: ^15.8.1

---

## 🌐 Infraestructura

### Servicios Docker

-   **MariaDB**: Base de datos principal
-   **MailHog**: Servidor SMTP de desarrollo
-   **phpMyAdmin**: Interfaz de administración de BD

### Requisitos del Sistema

-   PHP 8.2+
-   Composer
-   Node.js 18+
-   MariaDB 11.8.3+
-   Extensión PHP: PDO, MBString, XML, GD/Imagick

---

## 📊 Métricas del Proyecto

-   **Modelos**: 64
-   **Componentes Livewire**: 122
-   **Migraciones**: 146
-   **Tests**: 84 archivos PHPUnit + 16 E2E Cypress
-   **Rutas**: 15 archivos de rutas
-   **Servicios**: 22 servicios especializados
-   **Páginas SEO**: 50+ páginas de contenido

---

## 🔧 Configuración Técnica Detallada

### Variables de Entorno Críticas

**Base de Datos**:

-   `DB_CONNECTION`: `mariadb` (producción)
-   `DB_DATABASE`: Nombre de la base de datos
-   `DB_HOST`, `DB_PORT`: Configuración de conexión
-   `DB_CHARSET`: `utf8mb4`

**Cache**:

-   `CACHE_STORE`: `database` (por defecto)
-   `CACHE_PREFIX`: Prefijo de claves de cache

**Colas**:

-   `QUEUE_CONNECTION`: `database` (por defecto)
-   `DB_QUEUE_RETRY_AFTER`: 90 segundos
-   `QUEUE_FAILED_DRIVER`: `database-uuids`

**APIs Externas**:

-   `PAYPAL_MODE`: `sandbox` / `live`
-   `PAYPAL_SANDBOX_CLIENT_ID`, `PAYPAL_SANDBOX_CLIENT_SECRET`
-   `PAYPAL_LIVE_CLIENT_ID`, `PAYPAL_LIVE_CLIENT_SECRET`
-   `NASA_EARTHDATA_API_KEY` (si es requerido)

**Monitoreo**:

-   `SENTRY_LARAVEL_DSN`: DSN de Sentry para error tracking

### Estructura de Archivos de Configuración

-   `config/database.php`: Configuración de conexiones BD
-   `config/cache.php`: Drivers y stores de cache
-   `config/queue.php`: Configuración de colas y jobs
-   `config/auth.php`: Configuración de autenticación
-   `config/mail.php`: Configuración de correo
-   `config/filesystems.php`: Almacenamiento de archivos
-   `vite.config.js`: Configuración de build frontend
-   `cypress.config.js`: Configuración de tests E2E

### Comandos Artisan Personalizados

-   `users:delete-unverified`: Elimina usuarios no verificados
-   `logs:cleanup`: Limpia logs antiguos
-   (Otros comandos personalizados en `app/Console/`)

---

## 🎓 Conclusión

Agro365 es una aplicación web moderna y completa construida con las mejores prácticas de Laravel y Livewire. La arquitectura es escalable, mantenible y sigue principios SOLID. El sistema está diseñado para manejar la complejidad del dominio agrícola/viticultor con múltiples integraciones, validaciones normativas y funcionalidades avanzadas de teledetección.

**Aspectos técnicos destacados**:

-   **Laravel 12** como framework robusto con arquitectura MVC mejorada
-   **Livewire 3** para interactividad reactiva sin JavaScript complejo
-   **Arquitectura por capas** con separación clara de responsabilidades
-   **Sistema de colas** con procesamiento asíncrono y reintentos
-   **Cache estratégico** con múltiples drivers y TTLs optimizados
-   **Testing exhaustivo** con PHPUnit (84 tests) y Cypress (16 E2E)
-   **Integraciones externas** bien estructuradas (NASA, SIGPAC, PayPal)
-   **SEO optimizado** con sitemap dinámico y 50+ páginas de contenido
-   **Seguridad robusta** con policies, validaciones y auditoría
-   **Scheduler configurado** para tareas automáticas y mantenimiento

**Métricas técnicas**:

-   64 modelos Eloquent con relaciones complejas
-   122 componentes Livewire organizados por funcionalidad
-   146 migraciones de base de datos versionadas
-   22 servicios especializados de lógica de negocio
-   15 archivos de rutas con middleware específico
-   3 jobs asíncronos con configuraciones de retry
-   4 tareas programadas en scheduler

---
