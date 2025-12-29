<!-- FAQ 1: Informes Oficiales -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button 
        @click="openIndexes.includes(1) ? openIndexes = openIndexes.filter(i => i !== 1) : openIndexes.push(1)"
        class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all"
    >
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Cómo funcionan los informes oficiales con firma electrónica?
            </h3>
            <p class="text-sm text-gray-500">7 tipos certificados con SHA-256</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(1) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(1)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-4">
            Agro365 genera <strong>7 tipos de informes oficiales certificados</strong>: Tratamientos Fitosanitarios, Riegos, Fertilizaciones, Labores Culturales, Cosechas, PAC y Certificaciones Completas.
        </p>
        <p class="text-gray-700 leading-relaxed mb-3"><strong>Cada informe incluye:</strong></p>
        <ul class="space-y-2 text-gray-700 mb-4">
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Firma electrónica SHA-256</strong> única e inmutable</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Código QR de verificación pública</strong> en cada página</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Verificación instantánea</strong> en agro365.es/verify-report/CODIGO</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Contador de verificaciones</strong> para auditoría completa</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Invalidación en 30 días</strong> si detectas errores</span>
            </li>
        </ul>
        <p class="text-gray-700 leading-relaxed">
            Los inspectores escanean el QR y validan instantáneamente la autenticidad. También puedes compartir el link público con cooperativas, bodegas o certificadoras.
        </p>
    </div>
</div>

<!-- FAQ 2: SIGPAC -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(2) ? openIndexes = openIndexes.filter(i => i !== 2) : openIndexes.push(2)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Qué es SIGPAC y cómo me ayuda Agro365?
            </h3>
            <p class="text-sm text-gray-500">Integración completa con códigos oficiales</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(2) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(2)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-3">
            <strong><a href="{{ route('content.sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></strong> (Sistema de Información Geográfica de Parcelas Agrícolas) es el sistema oficial del Ministerio que identifica cada parcela con un código único: <code class="bg-gray-100 px-2 py-1 rounded text-sm">PROVINCIA-MUNICIPIO-AGREGADO-ZONA-PARCELA-RECINTO</code>
        </p>
        <p class="text-gray-700 leading-relaxed mb-3"><strong>Agro365 integra SIGPAC completamente:</strong></p>
        <ul class="space-y-2 text-gray-700 mb-4">
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Códigos <strong>multiparcela</strong> (varias subparcelas en una gestión)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Geometrías GeoJSON</strong> visualizadas en mapa interactivo</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Variedades, <strong>hectáreas exactas</strong> y sistema de conducción por recinto</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Asociación automática</strong> de actividades al código SIGPAC correcto</span>
            </li>
        </ul>
        <p class="text-gray-700 leading-relaxed">
            Cuando registras un tratamiento o riego, Agro365 lo asocia automáticamente al SIGPAC. En inspecciones PAC, presentas informes oficiales con códigos verificables. <strong>Sin errores, sin multas.</strong>
        </p>
    </div>
</div>

<!-- FAQ 3: Cuadrillas y Maquinaria -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(3) ? openIndexes = openIndexes.filter(i => i !== 3) : openIndexes.push(3)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Puedo gestionar cuadrillas y maquinaria?
            </h3>
            <p class="text-sm text-gray-500">Control total de recursos y costos</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(3) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(3)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-4">¡Absolutamente! Agro365 incluye gestión completa de recursos humanos y materiales:</p>
        <div class="mb-4">
            <h4 class="font-semibold text-[var(--color-agro-green-dark)] mb-2">👥 GESTIÓN DE CUADRILLAS:</h4>
            <ul class="space-y-2 text-gray-700 ml-4">
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Registra cuadrillas completas con miembros y roles</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Asigna personal específico a cada actividad</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Calcula costos laborales reales por parcela</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Trazabilidad completa de mano de obra</span></li>
            </ul>
        </div>
        <div class="mb-3">
            <h4 class="font-semibold text-[var(--color-agro-green-dark)] mb-2">🚜 CONTROL DE MAQUINARIA:</h4>
            <ul class="space-y-2 text-gray-700 ml-4">
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Registra tractores, pulverizadores, equipos especializados</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Asocia maquinaria específica a cada actividad</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Historial completo de uso por parcela</span></li>
                <li class="flex items-start gap-2"><span class="text-[var(--color-agro-green)] font-bold">•</span><span>Análisis de costos de maquinaria desglosados</span></li>
            </ul>
        </div>
        <p class="text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg">
            <strong>💡 Beneficio:</strong> Sabes EXACTAMENTE cuánto cuesta mantener cada parcela (mano de obra + maquinaria + insumos) para optimizar tu rentabilidad.
        </p>
    </div>
