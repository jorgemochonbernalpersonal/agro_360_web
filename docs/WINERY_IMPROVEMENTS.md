# Mejoras Propuestas - Modelo Winery No Independiente

**Fecha**: 2026-04-09  
**Estado**: Análisis de Gap + Recomendaciones

---

## 🔴 CRÍTICAS (Seguridad + Data Integrity)

### ✅ 1. Tokens de Invitación sin Hash [COMPLETADO]

**Problema**: Tokens almacenados en plaintext en BD

```php
// Actual (INSEGURO)
$token = Str::random(64);  // plaintext
$user->update(['invitation_token' => $token]);  // Guardado sin hash

// Si BD se compromete → todos los tokens expuestos ✗
```

**Riesgo**: 
- Si BD es hackeada, atacante obtiene todos los tokens activos
- Puede aceptar invitaciones en lugar del viticultor

**Solución Propuesta**:
```php
// Nuevo
use Illuminate\Support\Facades\Hash;

$plainToken = Str::random(64);
$hashedToken = Hash::make($plainToken);

$user->update(['invitation_token' => $hashedToken]);

// En ClaimAccount.php
$user = User::where('can_login', false)->first();
if (!Hash::check($token, $user->invitation_token)) {
    $this->tokenValid = false;
}
```

**Archivos a cambiar**:
- `app/Livewire/Winery/Viticulturists/Show.php:75` - sendInvitation()
- `app/Livewire/Auth/ClaimAccount.php:30` - mount()

**Migration requerida**:
```php
// Change invitation_token from varchar(255) to varchar(255) con índice
// No es necesario migration, solo uso de Hash::check()
```

---

### ✅ 2. Ghost de Múltiples Bodegas - Auto-Grant Vulnerable [COMPLETADO]

**Problema**: Viticultor puede ser ghost de 2 bodegas simultáneamente

```
Bodega A crea ghost Juan (can_login=false)
Bodega B invita mismo Juan (email)
Juan acepta invitación...

¿De cuál bodega acepta?
```

**Código Actual**:
```php
// ClaimAccount.php:101-107
if ($this->shareCuaderno) {
    WineryViticulturist::where('viticulturist_id', $this->pendingUser->id)
        ->whereNotNull('winery_id')
        ->update(['cuaderno_access' => true]);  // ✗ ACTUALIZA TODAS
}
```

**Riesgo**: 
- Si Juan acepta, automáticamente da acceso al cuaderno a AMBAS bodegas
- Podría no saber que Bodega B también tiene acceso
- Violación de privacidad

**Solución Propuesta**:

**Opción A**: Bloquear múltiples invitaciones
```php
// En Invite.php:67 (link())
$alreadyLinked = WineryViticulturist::where('viticulturist_id', $userId)
    ->exists();  // ← Cualquier bodega

if ($alreadyLinked) {
    $this->toastError('Este viticultor ya está vinculado a otra bodega.');
    return null;
}
```

**Opción B**: Permitir múltiples pero con UI clara
```php
// En ClaimAccount.php:49 - mostrar todas las bodegas que lo invitaron
$allInvitations = WineryViticulturist::where('viticulturist_id', $user->id)
    ->with('winery')
    ->get();

// En view: checkbox individual por bodega
@foreach($allInvitations as $invitation)
    <input type="checkbox" name="share_cuaderno[{{ $invitation->winery_id }}]" />
    Compartir cuaderno con {{ $invitation->winery->name }}
@endforeach

// En activate(): update solo las seleccionadas
foreach ($request->input('share_cuaderno', []) as $wineryId => $checked) {
    if ($checked) {
        WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $user->id)
            ->update(['cuaderno_access' => true]);
    }
}
```

**Mi recomendación**: **Opción A es más segura** - Una bodega = Un viticultor

---

### ✅ 3. Datos Huérfanos al Desvincullar [COMPLETADO]

**Problema**: Viticultor desvinculado, pero sus datos quedan

```
Bodega desvincula viticultor Juan
- campaigns(viticulturist_id=42) → quedan en BD
- activities(viticulturist_id=42) → quedan en BD
- Pero WineryViticulturist se elimina

¿Ahora qué?
- Juan no puede ver sus datos (bodega lo desvinculó)
- Datos quedan huérfanos
- No hay referencia cruzada
```

**Solución Propuesta**:

**Opción A**: Soft delete (recomendado)
```php
// En unlinkViticulturist()
// En lugar de delete(), usar soft delete con causalidad

$relation->delete();  // Elimina relación
// Opcional: marcar campañas como archived/readonly
Campaign::where('viticulturist_id', $relation->viticulturist_id)
    ->where('winery_viticulturist_id', $relation->id)  // ← Agregar este campo
    ->update(['archived_at' => now()]);
```

