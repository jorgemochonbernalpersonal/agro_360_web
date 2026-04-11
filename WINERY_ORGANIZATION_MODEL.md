# Modelo Organizacional: Winery No Independiente

**Fecha**: 2026-04-09  
**Estado**: ✅ ANÁLISIS COMPLETO

---

## 📋 Resumen Ejecutivo

Agro365 implementa un modelo SaaS de **organización alrededor de la bodega**:

```
┌─────────────────────────────────────────────┐
│ WINERY (user.id = bodega)                   │
│ Es la "ORGANIZACIÓN" del sistema             │
│ ✅ Paga la suscripción                       │
│ ✅ Invita viticulturists                     │
│ ✅ Acceso: admin de su organización          │
└─────────────────────────────────────────────┘
        ↓
  (invitación + link)
        ↓
┌─────────────────────────────────────────────┐
│ VITICULTURIST INVITADO (user.id = viti)     │
│ Es un "MIEMBRO" de la organización bodega    │
│ ✅ Aceptó invitación                         │
│ ✅ Se registró en el sistema                 │
│ ✅ Puede usar FREE o PAGO (heredado)         │
│ ✅ Acceso: miembro de la bodega              │
└─────────────────────────────────────────────┘
```

---

## 1️⃣ CREACIÓN DE VITICULTOR FANTASMA (Ghost User)

### Qué es un "Ghost User"?

Un **ghost user** es un registro de usuario con `can_login=false`:
- No puede acceder al sistema
- No tiene contraseña válida (hash aleatorio)
- Tiene email fantasma: `viticultores.{uuid}@noemail.agro365.es`
- Creado por la bodega como "invitación pre-registrada"

### Flujo de Creación

**Archivo**: `app/Livewire/Winery/Viticulturists/Create.php`

```php
public function performCreate(): void
{
    // Paso 1: Bodega rellena formulario
    $user = User::create([
        'name'      => 'Juan García',                    // Nombre del viticultor
        'email'     => $email ?: ('viticultores.' . Str::uuid() . '@noemail.agro365.es'),
        'dni'       => '12345678X',                      // DNI (opcional)
        'role'      => 'viticulturist',                  // ✅ Es viticultor
        'can_login' => false,                            // ❌ NO puede entrar
        'password'  => Hash::make(Str::random(40)),      // Password aleatorio
    ]);

    // Paso 2: Crear relación con bodega
    WineryViticulturist::create([
        'winery_id'        => $bodega_id,
        'viticulturist_id' => $user->id,
        'source'           => 'own',                     // Bodega lo creó
        'assigned_by'      => $bodega_id,
        'notes'            => 'Proveedor principal',     // Notas internas
    ]);

    // Paso 3: Heredar acceso beta de bodega
    $winery = User::find($bodega_id);
    if ($winery->isBetaUser() && !$winery->betaExpired()) {
        $user->grantBetaAccess($winery->beta_ends_at);  // Mismo plan que bodega
    }
}
```

### Estado del Ghost

```
┌──────────────────────────────────────────────┐
│ Usuario "Juan García" (Ghost)                │
├──────────────────────────────────────────────┤
│ id: 42                                       │
│ name: "Juan García"                          │
│ email: "viticultores.abc123@noemail..."      │
│ role: "viticulturist"                        │
│ can_login: false                 ← ¡NO PUEDE ENTRAR!
│ password: Hash(random_40_chars)               │
│ invitation_token: null          (aún no invitado)
│ invitation_sent_at: null                      │
│ invitation_expires_at: null                   │
│ is_beta_user: true (heredado)                 │
│ beta_ends_at: 2026-05-09 (heredado)          │
└──────────────────────────────────────────────┘

┌──────────────────────────────────────────────┐
│ Relación WineryViticulturist                 │
├──────────────────────────────────────────────┤
│ winery_id: 5 (Bodega Tinto Fuerte)           │
│ viticulturist_id: 42 (Juan García)           │
│ source: "own" (bodega lo creó)               │
│ assigned_by: 5                                │
│ cuaderno_access: false (no otorgado aún)     │
│ notes: "Proveedor principal"                  │
│ created_at: 2026-04-09 10:30                 │
└──────────────────────────────────────────────┘
```

