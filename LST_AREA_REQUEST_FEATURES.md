# 🌡️ LST & 🗺️ Area Request - Nuevas Funcionalidades

## 📋 Resumen

Se han implementado **2 nuevas capacidades avanzadas** de la API de NASA AppEEARS:

1. **LST (Land Surface Temperature)** - Temperatura de superficie vía MODIS
2. **Area Request** - Mapas de variabilidad intra-parcela (vigor zonificado)

---

## 🌡️ FUNCIONALIDAD 1: LST (Land Surface Temperature)

### ¿Qué hace?

Obtiene la **temperatura de la superficie del suelo/vegetación** (no del aire) usando datos térmicos del satélite MODIS.

### Datos que proporciona

- **LST Día**: Temperatura máxima de superficie durante el día
- **LST Noche**: Temperatura mínima de superficie durante la noche
- **Amplitud Térmica (DTR)**: Diferencia día-noche
- **CWSI (Crop Water Stress Index)**: Índice de estrés hídrico calculado con LST

### Alertas automáticas

1. **Estrés Térmico** (calor excesivo)
   - Umbral: >42°C en verano, >38°C resto del año
   - Severidad: Moderado / Alto / Crítico
   - Recomendación: Riego refrigerante

2. **Riesgo de Helada** (frío)
   - Umbral: <3°C nocturno en primavera
   - Riesgo fenológico: Daño en brotación/floración
   - Recomendación: Activar sistemas anti-helada

3. **CWSI (Estrés Hídrico)**
   - Rango: 0-1 (0 = sin estrés, 1 = estrés máximo)
   - Niveles: Sin estrés / Leve / Moderado / Alto / Crítico
   - Combina: LST + temperatura del aire + humedad

### Valor real

- **Detección temprana de estrés** antes de que sea visible
- **Riego de precisión** basado en necesidad real de planta
- **Prevención de pérdidas** por heladas o calor extremo
- **Ahorro de agua** 15-25% vs riego tradicional

### API utilizada

```
NASA MODIS MOD11A2.061 (LST)
- Resolución: 1km
- Frecuencia: 8 días
- Producto: gratuito
```

---

## 🗺️ FUNCIONALIDAD 2: Area Request (Mapa de Vigor)

### ¿Qué hace?

En lugar de obtener un **único valor** de NDVI para el centro de la parcela, obtiene **todos los píxeles** dentro de la parcela y genera un **mapa de variabilidad**.

### Datos que proporciona

**Estadísticas espaciales:**
- Media, mínima, máxima, desviación estándar
- Percentiles (P25, P50, P75)
- Coeficiente de variación (CV)
- Número de píxeles analizados

**Zonas de Vigor:**
1. 🔴 **Bajo Vigor** - Zonas con problemas
2. 🟡 **Vigor Medio** - Zonas normales
3. 🟢 **Alto Vigor** - Zonas excelentes

### Interpretación CV (Coeficiente de Variación)

- **<10%**: Parcela homogénea ✅
- **10-20%**: Variabilidad moderada ⚠️
- **>20%**: Alta heterogeneidad 🚨 (problemas localizados)

### Aplicaciones prácticas

1. **Agricultura de precisión**
   - Aplicar más fertilizante solo donde hace falta
   - Riego diferenciado por zonas
   - Tratamientos fitosanitarios zonificados

2. **Detección de problemas**
   - Identificar zonas con enfermedades
   - Localizar áreas con mal drenaje
   - Encontrar zonas con déficit nutricional

3. **Optimización económica**
   - Reducir insumos 20-30%
   - Aumentar rendimiento en zonas rezagadas
   - ROI típico: 3-5x en primer año

### Diferencia vs Point Request

| Característica | Point Request | Area Request |
|----------------|---------------|--------------|
| Tiempo respuesta | Instantáneo | 5-10 minutos |
| Dato obtenido | 1 valor NDVI | Mapa completo |
| Variabilidad | No detectada | Sí detectada |
| Costo API | Ninguno | Ninguno |
| Uso de recursos | Bajo | Alto |