**Opción B**: Vinculación explícita
```php
// En campaigns, activities: agregar campo winery_viticulturist_id
Schema::table('campaigns', function (Blueprint $table) {
    $table->foreignId('winery_viticulturist_id')
        ->nullable()
        ->constrained('winery_viticulturist')
        ->onDelete('soft delete');
});

// Cuando crear actividad
$activity = AgriculturalActivity::create([
    'viticulturist_id' => $user->id,
    'winery_viticulturist_id' => $relation->id,  // ← Vincular explícitamente
]);

// Cuando desvincullar: cascade soft delete automático
```

**Mi recomendación**: **Opción B** - Vinculación explícita previene todos los problemas futuros

---

## 🟡 IMPORTANTES (Funcionalidad + UX)

### 4. Rate Limit de 1 Hora es Restrictivo

**Problema**: No se puede reintentar invitación si viticultor perdió email

```php
// Actual
if ($this->viticulturist->invitation_sent_at
    && $this->viticulturist->invitation_sent_at->isAfter(now()->subHour())) {
    $this->toastError('Invitación enviada hace menos de 1 hora. Espera...');
    return;
}
```

**UX Issue**: 
- Viticultor dice "no recibí email"
- Bodega no puede reenviar en 1 hora
- Flujo interrumpido

**Solución Propuesta**:

```php
// Permitir reenvío inmediato si:
// 1. Invitación anterior expiró (> 7 días)
// 2. Token fue revocado explícitamente

public function sendInvitation(): void
{
    $lastSent = $this->viticulturist->invitation_sent_at;
    
    // Allow resubmission if expired or no token
    $isExpired = !$lastSent || $lastSent->addDays(7)->isPast();
    $hasActiveToken = $this->viticulturist->invitation_token;
    
    if ($hasActiveToken && !$isExpired && $lastSent->isAfter(now()->subHour())) {
        $this->toastError('Invitación activa. Revoca antes de reenviar.');
        return;
    }
    
    // ... enviar nueva invitación
}
```

**UI Improvement**:
```blade
@if ($viticulturist->invitation_token && !$viticulturist->invitation_expires_at?->isPast())
    <button wire:click="revokeInvitation">Revocar invitación actual</button>
    <button wire:click="sendInvitation">Reenviar invitación</button>
@else
    <button wire:click="sendInvitation">Enviar invitación</button>
@endif
```

---

### 5. Herencia de Plan - No Sincroniza Upgrades

**Problema**: Herencia solo ocurre en CREATE, no en upgrades

```php
// En Create.php:63-67
if ($winery->isBetaUser()) {
    $user->grantBetaAccess($winery->beta_ends_at);  // ✓ Hereda en creación
}

// Pero si bodega LUEGO compra plan
$winery->grantBetaAccess($newEndDate);  // Actualiza bodega
// ¿Pero también actualiza viticultores invitados? ✓ SÍ (línea 59-73)
```

**Después de revisar**: La cascada EXISTE en `HasBetaAccess::grantBetaAccess()`

**Pero UX Issue**: 
- No hay confirmación visual de que viticultorists heredan
- En Dashboard de bodega no se muestra: "5 viticultores heredarán plan"

**Mejora Propuesta**:
```php
// En grantBetaAccess()
public function grantBetaAccess(?\Carbon\Carbon $endsAt = null): void
{
    // ... código actual ...
    
    if ($this->hasWineryAccess()) {
        $viticulturistIds = ...;
        
        if ($viticulturistIds->isNotEmpty()) {
            User::whereIn('id', $viticulturistIds)->update([...]);
            
            // ✓ NUEVO: Emit event o log
            event(new BetaAccessCascadedToViticulturists(
                $this->id,
                $viticulturistIds->count(),
                $betaEndsAt
            ));
        }
    }
}
```

**Dashboard Alert**:
```blade
@if ($winery->isBetaUser() && $viticulturistCount > 0)
    <div class="alert alert-info">
        ℹ️ {{ $viticulturistCount }} viticultor(es) heredarán este plan
        hasta {{ $winery->beta_ends_at->format('d/m/Y') }}
    </div>
@endif
```

---

### 6. Relación `relationship_type` No Usada

**Problema**: Campo existe pero nunca se rellena

```php
// En WineryViticulturist
protected $fillable = [
    'relationship_type',  // ← Existe pero no se usa
];

// ENUM: 'supplier', 'employee', 'partner'
```