---

## 2️⃣ ENVÍO DE INVITACIÓN

### Flujo de Invitación

**Archivo**: `app/Livewire/Winery/Viticulturists/Show.php`

```php
public function sendInvitation(): void
{
    // Paso 1: Bodega rellena email real
    $email = "juan@bodega-partner.es";
    
    // Paso 2: Validaciones
    // - Rate limit: máximo 1 invitación por hora
    // - Email no puede estar en otro usuario
    
    // Paso 3: Generar token
    $token = Str::random(64);  // Token seguro de 64 caracteres
    
    // Paso 4: Actualizar usuario ghost
    $viticulturist->update([
        'invitation_token'      => $token,
        'invitation_sent_at'    => now(),
        'invitation_expires_at' => now()->addDays(7),  // Válido 7 días
        'email'                 => $email,  // Actualizar email si era fantasma
    ]);
    
    // Paso 5: Enviar email con link de aceptación
    $viticulturist->notify(new ViticulturistInvitationNotification(
        $bodega,      // Quién invita
        $token        // Link: /claim-account/{token}
    ));
}
```

### Email de Invitación

```
Asunto: "Bodega Tinto Fuerte te invita a Agro365"

Hola Juan,

Bodega Tinto Fuerte te ha invitado a usar Agro365 para gestionar 
tus viñedos y coordinar con ellos.

Link de aceptación: https://agro365.app/claim-account/{token}

Este link expira en 7 días (2026-04-16).
```

### Estado del Usuario Invitado

```
┌──────────────────────────────────────────────┐
│ Usuario "Juan García" (Invitado)             │
├──────────────────────────────────────────────┤
│ id: 42                                       │
│ name: "Juan García"                          │
│ email: "juan@bodega-partner.es"    ← Actualizado
│ role: "viticulturist"                        │
│ can_login: false              ← Aún no puede entrar
│ password: Hash(random_40_chars)               │
│ invitation_token: "abc123..."       ← Token activo
│ invitation_sent_at: 2026-04-09 10:45         │
│ invitation_expires_at: 2026-04-16 10:45      │ ← Válido 7 días
│ is_beta_user: true                           │
│ beta_ends_at: 2026-05-09                     │
└──────────────────────────────────────────────┘
```

---

## 3️⃣ ACEPTACIÓN DE INVITACIÓN (Claim Account)

### Flujo de Aceptación

**Archivo**: `app/Livewire/Auth/ClaimAccount.php`

```php
public function mount(string $token): void
{
    // Paso 1: Validar token
    $user = User::where('invitation_token', $token)
        ->where('can_login', false)
        ->first();
    
    if (!$user || $user->invitation_expires_at->isPast()) {
        $this->tokenValid = false;  // Token inválido o expirado
        return;
    }
    
    // Paso 2: Pre-cargar datos
    $this->tokenValid = true;
    $this->name = $user->name;
    $this->wineryName = $user->winery->name;  // Mostrar quién invitó
}

public function activate(): void
{
    // Paso 1: Viticultor rellena formulario
    // - Nombre (puede cambiar)
    // - Email real (validar único)
    // - Contraseña (mínimo 8 caracteres)
    // - Checkbox: "Compartir cuaderno con bodega"
    
    // Paso 2: Validar
    $this->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);
    
    // Paso 3: Activar cuenta
    $user->update([
        'name'                   => $this->name,
        'email'                  => $this->email,
        'password'               => Hash::make($this->password),
        'can_login'              => true,              // ✅ AHORA SÍ PUEDE ENTRAR
        'email_verified_at'      => now(),             // Email verificado
        'invitation_token'       => null,              // Limpiar token
        'invitation_expires_at'  => null,
        'invitation_sent_at'     => null,
    ]);
    
    // Paso 4: Otorgar acceso al cuaderno si lo aceptó
    if ($this->shareCuaderno) {
        WineryViticulturist::where('viticulturist_id', $user->id)
            ->whereNotNull('winery_id')
            ->update([
                'cuaderno_access'     => true,         // ✅ Bodega ve cuaderno
                'cuaderno_granted_at' => now(),
            ]);
    }
    
    // Paso 5: Auto-login
    Auth::login($user->fresh());
    
    // Paso 6: Redirigir a dashboard
    $this->redirectRoute('viticulturist.dashboard');
}
```

