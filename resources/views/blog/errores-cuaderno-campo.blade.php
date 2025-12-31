<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>5 Errores Comunes en el Cuaderno de Campo | Blog Agro365</title>
    <meta name="description" content="Evita sanciones: los 5 errores más frecuentes que cometen los viticultores al llevar el cuaderno de campo digital y cómo evitarlos.">
    <meta name="keywords" content="errores cuaderno campo, sanciones PAC, cuaderno digital errores, multas agricultura, registro fitosanitarios errores">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog/errores-cuaderno-campo') }}">
    <meta property="og:title" content="5 Errores en el Cuaderno de Campo - Blog Agro365">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                @endguest
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 mb-8">
                <a href="{{ url('/blog') }}" class="hover:text-[var(--color-agro-green)]">Blog</a> → 
                <span>Errores Cuaderno de Campo</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500">Diciembre 2024</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">
                        5 Errores Comunes en el Cuaderno de Campo
                    </h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    El <a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a> es obligatorio para recibir ayudas PAC. Estos son los errores más habituales que pueden costarte una sanción.
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">❌ Error 1: No vincular tratamientos a parcelas SIGPAC</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Cada <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamiento fitosanitario</a> debe estar vinculado a parcelas concretas con su código <a href="{{ route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a>. Registrar tratamientos "genéricos" sin parcela es motivo de sanción.
                </p>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg mb-6">
                    <p class="text-gray-700">✅ <strong>Solución:</strong> En Agro365, al crear una actividad se seleccionan automáticamente las parcelas afectadas.</p>
                </div>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">❌ Error 2: Registrar fuera de plazo</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    El registro debe hacerse en un plazo máximo de 1 mes desde la realización de la actividad. Muchos agricultores acumulan y registran todo junto al final de la campaña.
                </p>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg mb-6">
                    <p class="text-gray-700">✅ <strong>Solución:</strong> Agro365 permite registrar desde el móvil en el campo, en menos de 1 minuto.</p>
                </div>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">❌ Error 3: Omitir datos obligatorios en tratamientos</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Un tratamiento debe incluir: producto, dosis, superficie, justificación, número ROPO del aplicador y plazo de seguridad. La omisión de cualquiera es sancionable.
                </p>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg mb-6">
                    <p class="text-gray-700">✅ <strong>Solución:</strong> Agro365 valida que todos los campos obligatorios estén completos antes de guardar.</p>
                </div>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">❌ Error 4: No registrar el agua de riego</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Si riegas, debes registrar fuente de agua, concesión y caudal. Este requisito se incumple con frecuencia.
                </p>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg mb-6">
                    <p class="text-gray-700">✅ <strong>Solución:</strong> Agro365 incluye campos específicos para gestión de riego con todos los datos requeridos.</p>
                </div>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">❌ Error 5: No poder demostrar el registro</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    En una inspección, debes poder demostrar cuándo se hizo el registro. Un cuaderno en papel o Excel no tiene sello de tiempo verificable.
                </p>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg mb-6">
                    <p class="text-gray-700">✅ <strong>Solución:</strong> Agro365 genera <a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">informes oficiales</a> con firma digital SHA-256 y timestamp verificable.</p>
                </div>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">📋 Evita sanciones con Agro365</h3>
                    <p class="text-gray-700 mb-6">
                        Cuaderno digital con validación automática. <strong>6 meses gratis</strong>.
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </article>

            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ url('/blog') }}" class="text-[var(--color-agro-green)] font-semibold hover:underline">← Volver al Blog</a>
            </div>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>
