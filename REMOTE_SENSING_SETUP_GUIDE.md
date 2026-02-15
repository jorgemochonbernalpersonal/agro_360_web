# 🚀 Configuración Remote Sensing: Producción REAL + Local MOCK

## ✅ TU CONFIGURACIÓN ES CORRECTA

### **🌐 Producción (.env en servidor)**
```env
NASA_EARTHDATA_MOCK=false      # ✅ DATOS REALES
OPEN_METEO_MOCK=false          # ✅ DATOS REALES
NASA_EARTHDATA_USERNAME=agro365
NASA_EARTHDATA_PASSWORD=Mistercagadas22@
NASA_EARTHDATA_API_URL=https://appeears.earthdatacloud.nasa.gov/api
```

### **💻 Local (.env en tu PC)**
```env
NASA_EARTHDATA_MOCK=true       # ✅ DATOS MOCK
OPEN_METEO_MOCK=true           # ✅ DATOS MOCK
NASA_EARTHDATA_USERNAME=agro365
NASA_EARTHDATA_PASSWORD=Mistercagadas22@
NASA_EARTHDATA_API_URL=https://appeears.earthdatacloud.nasa.gov/api
```

---

## 🔐 PASO 1: VERIFICAR CREDENCIALES NASA

**CRÍTICO:** Debes verificar que las credenciales funcionan ANTES de desplegar.

### Método 1: cURL (Más rápido)

```bash
curl -X POST "https://appeears.earthdatacloud.nasa.gov/api/login" \
  -u "agro365:Mistercagadas22@" \
  -H "Content-Type: application/json"
```

#### ✅ Si funciona verás:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_in": 86400
}
```

#### ❌ Si falla verás:
```json
{
  "message": "Invalid username/password"
}
```
O simplemente: `401 Unauthorized`

---

### Método 2: Comando Artisan (Más completo)

He creado un comando para testear todo:

```bash
# En local (probará con mock si está activado)
php artisan remote-sensing:test-credentials

# En local con credenciales reales (temporalmente cambia .env a MOCK=false)
NASA_EARTHDATA_MOCK=false php artisan remote-sensing:test-credentials
```

**Salida esperada si funciona:**
```
🔍 Testing NASA Earthdata credentials...

Configuration:
  Mock mode: DISABLED
  Username: agro365
  Environment: local

Testing with plot: Viñedo Sur (ID: 1)

📡 Fetching NDVI data...
✅ Data retrieved successfully!

┌─────────────────┬──────────────────────────────────┐
│ Field           │ Value                            │
├─────────────────┼──────────────────────────────────┤
│ NDVI Mean       │ 0.723                            │
│ NDVI Min        │ 0.693                            │
│ NDVI Max        │ 0.753                            │
│ Health Status   │ excellent                        │
│ Image Date      │ 2026-02-15                       │
│ Image Source    │ NASA MODIS MOD13Q1               │
│ Cloud Coverage  │ 12%                              │
└─────────────────┴──────────────────────────────────┘

✅ This is REAL data from NASA satellites!
```

---

## 🆘 SI LAS CREDENCIALES NO FUNCIONAN

### Opción A: Registrar Nueva Cuenta

1. **Crear cuenta en NASA Earthdata:**
   - Ve a: https://urs.earthdata.nasa.gov/users/new
   - Username: `agro365` (o el que prefieras)
   - Password: (actualiza en .env después)
   - Email: `info@agro365.es`

2. **Aprobar AppEEARS:**
   - Inicia sesión en: https://appeears.earthdatacloud.nasa.gov/
   - Autoriza la aplicación cuando te lo pida
   - Acepta los términos de uso

3. **Espera 10-15 minutos** (a veces tarda en activarse)

4. **Actualiza .env:**
   ```env
   NASA_EARTHDATA_USERNAME=tu_nuevo_usuario
   NASA_EARTHDATA_PASSWORD=tu_nuevo_password
   ```

5. **Vuelve a probar:**
   ```bash
   curl -X POST "https://appeears.earthdatacloud.nasa.gov/api/login" \
     -u "tu_nuevo_usuario:tu_nuevo_password"
   ```

### Opción B: Recuperar Cuenta Existente

Si ya tienes cuenta pero olvidaste password:
- Ve a: https://urs.earthdata.nasa.gov/
- Click en "Forgot Password?"
- Usa: `info@agro365.es` o el email que registraste

---

## 🧪 PASO 2: PROBAR EN LOCAL CON DATOS REALES

Antes de desplegar a producción:

### 1. Temporalmente activa datos reales en local

En tu `.env` local, cambia temporalmente:
```env
NASA_EARTHDATA_MOCK=false  # Temporal para prueba
OPEN_METEO_MOCK=false
```

### 2. Limpia cache
```bash
php artisan cache:clear
```

### 3. Prueba con el comando
```bash
php artisan remote-sensing:test-credentials
```

### 4. O prueba con una parcela específica
```bash
php artisan remote-sensing:regenerate-mock --plot-id=1
```

### 5. Revisa logs
```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50 | Select-String "NASA"

# O abre el archivo directamente
# storage/logs/laravel.log
```

**Busca estas líneas:**
- ✅ `[INFO] NASA Earthdata: Token obtained successfully`
- ✅ `[INFO] NDVI data fetched successfully`
- ❌ `[ERROR] NASA Earthdata: Failed to get auth token`
- ❌ `[ERROR] NASA Earthdata API request failed`

### 6. Si funciona en local → Restaura mock
```env
NASA_EARTHDATA_MOCK=true  # Volver a mock para desarrollo local
```

---

## 🚀 PASO 3: DESPLEGAR A PRODUCCIÓN

Solo cuando hayas verificado que funciona en local:

### 1. Asegúrate que el .env de producción tiene:
```env
APP_ENV=production
NASA_EARTHDATA_MOCK=false
OPEN_METEO_MOCK=false
NASA_EARTHDATA_USERNAME=agro365
NASA_EARTHDATA_PASSWORD=Mistercagadas22@
```

### 2. Despliega el código
```bash
git add .
git commit -m "fix: remote-sensing improvements and production setup"
git push
```

### 3. En el servidor, ejecuta:
```bash
# Limpiar duplicados (si existen)
php artisan remote-sensing:clean-duplicates