### Estado del Usuario Activado

```
┌──────────────────────────────────────────────┐
│ Usuario "Juan García" (ACTIVO)               │
├──────────────────────────────────────────────┤
│ id: 42                                       │
│ name: "Juan García"                          │
│ email: "juan@bodega-partner.es"              │
│ role: "viticulturist"                        │
│ can_login: true                   ✅ PUEDE ENTRAR
│ password: Hash(nueva_contraseña)             │
│ email_verified_at: 2026-04-09 11:00          │
│ invitation_token: null            (limpiado)
│ invitation_expires_at: null       (limpiado)
│ invitation_sent_at: null          (limpiado)
│ is_beta_user: true                (heredado)
│ beta_ends_at: 2026-05-09          (heredado)
└──────────────────────────────────────────────┘

┌──────────────────────────────────────────────┐
│ Relación WineryViticulturist (ACTUALIZADA)   │
├──────────────────────────────────────────────┤
│ winery_id: 5                                 │
│ viticulturist_id: 42                         │
│ source: "own"                                │
│ cuaderno_access: true              ✅ OTORGADO
│ cuaderno_granted_at: 2026-04-09 11:00        │
└──────────────────────────────────────────────┘
```

---

## 4️⃣ PLAN DE SUSCRIPCIÓN (FREE vs PAGO)

### Herencia de Plan

Cuando la bodega crea viticulturists, **heredan automáticamente el plan de la bodega**:

```php
// En Create.php línea 63-67
$winery = User::find($bodega_id);
if ($winery->isBetaUser() && !$winery->betaExpired()) {
    $user->grantBetaAccess($winery->beta_ends_at);  // ✅ Mismo plan
}
```

### Campos de Plan

En `users` table:

```
is_beta_user    → boolean
beta_ends_at    → timestamp nullable
```

### Escenarios de Plan

#### Escenario 1: Bodega tiene FREE
```
Bodega:
- is_beta_user = false
- beta_ends_at = null

Viticultor invitado:
- is_beta_user = false
- beta_ends_at = null

→ Ambos usan la versión FREE del sistema
```

#### Escenario 2: Bodega tiene PAGO (beta activo)
```
Bodega:
- is_beta_user = true
- beta_ends_at = 2026-06-09

Viticultor invitado (creado durante periodo beta):
- is_beta_user = true
- beta_ends_at = 2026-06-09    ← HEREDADO

→ Viticultor tiene acceso a features PAGO
→ Pero SOLO mientras la bodega pague
```

#### Escenario 3: Plan de bodega expira
```
Bodega:
- is_beta_user = true
- beta_ends_at = 2026-04-05 (PASADO)

Viticultor:
- is_beta_user = true
- beta_ends_at = 2026-04-05 (PASADO)

→ betaExpired() retorna true
→ Acceso a features PAGO se revoca
→ Ambos regresan a FREE
```

### Acceso a Features Pago

En componentes Livewire:

```php
// En Winery/FieldActivities/Index.php línea 39
if (!$this->winery->isBetaUser() || $this->winery->betaExpired()) {
    // Mostrar mensaje: "Actualiza a pago"
    abort(403, 'Requiere acceso pago');
}
```

---

## 5️⃣ ACCESO AL CUADERNO DE CAMPO (Notebook)

### Dos Niveles de Acceso

#### Nivel 1: Viticultor crea cuaderno
```
Viticultor Juan registra actividades:
- Campañas: campaigns.viticulturist_id = 42
- Actividades: activities.viticulturist_id = 42
- Cuaderno: digital_notebook.*

→ Juan siempre ve SUS PROPIOS datos
```

#### Nivel 2: Bodega solicita acceso
```
Flujo:
1. Bodega abre perfil de viticultor
2. Bodega ve: "Solicitar acceso al cuaderno"
3. Bodega enía solicitud → NotebookAccessRequest
4. Viticultor recibe notificación
5. Viticultor aprueba o rechaza
6. Si aprueba: WineryViticulturist.cuaderno_access = true
```

### Control de Acceso

