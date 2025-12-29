<footer class="bg-[var(--color-agro-green-dark)] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-5 gap-8 mb-12">
            <!-- Company Info -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold">Agro365</span>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30">
                        BETA
                    </span>
                </div>
                <p class="text-white/70 mb-4 text-sm">
                    Software de gestión agrícola profesional para viticultores en España.
                </p>
                <div class="flex items-center gap-2 text-white/60 text-sm mb-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>España</span>
                </div>
                @guest
                    <a href="{{ route('register') }}" rel="nofollow" class="inline-block px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm font-semibold">
                        Prueba Gratis 6 Meses
                    </a>
                @else
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="inline-block px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm font-semibold">
                        Ir al Dashboard
                    </a>
                @endguest
            </div>
            
            <!-- Producto -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Producto</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('content.software-viticultores') }}" class="hover:text-white transition-colors">Software Viticultores</a></li>
                    <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="hover:text-white transition-colors">Cuaderno Digital</a></li>
                    <li><a href="{{ route('content.sigpac') }}" class="hover:text-white transition-colors">Gestión SIGPAC</a></li>
                    <li><a href="{{ url('/gestion-vendimia') }}" class="hover:text-white transition-colors">Gestión Vendimia</a></li>
                    <li><a href="{{ url('/facturacion-agricola') }}" class="hover:text-white transition-colors">Facturación</a></li>
                    <li><a href="{{ url('/ndvi-viñedo-teledeteccion') }}" class="hover:text-white transition-colors">NDVI Teledetección</a></li>
                </ul>
            </div>
            
            <!-- Regiones -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Regiones</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ url('/software-viticultores-rioja') }}" class="hover:text-white transition-colors">DOCa Rioja</a></li>
                    <li><a href="{{ url('/software-viticultores-ribera-duero') }}" class="hover:text-white transition-colors">Ribera del Duero</a></li>
                    <li><a href="{{ url('/software-viticultores-rueda') }}" class="hover:text-white transition-colors">DO Rueda</a></li>
                    <li><a href="{{ url('/software-viticultores-priorat') }}" class="hover:text-white transition-colors">DOQ Priorat</a></li>
                    <li><a href="{{ url('/software-viticultores-rias-baixas') }}" class="hover:text-white transition-colors">Rías Baixas</a></li>
                    <li><a href="{{ url('/software-viticultores-penedes') }}" class="hover:text-white transition-colors">DO Penedès</a></li>
                    <li><a href="{{ url('/software-viticultores-la-mancha') }}" class="hover:text-white transition-colors">DO La Mancha</a></li>
                    <li><a href="{{ url('/software-viticultores-toro') }}" class="hover:text-white transition-colors">DO Toro</a></li>
                    <li><a href="{{ url('/software-viticultores-jumilla') }}" class="hover:text-white transition-colors">DO Jumilla</a></li>
                </ul>
            </div>
            
            <!-- Guías y Recursos -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Recursos</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('faqs') }}" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                    <li><a href="{{ route('content.normativa-pac') }}" class="hover:text-white transition-colors">Normativa PAC</a></li>
                    <li><a href="{{ url('/subvenciones-pac-2024') }}" class="hover:text-white transition-colors">Subvenciones PAC</a></li>
                    <li><a href="{{ url('/registro-fitosanitarios') }}" class="hover:text-white transition-colors">Fitosanitarios</a></li>
                    <li><a href="{{ route('content.app-agricultura') }}" class="hover:text-white transition-colors">App Agricultura</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                </ul>
            </div>
            
            <!-- Legal & Contacto -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Legal</h4>
                <ul class="space-y-2 text-white/70 text-sm mb-6">
                    <li><a href="{{ route('aviso-legal') }}" class="hover:text-white transition-colors">Aviso Legal</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Política de Privacidad</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Términos y Condiciones</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Política de Cookies</a></li>
                </ul>
                
                <h4 class="font-semibold text-lg mb-4">Contacto</h4>
                <div class="space-y-3 text-white/70 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:info@agro365.es" class="hover:text-white transition-colors">info@agro365.es</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                <p class="text-white/60">
                    &copy; {{ date('Y') }} Agro365. Todos los derechos reservados.
                </p>
                <p class="text-white/50">
                    Software gestión agrícola para viticultores profesionales · Versión Beta
                </p>
            </div>
            
            <!-- SEO Keywords Footer -->
            <div class="mt-6 pt-6 border-t border-white/10">
                <p class="text-white/40 text-xs text-center leading-relaxed">
                    <strong class="text-white/50">Agro365</strong> - Software de gestión agrícola profesional · Cuaderno de campo digital obligatorio 2027 · Gestión de parcelas SIGPAC · Informes oficiales con firma electrónica · Dashboard de cumplimiento PAC · Control de vendimia y cosechas · Facturación integrada · Gestión de productos fitosanitarios · Trazabilidad completa · Software para viticultores en España · Cumplimiento normativo PAC · Digitalización agrícola
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Schema.org Organization -->
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "Agro365",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/logo.png') }}",
    "description": "Plataforma de gestión agrícola profesional para viticultores y bodegas",
    "foundingDate": "2024",
    "contactPoint": {
        "@@type": "ContactPoint",
        "email": "info@agro365.es",
        "contactType": "customer service",
        "availableLanguage": ["Spanish"],
        "areaServed": "ES"
    },
    "address": {
        "@@type": "PostalAddress",
        "addressCountry": "ES",
        "addressRegion": "España"
    }
}
</script>



