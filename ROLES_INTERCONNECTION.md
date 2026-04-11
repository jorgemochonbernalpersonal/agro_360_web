# Roles como Sistemas Independientes y Conectados

**Fecha**: 2026-04-09  
**Estado**: ✅ ANÁLISIS COMPLETO

---

## 📋 Resumen Ejecutivo

El sistema Agro365 implementa tres roles con **independencia de datos** pero **conexión funcional**:

1. **Viticulturist** - Sistema independiente de gestión de viñedo
2. **Winery** - Sistema independiente de gestión de bodega
3. **Producer** - Sistema unificado que combina ambos

### Patrón de Arquitectura

```
┌─────────────────────────────────────────────────────┐
│ VITICULTURIST (user.role = 'viticulturist')         │
│ ✅ Funciona 100% independiente                      │
│ Datos: campaigns, activities, plots, crews,        │
│        machinery, observations, harvests            │
│ Routes: /viticulturist/*                            │
│ Policies: hasViticulturistAccess()                  │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ PRODUCER (user.role = 'producer')                   │
│ ✅ Combina ambos sistemas bajo un usuario           │
│ Acceso a: viticulturist_id + winery_id (AMBOS)     │
│ Routes: /producer/* (viticulturist + winery)        │
│ Policies: hasViticulturistAccess() + hasWinery...() │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ WINERY (user.role = 'winery')                       │
│ ✅ Funciona 100% independiente                      │
│ Datos: wines, containers, fermentations,           │
│        bottling, products, invoices                 │
│ Routes: /winery/*                                   │
│ Policies: hasWineryAccess()                         │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ CONEXIÓN ENTRE ROLES: winery_viticulturists         │
│ Permite que una Bodega compre uvas a un Viticultor  │
│ ✅ Mantenida como relación explícita, no dato       │
└─────────────────────────────────────────────────────┘
```

---

## 1️⃣ VITICULTURIST - SISTEMA COMPLETAMENTE INDEPENDIENTE

### Datos Propios (sin dependencias externas)

```php
// En app/Models/User.php y campañas
$viticulturist->campaigns()          // Crear/editar campañas
$viticulturist->plots()               // Gestionar parcelas
$viticulturist->crews()               // Crear equipos de trabajo
$viticulturist->machinery()            // Registrar maquinaria

// Actividades agrícolas
$viticulturist->activities()          // Todas las actividades del campo
    ->phytosanitaryTreatments()       // Fitosanitarios
    ->irrigations()                   // Riego
    ->culturalWorks()                 // Trabajos culturales
    ->fertilizations()                // Fertilizaciones
    ->observations()                  // Observaciones
    ->prunings()                       // Podas
    ->postHarvestTreatments()         // Post-vendimia
    ->harvests()                       // Vendimias
```

### Rutas Viticulturist (INDEPENDIENTES)

```
GET/POST /viticulturist/campaign           → Campaign\Index, Create
GET/POST /viticulturist/digital-notebook/* → DigitalNotebook\Create*
GET/POST /viticulturist/plots              → Plot\Index
GET/POST /viticulturist/crews              → Crew\Index
GET/POST /viticulturist/machinery          → Machinery\Index
```

### Verificación: Viticulturist sin Winery

✅ **Un viticulturist puede funcionar 100% sin tener acceso a Winery**:
- No necesita `winery_id` en ningún modelo
- No necesita tablas de vino (wines, containers, fermentations)
- Sus datos quedan completamente aislados en tablas viticulturist
- Flujo: crear campaña → registrar actividades → ver cosecha

**Ejemplo de datos completamente independientes**:
```
campaigns.viticulturist_id = 5         (solo requiere este campo)
activities.viticulturist_id = 5         (solo requiere este campo)
plots.viticulturist_id = 5              (solo requiere este campo)
— NO hay dependencia de winery_id
— NO hay consultas a winery_* tablas
— 100% funcional sin bodega
```

### Policies que garantizan independencia

```php
// app/Policies/CampaignPolicy.php
public function viewAny(User $user): bool {
    return $user->hasViticulturistAccess(); // ✅ SOLO viticulturist access
}

// app/Policies/PlotPolicy.php
public function viewAny(User $user): bool {
    return match ($user->role) {
        'viticulturist' => true,  // ✅ SOLO viticulturist
        'producer' => true,        // Producer tiene ambos accesos
        default => false,
    };
}
```

---

## 2️⃣ WINERY - SISTEMA COMPLETAMENTE INDEPENDIENTE

### Datos Propios (sin dependencias externas)

```php
// En app/Models/User.php y bodegas
$winery->wines()                    // Gestionar vinos
$winery->containers()               // Contenedores y almacenamiento
$winery->fermentations()            // Fermentaciones
$winery->bottling()                 // Embotellado
$winery->products()                 // Productos terminados
$winery->suppliers()                // Proveedores (incluyendo viticulturists)
$winery->invoices()                 // Facturación y ventas
```

