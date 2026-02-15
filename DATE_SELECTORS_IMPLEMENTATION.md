# 📅 Selectores de Fecha Histórica en Tarjetas Individuales

## Implementación Completada - 15/02/2026

### 🎯 Problema Resuelto

**Antes:** Todas las tarjetas (LAI, SMAP, Thermal, Spectral, Vigor Map) solo mostraban el último dato disponible.

**Ahora:** Cada tarjeta tiene un selector de fecha que permite:
- Ver datos del 12 de enero
- Comparar datos del 22 vs 15 de diciembre
- Analizar evolución seleccionando fechas específicas

---

## ✅ Tarjetas Actualizadas

### 1. **🌿 LAI Oficial Card**
- Selector de fecha: últimos 30 registros disponibles
- Campo filtrado: `lai` (not null)
- Vista: datos LAI, FPAR y estimación de rendimiento para fecha seleccionada

### 2. **🛰️ SMAP Soil Card**
- Selector de fecha: últimos 30 registros
- Campo filtrado: `soil_moisture_surface_smap`
- Vista: humedad superficie y zona radicular para fecha seleccionada

### 3. **🌡️ Thermal Stress Card**
- Selector de fecha: últimos 30 registros
- Campo filtrado: `lst_day`
- Vista: temperatura día/noche, CWSI, estrés térmico para fecha seleccionada

### 4. **🌈 Spectral Bands Card**
- Selector de fecha: últimos 30 registros
- Campo filtrado: `red_band`
- Vista: bandas espectrales e índices (GNDVI, NDRE, MSR, etc.) para fecha seleccionada

### 5. **🗺️ Vigor Map Card**
- Selector de fecha: últimos 30 registros
- Campo filtrado: `area_statistics`
- Vista: estadísticas de área y zonas de vigor para fecha seleccionada

---

## 🔧 Implementación Técnica

### Cada componente ahora incluye:

```php
// Propiedades
public ?string $selectedDate = null;
public array $availableDates = [];

// Métodos
public function loadAvailableDates() {
    // Carga últimos 30 registros con datos
}

public function updatedSelectedDate() {
    // Reactive: recarga al cambiar fecha
}

public function loadData() {
    // Filtro condicional por fecha
    $query = $this->plot->remoteSensingData()
        ->whereNotNull('campo_especifico');
    
    if ($this->selectedDate) {
        $query->whereDate('image_date', $this->selectedDate);
    }
    
    $remoteSensing = $query->orderBy('image_date', 'desc')->first();
}
```

### UI en cada tarjeta:

```html
<select wire:model.live="selectedDate">
    <option value="">Último dato</option>
    @foreach($availableDates as $date)
        <option value="{{ $date }}">
            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
        </option>
    @endforeach
</select>
```

---

## 📊 Casos de Uso Cubiertos

1. ✅ **Análisis retrospectivo**: "¿Cómo estaba el LAI el 12 de enero?"
2. ✅ **Comparación manual**: Seleccionar fecha 1, anotar valores, seleccionar fecha 2, comparar
3. ✅ **Investigación de eventos**: "Hubo helada el 22/12, ¿qué temperatura térmica había?"
4. ✅ **Validación de acciones**: "Regué el 15/01, ¿mejoró la humedad SMAP después?"
5. ✅ **Tracking temporal**: Navegar fecha por fecha para ver evolución

---

## 🎨 UX Mejorada

### Layout del selector:
```
┌─────────────────────────────────────────────────────┐
│ 🌿 LAI Oficial NASA      [📅 15/02/2026 ▼] [🔄]    │
│ Leaf Area Index...                                  │
└─────────────────────────────────────────────────────┘
```

**Características:**
- **Posición**: esquina superior derecha de cada tarjeta
- **Opción por defecto**: "Último dato" (más reciente)
- **Formato fecha**: dd/mm/YYYY (español)
- **Límite**: últimos 30 registros (suficiente para 1-2 meses)
- **Ordenación**: descendente (más reciente primero)
- **Reactive**: `wire:model.live` actualiza instantáneamente

---

## 🚀 Ventajas

1. **Sin cambiar flujo normal**: por defecto muestra último dato (como antes)
2. **Exploración fácil**: dropdown con fechas disponibles
3. **Sin errores**: solo muestra fechas con datos reales
4. **Performance**: carga solo las fechas, no todos los datos
5. **Consistente**: mismo patrón en todas las tarjetas

---

## 📝 Mejoras Futuras (Opcional)

Si quieres llevar esto más lejos:

1. **Navegación por flechas**: ← → para fecha anterior/siguiente
2. **Comparador integrado**: checkbox "Comparar con otra fecha"
3. **Favoritos**: marcar fechas importantes (ej: "Día de riego")
4. **Vista timeline**: mini gráfico en el dropdown mostrando NDVI
5. **Detección automática**: resaltar fechas con anomalías

---

## 🔍 Testing

**Verificar:**
- [ ] Selector aparece solo si hay datos disponibles
- [ ] Seleccionar fecha carga datos correctos
- [ ] "Último dato" muestra el más reciente
- [ ] Cambiar de tarjeta mantiene fechas independientes
- [ ] Error claro si no hay datos para fecha seleccionada
- [ ] Botón "Actualizar" recarga fechas disponibles

---

## 📦 Archivos Modificados

### Backend (PHP):
1. `app/Livewire/Viticulturist/RemoteSensing/OfficialLAICard.php`
2. `app/Livewire/Viticulturist/RemoteSensing/SmapSoilCard.php`
3. `app/Livewire/Viticulturist/RemoteSensing/ThermalStressCard.php`
4. `app/Livewire/Viticulturist/RemoteSensing/SpectralBandsCard.php`
5. `app/Livewire/Viticulturist/RemoteSensing/VigorMapCard.php`

### Frontend (Blade):
1. `resources/views/livewire/viticulturist/remote-sensing/official-lai-card.blade.php`
2. `resources/views/livewire/viticulturist/remote-sensing/smap-soil-card.blade.php`
3. `resources/views/livewire/viticulturist/remote-sensing/thermal-stress-card.blade.php`
4. `resources/views/livewire/viticulturist/remote-sensing/spectral-bands-card.blade.php`
5. `resources/views/livewire/viticulturist/remote-sensing/vigor-map-card.blade.php`

**Total:** 10 archivos modificados

---

## ✅ Resumen

**Ahora TODAS las tarjetas individuales tienen selector de fecha.**

Puedes:
- Ver LAI del mes pasado
- Comparar temperatura térmica del 12 vs hoy
- Analizar bandas espectrales del 22
- Revisar humedad SMAP de cualquier fecha disponible
- Consultar vigor map histórico

**La navegación temporal está completa en TODO el dashboard.**