**Archivo**: `app/Livewire/Winery/FieldActivities/Index.php:39`

```php
// Ghost viticulturists (can_login=false) cannot fill their own notebook,
// but wineries can see their data if cuaderno_access=true
if ($user->can_login === false && !$relation->cuaderno_access) {
    $this->toastWarning('El viticultor no ha aceptado compartir su cuaderno.');
    abort(403);
}
```

**Estados de Acceso**:

```
┌────────────────────────────────────────────────────┐
│ 1. Ghost (invitación pendiente)                    │
│    - can_login = false                             │
│    - cuaderno_access = false                       │
│    → Bodega NO ve cuaderno                         │
├────────────────────────────────────────────────────┤
│ 2. Activado (sin compartir cuaderno)               │
│    - can_login = true                              │
│    - cuaderno_access = false                       │
│    → Bodega NO ve cuaderno                         │
├────────────────────────────────────────────────────┤
│ 3. Activado + Compartiendo cuaderno                │
│    - can_login = true                              │
│    - cuaderno_access = true                        │
│    → Bodega VE CUADERNO (pero no edita)            │
└────────────────────────────────────────────────────┘
```

### Auto-otorgar en Registro

**Opción**: Viticultor marca checkbox al registrarse

```php
// En ClaimAccount.php línea 101-107
public bool $shareCuaderno = true;  // Por defecto SÍ

if ($this->shareCuaderno) {
    WineryViticulturist::where('viticulturist_id', $user->id)
        ->whereNotNull('winery_id')
        ->update([
            'cuaderno_access'     => true,
            'cuaderno_granted_at' => now(),
        ]);
}
```

---

## 6️⃣ DATOS COMPARTIDOS EN RELACIÓN winery_viticulturists

```sql
CREATE TABLE winery_viticulturists (
    id PRIMARY KEY,
    winery_id FOREIGN KEY,
    viticulturist_id FOREIGN KEY,
    
    -- Relación
    relationship_type ENUM('supplier', 'employee', 'partner') nullable,
    status ENUM('active', 'inactive') default 'active',
    
    -- Origen de la invitación
    source ENUM('own', 'supervisor') default 'own',
    supervisor_id FOREIGN KEY nullable,  -- Si source=supervisor
    assigned_by USER_ID,                 -- Quién asignó
    
    -- Acceso al cuaderno
    cuaderno_access BOOLEAN default false,
    cuaderno_granted_at TIMESTAMP nullable,
    
    -- Notas internas de la bodega
    notes TEXT nullable,
    
    created_at, updated_at
);
```

---

## 7️⃣ ROLES Y PERMISOS EN MODELO ORGANIZACIONAL

### Viticultor Invitado (can_login=true)

**Acceso a su propio contenido**:
- ✅ Ver/editar sus campañas
- ✅ Registrar/editar actividades
- ✅ Ver sus parcelas
- ✅ Crear cuadrillas y maquinaria

**Acceso a contenido de bodega (si cuaderno_access=true)**:
- ✅ Ver datos de bodega en dashboard
- ❌ NO puede editar datos de bodega

**Acceso a invitaciones**:
- ✅ Ver solicitudes de acceso al cuaderno
- ✅ Aprobar/rechazar solicitudes

### Bodega (winery)

**Acceso a su contenido**:
- ✅ Ver/editar sus vinos
- ✅ Gestionar contenedores
- ✅ Ver facturación

**Acceso a viticulturists invitados**:
- ✅ Ver lista de viticulturists
- ✅ Invitar nuevos viticulturists
- ✅ Ver datos de viticulturist (si cuaderno_access=true)
- ✅ Enviar solicitudes de acceso al cuaderno
- ❌ NO puede editar cuaderno de viticultor

---

## 8️⃣ LIMPIEZA DE USUARIOS FANTASMA

### Comando: Clean Stale Invitations

```bash
php artisan viticulturists:clean-stale-invitations
```

Limpia tokens caducados:

```php
// En CleanStaleInvitations.php
User::where('can_login', false)
    ->where('invitation_sent_at', '<', now()->subDays(7))
    ->update([
        'invitation_token'      => null,
        'invitation_expires_at' => null,
        'invitation_sent_at'    => null,
    ]);
```