**Pero en Create.php, Invite.php, assignFromDO()**:
```php
WineryViticulturist::create([
    'winery_id'        => $wineryId,
    'viticulturist_id' => $userId,
    'source'           => $source,  // 'own', 'supervisor'
    'relationship_type' => null,    // ← NUNCA se asigna
]);
```

**Mejora Propuesta**:

```php
// En Create.php
public string $relationshipType = 'supplier';

protected function rules(): array {
    return [
        'relationshipType' => ['required', 'in:supplier,employee,partner'],
    ];
}

protected function performCreate(): void {
    WineryViticulturist::create([
        'relationship_type' => $this->relationshipType,  // ✓ NUEVO
    ]);
}
```

**En Show.php**:
```blade
<select wire:model="relationshipType">
    <option value="supplier">Proveedor de uvas</option>
    <option value="employee">Empleado de bodega</option>
    <option value="partner">Socio/Colaborador</option>
</select>
```

**Beneficios**:
- Claridad de relación
- Pricing diferente por tipo (proveedor = uno, empleado = otro)
- Permisos futuros basados en type

---

### 7. Cuaderno Compartido - 2 Flujos Confusos

**Problema**: Auto-grant en registro + solicitud vía botón

```
Flujo 1: Al registrarse, viticultor marca "Compartir cuaderno"
         → WineryViticulturist.cuaderno_access = true AUTOMÁTICO

Flujo 2: Después, bodega ve botón "Solicitar acceso al cuaderno"
         → Envía NotebookAccessRequest
         → Viticultor aprueba

¿Cuándo usar cada uno?
- Viticultor marca en registro: acceso inmediato
- Bodega solicita después: requiere aprobación
- ¿Qué pasa si viticultor marcó SÍ pero bodega solicita?
```

**Code Issue**:
```php
// En Show.php:119-122
if ($this->relation->cuaderno_access) {
    $this->toastInfo('Esta bodega ya tiene acceso.');
    return;
}
```

**Mejora Propuesta**: Unificar flujo

```php
// OPCIÓN A: Solo auto-grant en registro (más simple)
// OPCIÓN B: Solo solicitud (más control)
// OPCIÓN C: Auto-grant para SOURCE_OWN, solicitud para SOURCE_SUPERVISOR
```

**Recomendación**: **OPCIÓN C**

```php
// En ClaimAccount.php
if ($this->shareCuaderno) {
    $ownRelations = WineryViticulturist::where('viticulturist_id', $user->id)
        ->where('source', WineryViticulturist::SOURCE_OWN)  // ← Solo propios
        ->update(['cuaderno_access' => true]);
    
    // Para supervisor: requerir solicitud explícita
}

// En Show.php: mostrar estado claro
@if ($relation->source === 'own' && $relation->cuaderno_access)
    ✅ Acceso otorgado en registro
@elseif ($relation->source === 'supervisor')
    ⏳ Esperando aprobación del viticultor
@endif
```

---

### 8. Sin Auditoría de Cambios

**Problema**: No se registra quién hizo qué cambios en relaciones

```php
// Cuando bodega desvincula viticultor
$relation->delete();  // ¿Quién lo hizo? ¿Cuándo? ¿Por qué?

// Cuando se revoca acceso cuaderno
$relation->revokeNotebookAccess();  // ¿Auditoría?
```

**Mejora Propuesta**:

```php
// En WineryViticulturist
protected $fillable = [
    'unlinked_by',        // ← Nuevo
    'unlinked_at',        // ← Nuevo
    'cuaderno_revoked_by', // ← Nuevo
];

// En unlinkViticulturist()
$relation->update([
    'unlinked_by' => Auth::id(),
    'unlinked_at' => now(),
]);
$relation->delete();

// O mejor: soft delete para auditoría
public function unlinkViticulturist()
{
    $relation->softDelete();  // keeps data for audit
}

// En dashboard de bodega
$unlinkedViticulturists = WineryViticulturist::onlyTrashed()
    ->where('winery_id', Auth::id())
    ->get();
```

---

## 🟢 NICE TO HAVE (Futuro)

### 9. Múltiples Viticultores por Usuario

**Escenario futuro**:
```
Usuario "Juan García" puede ser viticultor para:
- Bodega A (ghost creado por A)
- Bodega B (ghost creado por B)
- Independiente (autoregistrado)

Acceso unificado en dashboard
```

