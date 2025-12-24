<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Términos y Condiciones - Agro365</title>
    <meta name="description" content="Términos y condiciones de uso de Agro365, plataforma de gestión agrícola para viticultores">
    <meta name="robots" content="index, follow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://agro365.es{{ request()->getRequestUri() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    
    <!-- Navigation -->
    <nav class="glass-card border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="{{ url('/') }}" class="flex items-center">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365" 
                        width="200"
                        height="80"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="h-20 w-auto object-contain"
                    >
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] font-semibold transition-colors">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all shadow-lg hover:shadow-xl font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card rounded-2xl p-8 lg:p-12">
                <h1 class="text-4xl font-bold text-[var(--color-agro-green-dark)] mb-4">Términos y Condiciones</h1>
                <p class="text-gray-500 mb-8">Última actualización: {{ date('d/m/Y') }}</p>
                
                <div class="prose prose-lg max-w-none space-y-8">
                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">1. Aceptación de los Términos</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Al acceder y utilizar Agro365 ("el Servicio"), usted acepta estar sujeto a estos Términos y Condiciones. Si no está de acuerdo con alguno de estos términos, no debe utilizar el Servicio.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Agro365 es una plataforma de gestión agrícola en fase beta diseñada para viticultores y gestores de explotaciones agrícolas.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">2. Programa Beta</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Agro365 se encuentra actualmente en fase beta. Esto significa que:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>El servicio puede contener errores o bugs</li>
                            <li>Algunas funcionalidades pueden cambiar sin previo aviso</li>
                            <li>Los primeros 50 usuarios reciben 6 meses gratis más 25% de descuento permanente</li>
                            <li>Nos reservamos el derecho de modificar características durante la fase beta</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">3. Uso del Servicio</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Usted se compromete a:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>Proporcionar información precisa y veraz durante el registro</li>
                            <li>Mantener la seguridad de su cuenta y contraseña</li>
                            <li>No compartir su cuenta con terceros</li>
                            <li>Utilizar el Servicio únicamente para fines legales y agrícolas</li>
                            <li>No intentar acceder a áreas restringidas del sistema</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">4. Propiedad Intelectual</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Todo el contenido del Servicio, incluyendo pero no limitado a texto, gráficos, logos, iconos, imágenes, clips de audio, descargas digitales y compilaciones de datos, es propiedad de Agro365 y está protegido por las leyes de propiedad intelectual.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Los datos que usted ingresa (parcelas, actividades, cosechas, etc.) son de su propiedad y permanecerán confidencial es.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">5. Protección de Datos</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Nos comprometemos a proteger sus datos personales de acuerdo con el RGPD (Reglamento General de Protección de Datos) y la legislación española aplicable. 
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Para más información, consulte nuestra <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] underline hover:text-[var(--color-agro-green)]">Política de Privacidad</a>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">6. Limitación de Responsabilidad</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Agro365 se proporciona "tal cual" sin garantías de ningún tipo. No garantizamos que:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>El servicio esté libre de errores o interrupciones</li>
                            <li>Los datos calculados (como rendimientos estimados) sean 100% precisos</li>
                            <li>El servicio cumpla con todos sus requisitos específicos</li>
                        </ul>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            Usted es responsable de mantener copias de seguridad de sus datos críticos.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">7. Precios y Pagos</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Los precios actuales son:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>6 meses completamente gratis para todos los beta testers</li>
                            <li>Después: €9/mes o €90/año (Plan Mensual o Anual)</li>
                            <li>Primeros 50 usuarios: 25% de descuento permanente (€9→€6.75/mes o €90→€67.50/año)</li>
                        </ul>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            Nos reservamos el derecho de modificar los precios con 30 días de aviso previo. Los descuentos especiales se mantendrán según lo prometido.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">8. Cancelación</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Puede cancelar su suscripción en cualquier momento desde su panel de control o contactando con soporte. No se realizan reembolsos por períodos parciales ya pagados.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">9. Modificaciones</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Nos reservamos el derecho de modificar estos términos en cualquier momento. Le notificaremos los cambios significativos por email al menos 15 días antes de que entren en vigor.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">10. Contacto</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Para cualquier duda sobre estos términos, puede contactarnos en:
                        </p>
                        <ul class="list-none text-gray-700 space-y-2 mt-4">
                            <li><strong>Email:</strong> <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] underline">info@agro365.es</a></li>
                            <li><strong>Soporte:</strong> <a href="mailto:soporte@agro365.es" class="text-[var(--color-agro-green-dark)] underline">soporte@agro365.es</a></li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Simple Footer -->
    <footer class="bg-[var(--color-agro-green-dark)] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-white/80">
                <a href="{{ url('/') }}" class="hover:text-white transition-colors">Volver al inicio</a>
                <span class="mx-4">·</span>
                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacidad</a>
                <span class="mx-4">·</span>
                <a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a>
            </p>
            <p class="text-white/60 mt-4 text-sm">&copy; {{ date('Y') }} Agro365. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
