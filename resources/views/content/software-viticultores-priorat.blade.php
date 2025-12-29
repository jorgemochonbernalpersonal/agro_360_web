<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Software para Viticultores en DOCa Priorat - Gestión de Viñedos | Agro365</title>
    <meta name="description" content="Software especializado para viticultores de DOCa Priorat. Gestiona Tempranillo, Garnacha y Viura. Control de rendimientos 6.000 kg/ha, cumplimiento Consejo Regulador y cuaderno de campo digital.">
    <meta name="keywords" content="software viticultores rioja, cuaderno campo rioja, gestión viñedo rioja, tempranillo rioja, DOCa rioja, consejo regulador rioja, rendimientos rioja, heladas rioja alavesa">
    <meta name="robots" content="index, follow">
    
    <!-- Canonical & Open Graph -->
    <link rel="canonical" href="{{ url('/software-viticultores-rioja') }}">
    <meta property="og:title" content="Software para Viticultores en DOCa Priorat - Agro365">
    <meta property="og:description" content="Gestión especializada para viñedos de Priorat. Control de rendimientos, heladas y cumplimiento normativo.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:url" content="{{ url('/software-viticultores-rioja') }}">
    
    <!-- Favicon & Fonts -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)]">Inicio</a>
                    @guest
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a> → 
                <a href="{{ route('content.software-viticultores') }}" class="hover:text-[var(--color-agro-green)]">Software Viticultores</a> → 
                <span class="text-gray-700">Priorat</span>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🍷</span>
                    <span class="text-sm font-semibold text-red-800">DOCa Priorat - Denominación de Origen Calificada</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Priorat
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestiona tus viñedos en la <strong>DOCa Priorat</strong> con Agro365. Cuaderno de campo digital, control PAC y cumplimiento del Consejo Regulador. Más de <strong>1.900 hectáreas</strong> de viñedo en la primera Denominación de Origen Calificada de España.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <!-- Características de la DOCa Priorat -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">La DOCa Priorat: Primera Denominación Calificada de España</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-red-50 to-white p-6 rounded-xl border border-red-200">
                            <h3 class="font-bold text-xl text-red-800 mb-4">📊 Datos de la DO</h3>
                            <ul class="space-y-2 text-gray-700">
                                <li><strong>Superficie:</strong> 1.900 hectáreas</li>
                                <li><strong>Bodegas:</strong> 100+ bodegas registradas</li>
                                <li><strong>Viticultores:</strong> 600+ viticultores</li>
                                <li><strong>Producción anual:</strong> 5 millones de litros</li>
                                <li><strong>Zonas:</strong> Priorat Alta, Priorat Alavesa, Priorat Oriental</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-4">🍇 Variedades Autorizadas</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="font-semibold text-gray-800">Tintas (90%):</p>
                                    <ul class="text-sm text-gray-700 ml-4 mt-1">
                                        <li>• Garnacha y Cariñena (70%) - Variedad principal</li>
                                        <li>• Garnacha (12%)</li>
                                        <li>• Mazuelo, Graciano (3%)</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Blancas (10%):</p>
                                    <ul class="text-sm text-gray-700 ml-4 mt-1">
                                        <li>• Viura (Macabeo) - Principal</li>
                                        <li>• Malvasía, Garnacha Blanca</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-6">
                        <p class="text-gray-800">
                            <strong>Clima Continental Moderado:</strong> Inviernos fríos con riesgo de heladas, veranos cálidos y secos. Precipitación media de 400-500mm anuales. La Sierra de Cantabria protege Priorat Alavesa de vientos del norte.
                        </p>
                    </div>
                </section>

                <!-- Desafíos Específicos de Priorat -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Desafíos de los Viticultores en Priorat</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <div class="flex items-start gap-4">
                                <div class="text-4xl">❄️</div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-xl text-gray-900 mb-2">1. Heladas Tardías de Primavera</h3>
                                    <p class="text-gray-700 mb-3">
                                        Las heladas de abril-mayo son el mayor riesgo en Priorat Alavesa y Alta. Pueden destruir brotes recién formados y reducir la cosecha hasta un 80%.
                                    </p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        <p class="font-semibold text-green-800 mb-2">✅ Solución con Agro365:</p>
                                        <ul class="text-sm text-gray-700 space-y-1">
                                            <li>• Registra fechas de brotación por parcela</li>
                                            <li>• Alertas meteorológicas automáticas</li>
                                            <li>• Planifica activación de sistemas antiheladas</li>
                                            <li>• Histórico de heladas por parcela SIGPAC</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <div class="flex items-start gap-4">
                                <div class="text-4xl">🌧️</div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-xl text-gray-900 mb-2">2. Granizo en Época de Maduración</h3>
                                    <p class="text-gray-700 mb-3">
                                        Tormentas de granizo en julio-agosto pueden dañar racimos y hojas. Priorat Oriental es especialmente vulnerable.
                                    </p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        <p class="font-semibold text-green-800 mb-2">✅ Solución con Agro365:</p>
                                        <ul class="text-sm text-gray-700 space-y-1">
                                            <li>• Registro de daños por parcela para seguros</li>
                                            <li>• Cálculo automático de pérdidas de producción</li>
                                            <li>• Documentación fotográfica geolocalizada</li>
                                            <li>• Informes oficiales para peritajes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <div class="flex items-start gap-4">
                                <div class="text-4xl">📋</div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-xl text-gray-900 mb-2">3. Cumplimiento del Consejo Regulador</h3>
                                    <p class="text-gray-700 mb-3">
                                        El Consejo Regulador de Priorat exige rendimientos máximos estrictos: <strong>6.000 kg/ha para crianza</strong> y <strong>9.000 kg/ha para joven</strong>. Superar estos límites descalifica la cosecha.
                                    </p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        <p class="font-semibold text-green-800 mb-2">✅ Solución con Agro365:</p>
                                        <ul class="text-sm text-gray-700 space-y-1">
                                            <li>• Cálculo automático de rendimiento por parcela</li>
                                            <li>• Alertas si te acercas al límite</li>
                                            <li>• Proyección de producción en tiempo real</li>
                                            <li>• Informes para el Consejo Regulador</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <div class="flex items-start gap-4">
                                <div class="text-4xl">🦠</div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-xl text-gray-900 mb-2">4. Mildiu y Oidio</h3>
                                    <p class="text-gray-700 mb-3">
                                        Primaveras húmedas favorecen el mildiu. El oidio es persistente en Priorat Alta. Ambos requieren tratamientos preventivos precisos.
                                    </p>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                        <p class="font-semibold text-green-800 mb-2">✅ Solución con Agro365:</p>
                                        <ul class="text-sm text-gray-700 space-y-1">
                                            <li>• Base de datos de fitosanitarios autorizados</li>
                                            <li>• Registro obligatorio de tratamientos (ROPO)</li>
                                            <li>• Alertas de plazo de seguridad antes de vendimia</li>
                                            <li>• Histórico de tratamientos por parcela</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Funcionalidades Clave para Priorat -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Funcionalidades Clave para Viticultores de Priorat</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">📱</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Cuaderno de Campo Digital</h3>
                            <p class="text-gray-700 text-sm">
                                Obligatorio desde 2027. Registra todas las actividades por parcela SIGPAC: laboreo, tratamientos, riego, vendimia. Cumple con PAC y Consejo Regulador.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">🗺️</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión SIGPAC Priorat</h3>
                            <p class="text-gray-700 text-sm">
                                Importa tus parcelas SIGPAC de La Priorat, Álava y Navarra. Visualiza en mapa, calcula superficies exactas y mantén actualizado el registro.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">🍇</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Control de Vendimia</h3>
                            <p class="text-gray-700 text-sm">
                                Registra peso, grado, acidez por parcela y variedad. Calcula rendimientos automáticamente. Genera albaranes para bodegas con trazabilidad completa.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">📄</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Informes Oficiales</h3>
                            <p class="text-gray-700 text-sm">
                                Genera informes con firma digital para inspecciones PAC, Consejo Regulador o certificaciones ecológicas. Exporta en PDF oficial.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">💧</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión de Riego</h3>
                            <p class="text-gray-700 text-sm">
                                Aunque Priorat es mayormente secano, registra riegos de apoyo en parcelas autorizadas. Control de concesiones y volúmenes aplicados.
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-xl border border-green-200">
                            <div class="text-3xl mb-3">👥</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión de Cuadrillas</h3>
                            <p class="text-gray-700 text-sm">
                                Organiza el trabajo de vendimia, poda y laboreo. Asigna cuadrillas a parcelas, controla jornadas y genera partes de trabajo.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Normativa Específica de Priorat -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Normativa del Consejo Regulador de Priorat</h2>
                    
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-6">
                        <h3 class="font-bold text-lg text-red-800 mb-3">Rendimientos Máximos Autorizados</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold">Tipo de Vino</th>
                                        <th class="px-4 py-2 text-left font-semibold">Rendimiento Máximo</th>
                                        <th class="px-4 py-2 text-left font-semibold">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    <tr class="border-t border-red-200">
                                        <td class="px-4 py-2">Vino Joven</td>
                                        <td class="px-4 py-2 font-semibold">9.000 kg/ha</td>
                                        <td class="px-4 py-2">Sin crianza en barrica</td>
                                    </tr>
                                    <tr class="border-t border-red-200 bg-red-50">
                                        <td class="px-4 py-2">Crianza, Reserva, Gran Reserva</td>
                                        <td class="px-4 py-2 font-semibold">6.000 kg/ha</td>
                                        <td class="px-4 py-2">Requisito obligatorio</td>
                                    </tr>
                                    <tr class="border-t border-red-200">
                                        <td class="px-4 py-2">Vinos Blancos</td>
                                        <td class="px-4 py-2 font-semibold">10.000 kg/ha</td>
                                        <td class="px-4 py-2">Todas las categorías</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-sm text-red-700 mt-4">
                            ⚠️ <strong>Importante:</strong> Superar estos rendimientos descalifica la uva para la DO Priorat. Agro365 calcula automáticamente el rendimiento de cada parcela.
                        </p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg">
                        <h3 class="font-bold text-lg text-yellow-800 mb-3">Otras Obligaciones del Consejo Regulador</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li>✓ <strong>Declaración de cosecha:</strong> Antes del 31 de octubre</li>
                            <li>✓ <strong>Registro de movimientos:</strong> Entradas y salidas de uva/vino</li>
                            <li>✓ <strong>Libro de viticultura:</strong> Todas las prácticas culturales</li>
                            <li>✓ <strong>Trazabilidad completa:</strong> De viñedo a botella</li>
                        </ul>
                        <p class="text-sm text-yellow-700 mt-4">
                            💡 Agro365 genera automáticamente todos estos registros desde el cuaderno de campo digital.
                        </p>
                    </div>
                </section>

                <!-- CTA Final -->
                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🍷 Gestiona tu Viñedo en Priorat con Agro365</h3>
                        <p class="text-gray-700 mb-6">
                            Únete a cientos de viticultores de Priorat que ya usan Agro365. Cuaderno de campo digital, control de rendimientos, cumplimiento del Consejo Regulador y <strong>6 meses gratis</strong> para probar.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                                Comenzar Gratis - 6 Meses
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                            <a href="{{ route('faqs') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-white border-2 border-[var(--color-agro-green)] text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-semibold">
                                Ver Preguntas Frecuentes
                            </a>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>

    @include('partials.footer-seo')

    <!-- Breadcrumb Schema.org -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@@type": "ListItem", "position": 2, "name": "Software Viticultores", "item": "{{ route('content.software-viticultores') }}"},
            {"@@type": "ListItem", "position": 3, "name": "Priorat", "item": "{{ url('/software-viticultores-rioja') }}"}
        ]
    }
    </script>
</body>
</html>