# Limpiar cache
php artisan cache:clear

# Probar credenciales
php artisan remote-sensing:test-credentials

# Generar datos para una parcela de prueba
php artisan remote-sensing:regenerate-mock --plot-id=1
```

### 4. Verifica los logs
```bash
# Linux/Mac
tail -f storage/logs/laravel.log | grep "NASA"

# O descarga y revisa el archivo de logs
```

**Busca:**
- ✅ `NASA Earthdata: Token obtained successfully`
- ✅ `NDVI data fetched successfully`
- ✅ `image_source: NASA MODIS MOD13Q1` (sin "Mock")

### 5. Prueba desde la UI
- Ve al dashboard de Remote Sensing
- Selecciona una parcela
- Haz click en "Actualizar"
- Verifica que `image_source` NO dice "Mock"

---

## 🔄 COMPORTAMIENTO ESPERADO

### **En Producción (MOCK=false):**

1. **Primera vez:**
   - Llama a NASA API
   - Obtiene token de autenticación
   - Descarga datos MODIS del último período disponible
   - Guarda en base de datos

2. **Siguientes veces (mismo día):**
   - Usa datos de la base de datos (no vuelve a llamar API)
   - Más rápido y eficiente

3. **Día siguiente:**
   - Intenta obtener datos nuevos de NASA
   - Si falla → usa último dato guardado (no crashea)

4. **Si API falla:**
   - Log del error
   - Retorna último dato válido de la BD
   - No afecta la experiencia del usuario

### **En Local (MOCK=true):**

1. Genera datos simulados consistentes
2. Guarda en BD local
3. No llama a APIs externas
4. Desarrollo rápido sin límites

---

## 📊 DIFERENCIA DE DATOS

### **Mock (Local):**
```
Source: NASA MODIS (Mock)
NDVI: 0.742 (calculado estacionalmente)
Update: Cada carga genera para ese día
```

### **Real (Producción):**
```
Source: NASA MODIS MOD13Q1
NDVI: 0.731 (desde satélite Sentinel)
Update: Cada 16 días (ciclo satélite)
```

---

## ⚠️ LIMITACIONES NASA API

### Resolución Temporal
- **MODIS:** 16 días (cada dos semanas)
- **No es tiempo real:** última imagen puede ser de hace 1-2 semanas
- **Normal:** Los satélites pasan cada cierto tiempo

### Cobertura de Nubes
- Si hay nubes → imagen descartada
- Puede tardar más en obtener dato limpio
- Especialmente en invierno/primavera

### Alternativa si no hay datos recientes
El sistema automáticamente:
1. Intenta obtener última imagen disponible
2. Si no hay → usa último dato en BD
3. Nunca deja al usuario sin información

---

## 🎯 COMANDOS ÚTILES

```bash
# Probar credenciales
php artisan remote-sensing:test-credentials

# Regenerar datos de todas las parcelas
php artisan remote-sensing:regenerate-mock --clear

# Regenerar solo una parcela
php artisan remote-sensing:regenerate-mock --plot-id=5

# Limpiar duplicados
php artisan remote-sensing:clean-duplicates

# Ver logs en tiempo real (Linux)
tail -f storage/logs/laravel.log | grep -E "NASA|NDVI"

# Limpiar cache
php artisan cache:clear
```

---

## ✅ CHECKLIST FINAL ANTES DE PRODUCCIÓN

- [ ] Credenciales NASA verificadas con cURL
- [ ] Probado en local con `MOCK=false` y funciona
- [ ] Logs revisados, sin errores
- [ ] `.env` local tiene `MOCK=true`
- [ ] `.env` producción tiene `MOCK=false`
- [ ] Código desplegado al servidor
- [ ] Cache limpiada en servidor
- [ ] Comando `test-credentials` ejecutado en servidor
- [ ] UI comprobada, muestra datos sin "Mock"

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error: "Failed to get auth token"
**Causa:** Credenciales incorrectas
**Solución:** Registra/recupera cuenta en NASA Earthdata

### Error: "No data available"
**Causa:** No hay imágenes recientes (nubes o periodo sin pasar satélite)
**Solución:** Normal, sistema usará último dato disponible en BD

### Datos no se actualizan
**Causa:** Ya hay dato para hoy en BD
**Solución:** Normal, solo actualiza cuando pasan 16+ días

### Ver "Mock" en producción
**Causa:** `NASA_EARTHDATA_MOCK=true` en .env
**Solución:** Verifica que en producción esté en `false`

---

## 📞 RESUMEN EJECUTIVO

**Tu configuración es CORRECTA:**
- ✅ Producción: datos reales
- ✅ Local: datos mock

**Antes de desplegar:**
1. Verifica credenciales con cURL o comando artisan
2. Prueba en local con `MOCK=false` temporalmente
3. Si funciona → despliega

**Después de desplegar:**
1. Ejecuta comandos en servidor
2. Revisa logs
3. Comprueba UI

**Si algo falla:**
- Revisa logs: `storage/logs/laravel.log`
- Ejecuta: `php artisan remote-sensing:test-credentials`
- Las mejoras que hice evitan crashes

✅ **Todo listo para producción con datos reales!**