</div>

<!-- FAQ 4: Rendimientos -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(4) ? openIndexes = openIndexes.filter(i => i !== 4) : openIndexes.push(4)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Puedo comparar rendimientos estimados vs reales?
            </h3>
            <p class="text-sm text-gray-500">Sistema completo de análisis de cosechas</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(4) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(4)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-3">Sí. Agro365 tiene un <strong>sistema completo de estimación y análisis de cosechas</strong>:</p>
        <div class="space-y-3 mb-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-1">📊 ANTES DE VENDIMIA:</h4>
                <ul class="text-sm text-blue-800 ml-4 space-y-1">
                    <li>• Registra estimaciones de rendimiento por parcela</li>
                    <li>• Kg/ha esperados por variedad</li>
                    <li>• Previsión total de cosecha para planificar</li>
                </ul>
            </div>
            <div class="bg-purple-50 p-3 rounded-lg">
                <h4 class="font-semibold text-purple-900 mb-1">🍇 DURANTE VENDIMIA:</h4>
                <ul class="text-sm text-purple-800 ml-4 space-y-1">
                    <li>• Registra contenedores individuales (IDs únicos)</li>
                    <li>• Kg reales por contenedor</li>
                    <li>• Estados: En campo / En bodega / Vinificado / Facturado</li>
                </ul>
            </div>
            <div class="bg-green-50 p-3 rounded-lg">
                <h4 class="font-semibold text-green-900 mb-1">📈 DESPUÉS (ANÁLISIS):</h4>
                <ul class="text-sm text-green-800 ml-4 space-y-1">
                    <li>• Compara estimado vs real por parcela</li>
                    <li>• Identifica parcelas sobre/infra productivas</li>
                    <li>• Optimiza próxima campaña con datos reales</li>
                </ul>
            </div>
        </div>
        <p class="text-gray-700 leading-relaxed bg-amber-50 border-l-4 border-amber-500 p-3 rounded">
            <strong>Ejemplo:</strong> Estimaste 8,000 kg/ha en Parcela A pero cosechaste solo 6,500 kg/ha. Agro365 te alerta para revisar riego, poda o fertilización en la próxima temporada.
        </p>
    </div>
</div>

<!-- FAQ 5: Precio -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(5) ? openIndexes = openIndexes.filter(i => i !== 5) : openIndexes.push(5)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Cuánto cuesta Agro365 realmente?
            </h3>
            <p class="text-sm text-gray-500">6 meses gratis + precios especiales beta</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(5) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(5)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-3">
            Agro365 ofrece <strong>6 meses completamente gratis</strong> para todos los usuarios beta. Después del período gratuito:
        </p>
        <ul class="space-y-2 text-gray-700">
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Plan Mensual:</strong> €9/mes (precio con descuento beta del 25%)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Plan Anual:</strong> €90/año (equivalente a €7.50/mes, ahorra €18 al año)</span>
            </li>
        </ul>
        <p class="text-gray-700 leading-relaxed mt-3">
            <strong>Sin tarjeta requerida</strong> para comenzar la prueba. Cancela en cualquier momento sin compromiso.
        </p>
    </div>