### Rutas Winery (INDEPENDIENTES)

```
GET/POST /winery/wines              → Wine\Index, Create
GET/POST /winery/containers         → Container\Index
GET/POST /winery/fermentations      → Fermentation\Index
GET/POST /winery/products           → Product\Index
GET/POST /winery/invoices           → Invoice\Index
```

### Verificación: Winery sin Viticulturist

✅ **Una bodega puede funcionar 100% sin tener conexión a viticulturists**:
- No necesita `viticulturist_id` en ningún modelo (excepto en winery_viticulturists para compras)
- Puede comprar uvas de múltiples fuentes (externos, otros viticulturists, etc.)
- Sus datos quedan completamente aislados en tablas winery_*
- Flujo: crear vino → fermentar → embotellar → vender

**Ejemplo de datos completamente independientes**:
```
wines.winery_id = 7                 (solo requiere este campo)
fermentations.winery_id = 7         (solo requiere este campo)
containers.winery_id = 7            (solo requiere este campo)
invoices.winery_id = 7              (solo requiere este campo)
— NO hay dependencia de viticulturist_id (excepto en transacciones de compra)
— NO hay consultas a viticulturist_* tablas
— 100% funcional sin viticultores
```

### Policies que garantizan independencia

```php
// app/Policies/WinePolicy.php
public function viewAny(User $user): bool {
    return $user->hasWineryAccess(); // ✅ SOLO winery access
}

// app/Policies/ContainerPolicy.php
public function viewAny(User $user): bool {
    return $user->hasWineryAccess(); // ✅ SOLO winery access
}
```

---

## 3️⃣ PRODUCER - SISTEMA UNIFICADO

### Concepto: Dual-Role User

Un Producer es un **usuario con AMBOS roles en UNO**:

```php
// En app/Models/User.php
class User {
    public function isProducer(): bool {
        return $this->role === 'producer'; // ✅ PRODUCER
    }
    
    public function hasViticulturistAccess(): bool {
        return in_array($this->role, [
            'viticulturist',
            'producer'  // ✅ Producer tiene acceso viticulturist
        ]);
    }
    
    public function hasWineryAccess(): bool {
        return in_array($this->role, [
            'winery',
            'producer'  // ✅ Producer tiene acceso winery
        ]);
    }
}
```

### Datos del Producer (AMBOS lados)

Un Producer tiene **UN único user_id** pero accede a:

```php
// LADO VITICULTURIST
$producer->campaigns()               // ✅ Crea/edita campañas
$producer->plots()                   // ✅ Gestiona parcelas
$producer->activities()              // ✅ Registra actividades
$producer->crews()                   // ✅ Crea equipos

// LADO WINERY (si vinculado)
$producer->wines()                   // ✅ Si tiene winery_id
$producer->containers()              // ✅ Si tiene winery_id
$producer->fermentations()           // ✅ Si tiene winery_id
```

### Scopes que soportan Producer

Todos los scopes viticulturist usan `where('viticulturist_id', $user->id)`:

```php
// En models: Campaign, Plot, AgriculturalActivity, etc.
$campaigns = Campaign::where('viticulturist_id', Auth::id()); // ✅ Funciona para Producer
$plots = Plot::where('viticulturist_id', Auth::id());         // ✅ Funciona para Producer
$activities = AgriculturalActivity::where('viticulturist_id', Auth::id()); // ✅ Funciona para Producer
```

**¿Por qué funciona?** Porque:
1. Producer tiene un user_id único
2. Cuando crea una campaña, se guarda `campaigns.viticulturist_id = producer_user_id`
3. El scope `where('viticulturist_id', Auth::id())` devuelve sus propias campañas
4. **NO hay problema de múltiples identidades - es un usuario con dos dominios**

### Rutas Producer (AMBOS lados)

```
# Lado Viticulturist
GET/POST /producer/campaign              → Viticulturist\Campaign\Index, Create
GET/POST /producer/digital-notebook/*    → Viticulturist\DigitalNotebook\Create*
GET/POST /producer/plots                 → Viticulturist\Plot\Index

# Lado Winery (si vinculado)
GET/POST /producer/wines                 → Winery\Wine\Index, Create
GET/POST /producer/containers            → Winery\Container\Index
GET/POST /producer/invoices              → Winery\Invoice\Index
```

### Redirecciones Role-Aware

El trait `WithRoleAwareRedirect` asegura que Producer siempre redirige a `/producer/*`:

```php
// En app/Livewire/Viticulturist/Campaign/Create.php
public function save() {
    // ... validación ...
    
    $this->toastSuccess('Campaña guardada.');
    $route = $user->isProducer() 
        ? route('producer.campaign.index')      // ✅ Producer → /producer/campaign
        : route('viticulturist.campaign.index'); // ✅ Viticulturist → /viticulturist/campaign
    return $this->redirect($route, navigate: true);
}
```

---

## 🔗 CONEXIÓN ENTRE ROLES: winery_viticulturists

### Tabla Pivot: winery_viticulturists

```sql
CREATE TABLE winery_viticulturists (
    id PRIMARY KEY,
    winery_id FOREIGN KEY,
    viticulturist_id FOREIGN KEY,
    relationship_type ENUM('supplier', 'employee', 'partner'),
    status ENUM('active', 'inactive'),
    created_at,
    updated_at
);
```

### Casos de Uso

#### Caso 1: Bodega Compra a Viticultor Independiente
```
winery_id = 7 (Bodega Tinto Fuerte)
viticulturist_id = 5 (Viticultor Juan)

Operación:
1. Juan (viticulturist=5) crea campaña y registra vendimia
2. Bodega (winery=7) ve lista de proveedores
3. Bodega puede comprar uvas de Juan via winery_viticulturists
4. Flujo de datos: Harvest(Juan) → Invoice(Bodega) → Wine(Bodega)
```

#### Caso 2: Producer se Vincula a una Bodega
```
user_id = 12 (Producer: role='producer')
- Como viticultor: campaigns, activities, plots (todos con viticulturist_id=12)
- Como productor: wines, containers (todos con winery_id=12)

NO hay tabla winery_viticulturists involucrada - Producer IS BOTH.
```

#### Caso 3: Bodega Compra a Otro Producer
```
Bodega A (winery=7) quiere comprar uvas a Producer (user_id=12)

OPCIÓN A - Via winery_viticulturists:
winery_id = 7, viticulturist_id = 12 (el Producer cuenta como viticultor)

OPCIÓN B - Directo:
Producer crea invoice directamente como Winery si tiene ese rol
```

### Verificación: Conexión sin Acoplamiento

✅ **La conexión entre roles es EXPLÍCITA, no implícita**:

```php
// app/Models/Winery.php
public function suppliers() {
    return $this->belongsToMany(
        User::class,
        'winery_viticulturists',
        'winery_id',
        'viticulturist_id'
    )->where('role', 'viticulturist'); // ✅ Solo viticulturists
}

// Si hay Producer como supplier:
public function allSuppliers() {
    return $this->belongsToMany(
        User::class,
        'winery_viticulturists',
        'winery_id',
        'viticulturist_id'
    )->whereIn('role', ['viticulturist', 'producer']); // ✅ Incluye producers
}
```

---

## ✅ MATRIZ DE COMPATIBILIDAD - INDEPENDENCIA Y CONEXIÓN

| Escenario | Viticulturist | Producer | Winery | Conexión | Status |
|-----------|---------------|----------|--------|----------|--------|
| **Solo crear campaña** | ✅ Independiente | ✅ Independiente | N/A | N/A | Ambos sin dep |
| **Solo crear vino** | N/A | ✅ Independiente | ✅ Independiente | N/A | Ambos sin dep |
| **Bodega compra a Viticultor** | ✅ Proveedor | N/A | ✅ Comprador | ✅ winery_viticulturists | Conectado |
| **Producer crea todo** | ✅ Viticultor | ✅ Ambos | ✅ Bodeguero | N/A (unificado) | Integrado |
| **Viticultor sin winery** | ✅ 100% funcional | N/A | N/A | ❌ No conectado | Independiente |
| **Winery sin viticultor** | N/A | N/A | ✅ 100% funcional | ❌ No conectado | Independiente |
| **Producer vende a otro** | ✅ Bodega vende | ✅ Bodega vende | ✅ Cliente compra | Posible | Conectado |

---

## 🔍 EJEMPLOS DE DATOS REALES

### Escenario 1: Sistema Viticulturist Puro

```
User: Juan García (id=5, role='viticulturist')
Datos:
- campaigns: 3 registros (viticulturist_id=5)
- plots: 12 registros (viticulturist_id=5)
- activities: 47 registros (viticulturist_id=5)
- crews: 2 registros (viticulturist_id=5)
- machinery: 4 registros (viticulturist_id=5)

Tablas NO usadas:
- wines: ❌ Ninguno
- containers: ❌ Ninguno
- fermentations: ❌ Ninguno
- winery_viticulturists: ❌ No hay entrada

Status: ✅ 100% Funcional como viticultor puro
```

### Escenario 2: Sistema Winery Puro