### Comando: Purge Abandoned Ghosts

```bash
php artisan viticulturists:purge-abandoned-ghosts --dry-run
php artisan viticulturists:purge-abandoned-ghosts
```

Elimina ghosts que nunca fueron invitados ni usados:

```php
// En PurgeAbandonedGhosts.php
$abandoned = User::where('can_login', false)
    ->where('created_at', '<', now()->subDays(30))  // Creado hace 30+ días
    ->where('invitation_sent_at', null)             // Nunca invitado
    ->doesntHave('agriculturalActivities')          // Sin actividades
    ->get();

// Categoría: "safe" (sin datos → pueden eliminarse)
// Categoría: "blocked" (con datos → no pueden eliminarse)
```

---

## 🎯 FLUJO COMPLETO: ESCENARIO REAL

### Ejemplo: Bodega "Tinto Fuerte" invita a viticultor "Juan García"

#### Día 1 - Bodega invita (10:30)

**Bodega accede a `/winery/viticulturists/create`**:
```
Formulario:
- Nombre: "Juan García"
- DNI: "12345678X"
- Email: (vacío, se genera fantasma)
- Teléfono: "666123456"
- Notas: "Proveedor principal de Tempranillo"

click → "Crear viticultor"

Resultado:
- User(42): name="Juan García", can_login=false, email="viticultores.abc123@noemail.agro365.es"
- WineryViticulturist(50): winery_id=5, viticulturist_id=42, source='own'
- Hereda: is_beta_user=true, beta_ends_at=2026-05-09 (plan de bodega)
```

#### Día 1 - Bodega envía invitación (10:45)

**Bodega accede a `/winery/viticulturists/42`**:
```
Rellena:
- Email: "juan@bodega-partner.es"
- Checkbox de cuaderno: (decide después)

click → "Enviar invitación"

Resultado:
- User(42): invitation_token="abc123...", invitation_expires_at=2026-04-16
- Email enviado: "Bodega Tinto Fuerte te invita..."
```

#### Día 3 - Viticultor acepta invitación (14:20)

**Viticultor abre email y hace click en link**:
```
URL: /claim-account/abc123...

Validación:
- Token válido ✅
- No expirado ✅ (expira 2026-04-16)
- User.can_login = false ✅

Formulario:
- Nombre: "Juan García" (pre-llenado, puede cambiar)
- Email: "juan@bodega-partner.es" (pre-llenado)
- Contraseña: "MiContraseña123!" (nuevo)
- Compartir cuaderno: ✅ (checked por defecto)

click → "Activar cuenta"

Resultado:
- User(42): can_login=true, password=hash, email_verified_at=now, invitation_token=null
- WineryViticulturist(50): cuaderno_access=true, cuaderno_granted_at=now
- Hereda: is_beta_user=true, beta_ends_at=2026-05-09 (PAGO activo)
- Auto-login → Redirige a /viticulturist/dashboard
```

#### Día 3 - Juan crea sus primeras campañas (14:45)

**Juan accede a `/viticulturist/campaign/create`**:
```
Datos:
- campaigns.viticulturist_id = 42 (el suyo)
- Crea 2 campañas para variedad Tempranillo

Bodega en `/winery/viticulturists/42/show`:
- Ve datos de Juan: parcelas, plantaciones, etc.
- Ve acceso: "Cuaderno compartido desde 2026-04-09"
```

#### Mes 2 - Plan de bodega expira (Mayo)

**Plan de bodega expira en 2026-05-09**:
```
Bodega:
- beta_ends_at = 2026-05-09 (HOY)
- isBetaUser() = true, betaExpired() = true
- Regresan a FREE automáticamente

Viticultor Juan:
- beta_ends_at = 2026-05-09 (HEREDADO)
- isBetaUser() = true, betaExpired() = true
- También regresa a FREE

Consecuencia:
- Ambos pierden acceso a features PAGO
- Si intentan usar feature de pago → 403 Forbidden
```

---

## 📊 MATRIZ DE INDEPENDENCIA vs CONEXIÓN

