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
                </div>
                <p class="text-white/70 mb-4 text-sm">
                    La plataforma que conecta viticultores, bodegas y Denominaciones de Origen en España.
                    Cuaderno de campo digital obligatorio 2027.
                </p>
                <div class="flex items-center gap-2 text-white/60 text-sm mb-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>España</span>
                </div>
                <a href="tel:+34684217167" class="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors mb-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    +34 684 217 167
                </a>
                <a href="https://wa.me/34684217167" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors mb-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
                <a href="mailto:{{ config('app.legal_contact_email', 'info@agro365.es') }}" class="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors mb-4">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ config('app.legal_contact_email', 'info@agro365.es') }}
                </a>
                @guest
                    <a href="{{ route('register') }}" rel="nofollow" class="inline-block px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm font-semibold">
                        Comenzar Gratis
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
                    <li><a href="{{ content_route('content.software-viticultura') }}" class="hover:text-white transition-colors">Software Viticultura</a></li>
                    <li><a href="{{ content_route('content.cuaderno-digital') }}" class="hover:text-white transition-colors">Cuaderno Digital</a></li>
                    <li><a href="{{ content_route('content.que-es-sigpac') }}" class="hover:text-white transition-colors">Gestión SIGPAC</a></li>
                    <li><a href="{{ content_route('content.gestion-vendimia') }}" class="hover:text-white transition-colors">Gestión Vendimia</a></li>
                    <li><a href="{{ content_route('content.facturacion-agricola') }}" class="hover:text-white transition-colors">Facturación</a></li>
                    <li><a href="{{ content_route('content.ndvi-teledeteccion') }}" class="hover:text-white transition-colors">NDVI Teledetección</a></li>
                    <li><a href="{{ content_route('content.trazabilidad-agricola') }}" class="hover:text-white transition-colors">Trazabilidad</a></li>
                </ul>
            </div>

            <!-- Sectores -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Sectores</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ content_route('content.viticultores') }}" class="hover:text-white transition-colors">Viticultores</a></li>
                    <li><a href="{{ content_route('content.software-bodegas') }}" class="hover:text-white transition-colors">Bodegas</a></li>
                    <li><a href="#ecosistema" class="hover:text-white transition-colors">Denominaciones de Origen</a></li>
                    <li><a href="{{ content_route('content.cooperativas') }}" class="hover:text-white transition-colors">Cooperativas</a></li>
                    <li><a href="{{ content_route('content.ingenieros-agronomos') }}" class="hover:text-white transition-colors">Ingenieros Agrónomos</a></li>
                    <li><a href="{{ content_route('content.app-agricultura') }}" class="hover:text-white transition-colors">Apps para el Campo</a></li>
                </ul>
            </div>
            
            <!-- Regiones -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Regiones</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ content_route('content.viticultores-rioja') }}" class="hover:text-white transition-colors">DOCa Rioja</a></li>
                    <li><a href="{{ content_route('content.viticultores-ribera') }}" class="hover:text-white transition-colors">Ribera del Duero</a></li>
                    <li><a href="{{ content_route('content.viticultores-rueda') }}" class="hover:text-white transition-colors">DO Rueda</a></li>
                    <li><a href="{{ content_route('content.viticultores-priorat') }}" class="hover:text-white transition-colors">DOQ Priorat</a></li>
                    <li><a href="{{ content_route('content.viticultores-rias-baixas') }}" class="hover:text-white transition-colors">Rías Baixas</a></li>
                    <li><a href="{{ content_route('content.viticultores-penedes') }}" class="hover:text-white transition-colors">DO Penedès</a></li>
                    <li><a href="{{ content_route('content.viticultores-la-mancha') }}" class="hover:text-white transition-colors">DO La Mancha</a></li>
                    <li><a href="{{ content_route('content.viticultores-toro') }}" class="hover:text-white transition-colors">DO Toro</a></li>
                    <li><a href="{{ content_route('content.viticultores-jumilla') }}" class="hover:text-white transition-colors">DO Jumilla</a></li>
                </ul>
            </div>
            
            <!-- Recursos & Legal -->
            <div>
                <h4 class="font-semibold text-lg mb-4">Contacto</h4>
                <p class="text-white/70 text-sm mb-4">
                    ¿Dudas? Escríbenos:
                </p>
                <a href="mailto:{{ config('app.legal_contact_email', 'info@agro365.es') }}" class="text-white font-medium hover:underline transition-colors">
                    {{ config('app.legal_contact_email', 'info@agro365.es') }}
                </a>
                
                <h4 class="font-semibold text-lg mb-4 mt-6">Recursos</h4>
                <ul class="space-y-2 text-white/70 text-sm mb-6">
                    <li><a href="{{ url('/precios') }}" class="hover:text-white transition-colors">Precios</a></li>
                    <li><a href="{{ route('faqs') }}" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog Agro365</a></li>
                    <li><a href="{{ content_route('content.normativa-pac') }}" class="hover:text-white transition-colors">Normativa PAC</a></li>
                    <li><a href="{{ content_route('content.comparativa') }}" class="hover:text-white transition-colors">Comparativa Software</a></li>
                    <li><a href="{{ content_route('content.informes-oficiales') }}" class="hover:text-white transition-colors">Informes Oficiales</a></li>
                </ul>
                
                <h4 class="font-semibold text-lg mb-4">Legal</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('aviso-legal') }}" class="hover:text-white transition-colors">Aviso Legal</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacidad</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Términos</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                <p class="text-white/60">
                    &copy; {{ date('Y') }} Agro365. Todos los derechos reservados.
                    @if(config('app.legal_owner_name'))
                        <span class="text-white/50"> · Titular: {{ config('app.legal_owner_name') }}</span>
                    @endif
                </p>
                <p class="text-white/50">
                    Software para viticultores, bodegas y Denominaciones de Origen
                </p>
            </div>
            
            <!-- SEO Keywords Footer -->
            <div class="mt-6 pt-6 border-t border-white/10">
                <p class="text-white/40 text-xs text-center leading-relaxed">
                    <strong class="text-white/50">Agro365</strong> — Software de gestión agrícola para viticultores, bodegas y Denominaciones de Origen en España · Cuaderno de campo digital obligatorio 2027 · Gestión de parcelas SIGPAC · Teledetección NDVI para viñedos · Facturación Verifactu integrada · Informes oficiales con firma electrónica SHA-256 · Dashboard de cumplimiento PAC · Trazabilidad vitivinícola completa del viñedo a la botella · Gestión de vendimia y contenedores · Libros de bodega, AICA e INFOVI · Denominaciones de Origen · Digitalización agrícola España
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
    "description": "Plataforma de gestión agrícola para viticultores, bodegas y Denominaciones de Origen en España. Cuaderno de campo digital obligatorio 2027.",
    "foundingDate": "2024",
    "contactPoint": {
        "@@type": "ContactPoint",
        "telephone": "+34-684-217-167",
        "email": "{{ config('app.legal_contact_email', 'info@agro365.es') }}",
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