</div>

<!-- FAQ 6: Móvil -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(6) ? openIndexes = openIndexes.filter(i => i !== 6) : openIndexes.push(6)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Puedo usar Agro365 desde el móvil en el viñedo?
            </h3>
            <p class="text-sm text-gray-500">100% responsive y optimizado</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(6) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(6)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed">
            ¡Por supuesto! Agro365 es una <strong><a href="{{ route('content.app-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">app de agricultura</a></strong> <strong>100% optimizada para móviles y tablets</strong>. Funciona como una aplicación web responsive, por lo que puedes acceder desde cualquier navegador sin necesidad de instalar apps. Registra tratamientos, riegos y actividades directamente desde el viñedo, incluso con conexión limitada. Los datos se sincronizan automáticamente cuando recuperas señal. Diseñado específicamente para funcionar en condiciones reales de campo.
        </p>
    </div>
</div>

<!-- FAQ 7: Seguridad -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(7) ? openIndexes = openIndexes.filter(i => i !== 7) : openIndexes.push(7)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Mis datos están seguros en Agro365?
            </h3>
            <p class="text-sm text-gray-500">RGPD compliant + cifrado bancario</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(7) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(7)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed">
            Absolutamente. Tus datos están protegidos con <strong>cifrado HTTPS de nivel bancario</strong> y almacenados en servidores seguros europeos. Cumplimos estrictamente con el <strong>RGPD</strong> (Reglamento General de Protección de Datos). Realizamos backups automáticos diarios para que nunca pierdas información. Solo tú tienes acceso a tus datos agrícolas, y nunca los compartimos con terceros. Puedes exportar o eliminar tu información en cualquier momento. Tu privacidad es nuestra prioridad.
        </p>
    </div>
</div>

<!-- FAQ 8: Cuaderno Digital Obligatorio -->
<div class="group glass-card rounded-2xl overflow-hidden border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300 hover-lift">
    <button @click="openIndexes.includes(8) ? openIndexes = openIndexes.filter(i => i !== 8) : openIndexes.push(8)" class="w-full px-8 py-6 flex items-start gap-4 text-left hover:bg-gradient-to-r hover:from-[var(--color-agro-green)]/5 hover:to-transparent transition-all">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center shadow-md flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-1 group-hover:text-[var(--color-agro-green)] transition-colors">
                ¿Es obligatorio el cuaderno de campo digital?
            </h3>
            <p class="text-sm text-gray-500">Normativa 2023/2027 - Cumplimiento garantizado</p>
        </div>
        <svg class="w-7 h-7 text-[var(--color-agro-green)] flex-shrink-0 transition-transform mt-1" :class="{ 'rotate-180': openIndexes.includes(8) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openIndexes.includes(8)" x-collapse class="px-8 pb-8 pt-2">
        <p class="text-gray-700 leading-relaxed mb-3">
            Sí. El <strong><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a></strong> es obligatorio en España según la normativa actual. Para <a href="{{ route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores profesionales</a>:
        </p>
        <ul class="space-y-2 text-gray-700 mb-4">
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Desde 2023:</strong> Obligatorio para explotaciones profesionales</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Desde 2027:</strong> DEBE estar digitalizado (normativa europea)</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Obligatorio registrar:</strong> Tratamientos fitosanitarios, riegos, fertilizaciones, labores culturales</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Inspecciones PAC:</strong> Pueden solicitarte el cuaderno en cualquier momento</span>
            </li>
        </ul>
        <p class="text-gray-700 leading-relaxed bg-green-50 border-l-4 border-green-500 p-3 rounded">
            <strong>✅ Agro365 cumple 100%</strong> con todos los requisitos legales y te prepara para la normativa 2027. Evita multas y sanciones PAC con un cuaderno siempre actualizado y conforme.
        </p>
    </div>
</div>