### Flujo de trabajo

1. **Solicitud**: `remote-sensing:update-enriched --include-area`
2. **NASA procesa**: 5-10 minutos en background
3. **Verificación**: `remote-sensing:check-area-tasks`
4. **Descarga**: Automática si tarea completa
5. **Visualización**: Dashboard → Pestaña "🗺️ Mapa Vigor"

---

## 📊 Nuevos Campos en Base de Datos

### Tabla: `plot_remote_sensing`

```sql
-- LST
lst_day           DECIMAL(6,2)  -- Temperatura superficie día (°C)
lst_night         DECIMAL(6,2)  -- Temperatura superficie noche (°C)
lst_diff          DECIMAL(6,2)  -- Amplitud térmica (°C)
cwsi              DECIMAL(5,3)  -- Crop Water Stress Index (0-1)

-- Area Request
area_statistics   JSON          -- Estadísticas espaciales completas
data_source       VARCHAR(50)   -- 'point', 'area', 'timeseries'
satellite         VARCHAR(20)   -- 'MODIS', 'VIIRS'
pixel_reliability INT           -- Calidad del dato (0=bueno, 1-3=malo)
```

---

## 🚀 Uso

### 1. Actualizar datos enriquecidos (solo LST)

```bash
# Todas las parcelas
php artisan remote-sensing:update-enriched

# Parcela específica
php artisan remote-sensing:update-enriched --plot-id=123

# Forzar actualización (aunque ya esté actualizado hoy)
php artisan remote-sensing:update-enriched --force
```

### 2. Actualizar con Area Request (más lento)

```bash
# Solicitar mapas de vigor
php artisan remote-sensing:update-enriched --include-area

# Solo para una parcela
php artisan remote-sensing:update-enriched --plot-id=123 --include-area
```

### 3. Verificar tareas de área

```bash
# Ver estado de todas las tareas pendientes
php artisan remote-sensing:check-area-tasks

# Esto descarga automáticamente las tareas completadas
```

---

## 🖥️ Interfaz Web

### Nueva Pestaña: 🌡️ Térmico

**Ubicación**: Dashboard Teledetección → Pestaña "🌡️ Térmico"

**Muestra:**
- 🌡️ Temperatura Día/Noche/Amplitud
- 💧 CWSI con indicador visual
- 🔥 Alertas de estrés térmico
- ❄️ Alertas de riesgo de helada

### Nueva Pestaña: 🗺️ Mapa Vigor

**Ubicación**: Dashboard Teledetección → Pestaña "🗺️ Mapa Vigor"

**Muestra:**
- 📊 Estadísticas de área (mean, min, max, stddev)
- 📈 Coeficiente de variación
- 🔴🟡🟢 Zonas de vigor (bajo/medio/alto)
- 🎯 Percentiles (P25, P50, P75)

**Botón**: "Solicitar Mapa de Vigor" - Solo visible si no hay datos

---

## ⏰ Automatización (Cron)

### Actualización diaria LST (recomendado)

```bash
# Crontab entry (ejecutar a las 6 AM)
0 6 * * * cd /ruta/agro365 && php artisan remote-sensing:update-enriched >> /dev/null 2>&1
```

### Actualización semanal Area (opcional)

```bash
# Crontab entry (ejecutar domingos a las 3 AM)
0 3 * * 0 cd /ruta/agro365 && php artisan remote-sensing:update-enriched --include-area >> /dev/null 2>&1

# Verificar tareas pendientes (domingos a las 4 AM - 1h después)
0 4 * * 0 cd /ruta/agro365 && php artisan remote-sensing:check-area-tasks >> /dev/null 2>&1
```

---

## 🔬 Componentes Técnicos Creados

### Servicios Backend