**Cambios necesarios**:
```php
// Agregar tabla: user_organizations
Schema::create('user_organizations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->foreignId('organization_id')->nullable();  // winery_id o null
    $table->enum('organization_type', ['winery', 'independent', 'supervisor']);
    $table->timestamps();
});

// En Dashboard: selector de organización
$myOrganizations = Auth::user()->organizations()->get();
```

---

### 10. Notificaciones de Estado

**Mejora UX**: Notificaciones cuando estado cambia

```php
// Observer en WineryViticulturist
public function updated(WineryViticulturist $relation)
{
    // Cuaderno access granted
    if ($relation->wasChanged('cuaderno_access') && $relation->cuaderno_access) {
        $relation->viticulturist->notify(
            new NotebookAccessGrantedNotification($relation->winery)
        );
    }
    
    // Cuaderno access revoked
    if ($relation->wasChanged('cuaderno_access') && !$relation->cuaderno_access) {
        $relation->viticulturist->notify(
            new NotebookAccessRevokedNotification($relation->winery)
        );
    }
}
```

---

## 📊 MATRIZ DE PRIORIDAD

| Gap | Tipo | Severidad | Esfuerzo | Impacto | Prioridad |
|-----|------|-----------|----------|---------|-----------|
| ✅ 1. Tokens sin hash | Seguridad | 🔴 ALTA | 1h | Crítico | ✅ COMPLETADO |
| ✅ 2. Ghost múltiples bodegas | Seguridad | 🔴 ALTA | 2h | Alto | ✅ COMPLETADO |
| ✅ 3. Datos huérfanos | Data Integrity | 🟡 MEDIA | 4h | Alto | ✅ COMPLETADO |
| 4. Rate limit 1h | UX | 🟡 MEDIA | 1h | Bajo | 🟢 LUEGO |
| 5. Herencia plan upgrades | Funcionalidad | 🟢 BAJA | 2h | Bajo | 🟢 LUEGO |
| 6. relationship_type | Funcionalidad | 🟢 BAJA | 3h | Medio | 🟢 FUTURO |
| 7. Flujos cuaderno | UX | 🟡 MEDIA | 2h | Bajo | 🟢 LUEGO |
| 8. Auditoría | Operaciones | 🟢 BAJA | 3h | Bajo | 🟢 FUTURO |
| 9. Múltiples orgs | Arquitectura | 🟢 BAJA | 8h | Alto | 🟢 FUTURO |
| 10. Notificaciones | UX | 🟢 BAJA | 2h | Bajo | 🟢 FUTURO |

---

## 🎯 RECOMENDACIÓN: Top 3 Inmediatos

Si solo tienes tiempo para 3 mejoras, implementa estas:

### ✅ 1️⃣ COMPLETADO - Tokens Hash (1h)
```bash
# Cambio: plaintext → hashed
# Riesgo: BD hackeada → todos los tokens expuestos
# ✅ IMPLEMENTADO: Hash::make() en Store, Hash::check() en Verify
# Archivos actualizados:
#   - app/Livewire/Winery/Viticulturists/Show.php
#   - app/Livewire/Auth/ClaimAccount.php
#   - app/Http/Controllers/Api/AuthController.php
#   - app/Livewire/Supervisor/Growers/Index.php
#   - app/Livewire/Viticulturist/Viticulturists/Index.php
```

### ✅ 2️⃣ COMPLETADO - Ghost Múltiples Bodegas (2h)
```bash
# Cambio: Bloquear múltiples (Opción A implementada)
# Implementación:
#   - app/Livewire/Winery/Viticulturists/Invite.php:link() 
#     Verificación dual: same winery + other wineries
#   - app/Livewire/Auth/ClaimAccount.php:mount() 
#     Validación que bodega existe (no null)
```

### ✅ 3️⃣ COMPLETADO - Datos Huérfanos (4h)
```bash
# Cambio: Vincular explícito campaigns/activities a winery_viticulturist_id
# ✅ IMPLEMENTADO:
#   - Migration: 2026_04_09_000001_add_winery_viticulturist_id_...
#   - Models: Campaign + AgriculturalActivity con FK
#   - Relaciones: wineryRelation() belongsTo WineryViticulturist
#   - Create: Campaign/Create.php + CreatePruning.php + CreatePostHarvest.php
#   - Cascade: onDelete('cascade') automático en migration
```

---

## 📋 Checklist de Implementación

- [ ] Revisar y aprobar gaps
- [ ] Elegir soluciones (A o B cuando aplique)
- [ ] Crear migrations
- [ ] Implementar en componentes
- [ ] Tests unitarios y feature
- [ ] Cypress E2E
- [ ] Documentación actualizada