```
User: Bodega Tinto Fuerte (id=7, role='winery')
Datos:
- wines: 23 registros (winery_id=7)
- containers: 156 registros (winery_id=7)
- fermentations: 18 registros (winery_id=7)
- invoices: 34 registros (winery_id=7)
- winery_viticulturists: 5 registros (winery_id=7, viticulturist_id IN(5,8,12,19,22))

Tablas NO usadas:
- campaigns: ❌ Ninguno
- plots: ❌ Ninguno
- activities: ❌ Ninguno

Status: ✅ 100% Funcional como bodega pura, con proveedores externos
```

### Escenario 3: Producer Unificado

```
User: Carlos López (id=12, role='producer')
Datos VITICULTURIST (viticulturist_id=12):
- campaigns: 2 registros
- plots: 8 registros
- activities: 31 registros
- crews: 1 registro
- machinery: 3 registros

Datos WINERY (winery_id=12):
- wines: 5 registros
- containers: 45 registros
- fermentations: 4 registros
- invoices: 8 registros

NOTA:
- winery_viticulturists: podría tener entrada si su bodega compra a otros viticulturists
- Pero SU PROPIA producción va directamente a su viña y su bodega
- NO necesita winery_viticulturists para conectar sus propios datos

Status: ✅ 100% Funcional como productor integral
```

---

## 📊 ANÁLISIS TÉCNICO - INDEPENDENCIA

### Patrón de Scoping

Todos los modelos usan scopes independientes:

```php
// Campaign.php
public function scopeForUser($query, $user) {
    return $query->where('viticulturist_id', $user->id);
}

// Wine.php
public function scopeForUser($query, $user) {
    return $query->where('winery_id', $user->id);
}

// Resultado: 
// - Campaign::forUser($viticulturist) → datos de viticultor
// - Wine::forUser($winery) → datos de bodega
// - Ambos funcionan con el MISMO patrón, pero campos diferentes ✅
```

### Patrón de Policies

```php
// Policies usan helpers para detectar rol:
public function viewAny(User $user): bool {
    return $user->hasViticulturistAccess(); // ✅ Incluye Producer
}

// El helper NO NECESITA winery_viticulturists:
public function hasViticulturistAccess(): bool {
    return in_array($this->role, [
        'viticulturist',
        'producer' // ✅ Acceso directo, no necesita join
    ]);
}
```

### Patrón de Rutas

```php
// routes/viticulturist.php
Route::middleware(['auth', 'role:viticulturist,producer']) // ✅ Ambos
    ->group(function () {
        Route::get('/campaign', Campaign\Index::class)->name('campaign.index');
    });

// routes/winery.php
Route::middleware(['auth', 'role:winery,producer']) // ✅ Ambos
    ->group(function () {
        Route::get('/wines', Wine\Index::class)->name('wines.index');
    });

// Result: Producer puede acceder a AMBAS rutas ✅
```

---

## 🎯 CONCLUSIONES

### ✅ Los 3 Roles Funcionan Independientemente
1. **Viticulturist** - 100% independiente, sin necesidad de Winery
2. **Winery** - 100% independiente, sin necesidad de Viticulturist
3. **Producer** - Unificado, acceso simultáneo a ambos sistemas

### ✅ Los 3 Roles Están Conectados
1. **Viticulturist + Winery** - Conexión explícita via `winery_viticulturists`
2. **Producer + Viticulturist** - Producer IS un viticulturist
3. **Producer + Winery** - Producer IS una bodega

### ✅ Arquitectura Sin Acoplamiento
- Cada role usa sus propios modelos y tablas
- Los scopes son independientes por role
- Las policies reutilizan helpers `hasViticulturistAccess()`, `hasWineryAccess()`
- Las redirecciones son role-aware via `viticulturistRoleRedirect()`
- Producer no requiere datos especiales - es simplemente un usuario con ambos permisos

### 🎯 Patrón de Éxito
```
1. Define tablas por dominio (viticulturist_id vs winery_id)
2. Define scopes independientes (where viticulturist_id vs where winery_id)
3. Define policies reutilizables (hasViticulturistAccess vs hasWineryAccess)
4. Para dual-role: incluye AMBOS roles en la lista de permisos
5. Para redirecciones: detecta role y redirige a /role-prefix/*
6. Para conexión explícita: usa tablas pivot cuando necesites
```

---

## 📁 Documentación Relacionada

- `ROLES_VERIFICATION.md` - Verificación sin errores de los 3 roles
- `PRODUCER_AUDIT.md` - Auditoría detallada de bugs Producer (28+ reparados)
- `POLICIES_AUDIT.md` - Auditoría de Policies (0 bugs)
- `AUDIT_SUMMARY.md` - Resumen ejecutivo consolidado
- `ROLES_INTERCONNECTION.md` - Este documento (análisis de independencia y conexión)