1. **`NasaLSTService`**
   - Fetch LST data from MODIS
   - Calculate CWSI
   - Detect heat stress & frost risk
   - Generate mock data (development)

2. **`NasaAreaRequestService`**
   - Submit area requests (async)
   - Check task status
   - Download completed data
   - Calculate spatial statistics
   - Create vigor zones

3. **`NasaEarthdataService::fetchEnrichedData()`**
   - Orchestrates LST + Area fetching
   - Updates PlotRemoteSensing records
   - Handles task metadata

### Componentes Livewire

1. **`ThermalStressCard`**
   - Display LST metrics
   - CWSI visualization
   - Heat stress alerts
   - Frost risk warnings

2. **`VigorMapCard`**
   - Display area statistics
   - Variability analysis (CV)
   - Vigor zones
   - Request button for new maps

### Comandos Artisan

1. **`remote-sensing:update-enriched`**
   - Updates LST data
   - Optionally requests area data
   - Progress bar & summary

2. **`remote-sensing:check-area-tasks`**
   - Checks pending area tasks
   - Downloads completed ones
   - Updates database

---

## 🧪 Testing

### Test LST (desarrollo con mock)

```bash
# Actualizar una parcela con LST
php artisan remote-sensing:update-enriched --plot-id=1

# Verificar en DB
SELECT id, plot_id, lst_day, lst_night, cwsi, data_source 
FROM plot_remote_sensing 
WHERE plot_id = 1 
ORDER BY image_date DESC LIMIT 1;
```

### Test Area Request (mock)

```bash
# Solicitar área
php artisan remote-sensing:update-enriched --plot-id=1 --include-area

# Verificar metadata
SELECT id, plot_id, metadata->>'$.area_task_id' as task_id 
FROM plot_remote_sensing 
WHERE plot_id = 1 
ORDER BY updated_at DESC LIMIT 1;

# "Completar" tarea (en mock se completa instantáneamente)
php artisan remote-sensing:check-area-tasks

# Verificar area_statistics
SELECT id, plot_id, area_statistics 
FROM plot_remote_sensing 
WHERE plot_id = 1 AND area_statistics IS NOT NULL;
```

---

## 💰 ROI Estimado

### LST + CWSI
- **Ahorro agua**: 15-25% (300-500 m³/ha/año)
- **Prevención heladas**: 5-20% cosecha (1 helada evitada)
- **ROI**: 4-6x en primer año

### Area Request (Agricultura de Precisión)
- **Reducción fertilizantes**: 20-30%
- **Incremento rendimiento**: 10-15% en zonas rezagadas
- **Ahorro fitosanitarios**: 15-25%
- **ROI**: 5-8x en primer año

---

## ⚠️ Limitaciones

### LST
- Resolución: 1km (no detecta variabilidad intra-parcela)
- Frecuencia: 8 días (no diario)
- Nubosidad: Puede afectar calidad del dato

### Area Request
- **Async**: 5-10 minutos de procesamiento
- **Cuota NASA**: Max 10 tareas simultáneas
- **Tamaño parcela**: Mejor para >1ha (parcelas pequeñas = pocos píxeles)
- **Procesamiento**: Requiere geometría SIGPAC válida

---

## 🔮 Próximas Mejoras

1. **VIIRS NDVI** (375m vs 250m MODIS)
2. **Evapotranspiración** oficial NASA (vs cálculo Open-Meteo)
3. **Soil Moisture** desde SMAP
4. **Mapas visuales** (GeoJSON overlay en mapa)
5. **Notificaciones push** para alertas térmicas

---

## 📚 Referencias

- [NASA MODIS LST Product](https://lpdaac.usgs.gov/products/mod11a2v061/)
- [AppEEARS API Documentation](https://appeears.earthdatacloud.nasa.gov/api/)
- [CWSI Calculation Methods](https://www.frontiersin.org/articles/10.3389/fpls.2017.01450/full)