| Escenario | Viticultor | Bodega | Conexión | Status |
|-----------|-----------|--------|----------|--------|
| **Viticultor + Bodega** | ✅ Ghost creado | ✅ Crea | ✅ WineryViticulturist | Acoplado |
| **Invitación enviada** | ⏳ Pendiente (can_login=false) | ✅ Puede invitar | ✅ Token activo | Acoplado |
| **Invitación aceptada** | ✅ Activo (can_login=true) | ✅ Ve datos | ✅ cuaderno_access | Acoplado |
| **Sin cuaderno compartido** | ✅ Independiente | ❌ No ve cuaderno | ✅ Relación existe | Semi-acoplado |
| **Plan hereda de bodega** | ✅ Mismo plan | ✅ Bodega paga | ✅ beta_ends_at | Acoplado |
| **Plan bodega expira** | ❌ Pierde PAGO | ❌ Pierde PAGO | ✅ Sincronizado | Acoplado |

---

## 🔄 DIFERENCIAS CON OTROS MODELOS

### Producer (DIFERENTE)

```
Producer:
- user.role = 'producer'
- ✅ Un usuario único
- ✅ Sin invitaciones
- ✅ Acceso simultáneo a viticulturist + winery
- ✅ NO hereda plan (es su propio usuario)

Viticultor Invitado:
- user.role = 'viticulturist'
- ❌ Usuario separado creado por bodega
- ✅ Invitación + aceptación requerida
- ✅ Acceso a viticulturist SOLO (no a winery)
- ✅ HEREDA plan de bodega que invitó
```

### Viticultor Independiente (DIFERENTE)

```
Viticultor Independiente:
- user.role = 'viticulturist'
- ✅ Se auto-registra sin invitación
- ✅ NO vinculado a bodega (sin WineryViticulturist)
- ✅ Plan independiente (no heredado)
- ✅ Vende uvas si quiere, pero sin estructura formal

Viticultor Invitado:
- user.role = 'viticulturist'
- ❌ Creado por bodega (ghost initial)
- ✅ Vinculado a bodega (WineryViticulturist)
- ✅ Plan heredado de bodega
- ✅ Estructura formal de relación
```

---

## 🛡️ SEGURIDAD

### Ataques Potenciales Mitigados

```
1. ❌ Usar token expirado → Validado: expires_at > now()
2. ❌ Reutilizar token → Limpiado: invitation_token = null después
3. ❌ Bodega invita email ya registrado → Validado: unique(email)
4. ❌ Cambiar DNI en invitación → Guardado antes de ghost
5. ❌ Acceso sin aceptación → Chequeo: can_login = true requerido
```

### Datos Sensibles

```
- Password: Hash seguro, no accesible
- Token: 64 caracteres random, válido 7 días
- Email real: Solo visible en notificación de invitación
- DNI: Almacenado, único por active user (can_login=true)
```

---

## 📁 Archivos Clave

| Archivo | Rol |
|---------|-----|
| `app/Livewire/Winery/Viticulturists/Create.php` | Crear ghost user |
| `app/Livewire/Winery/Viticulturists/Invite.php` | Invitar viticultor existente |
| `app/Livewire/Winery/Viticulturists/Show.php` | Enviar invitación + gestionar acceso cuaderno |
| `app/Livewire/Auth/ClaimAccount.php` | Aceptar invitación y activar cuenta |
| `app/Models/WineryViticulturist.php` | Relación de organización |
| `app/Models/NotebookAccessRequest.php` | Solicitudes de acceso al cuaderno |
| `app/Console/Commands/CleanStaleInvitations.php` | Limpiar tokens expirados |
| `app/Console/Commands/PurgeAbandonedGhosts.php` | Eliminar ghosts sin usar |

---

## 🎯 CONCLUSIÓN

Agro365 implementa un **modelo SaaS organizacional** donde:

1. **Bodega es la organización** - paga la suscripción
2. **Viticultorists son miembros invitados** - heredan plan de bodega
3. **Ghost users son pre-registros** - esperan aceptación de invitación
4. **Plan se sincroniza** - cuando bodega paga/expira, viticulturists también
5. **Cuaderno compartido es opcional** - viticultor debe aprobar acceso

Este modelo permite que una bodega **gestione su equipo de viticulturists** bajo una sola suscripción, con estructura clara de permisos, invitaciones y acceso a datos.
