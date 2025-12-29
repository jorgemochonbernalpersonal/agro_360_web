<footer class="bg-[var(--color-agro-green-dark)] text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-5 gap-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-xl font-bold">Agro365</span>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30">BETA</span>
                </div>
                <p class="text-white/70 text-sm mb-4">
                    Software de gestión agrícola para viticultores en España.
                </p>
                <a href="{{ route('register') }}" class="inline-block px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm font-semibold">
                    Prueba Gratis 6 Meses
                </a>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Producto</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('content.software-viticultores') }}" class="hover:text-white transition-colors">Software Viticultores</a></li>
                    <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="hover:text-white transition-colors">Cuaderno Digital</a></li>
                    <li><a href="{{ url('/gestion-vendimia') }}" class="hover:text-white transition-colors">Gestión Vendimia</a></li>
                    <li><a href="{{ url('/facturacion-agricola') }}" class="hover:text-white transition-colors">Facturación</a></li>
                    <li><a href="{{ url('/ndvi-viñedo-teledeteccion') }}" class="hover:text-white transition-colors">NDVI Teledetección</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Regiones</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ url('/software-viticultores-rioja') }}" class="hover:text-white transition-colors">DOCa Rioja</a></li>
                    <li><a href="{{ url('/software-viticultores-ribera-duero') }}" class="hover:text-white transition-colors">Ribera del Duero</a></li>
                    <li><a href="{{ url('/software-viticultores-rueda') }}" class="hover:text-white transition-colors">DO Rueda</a></li>
                    <li><a href="{{ url('/software-viticultores-priorat') }}" class="hover:text-white transition-colors">DOQ Priorat</a></li>
                    <li><a href="{{ url('/software-viticultores-rias-baixas') }}" class="hover:text-white transition-colors">Rías Baixas</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Guías</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('content.sigpac') }}" class="hover:text-white transition-colors">Qué es SIGPAC</a></li>
                    <li><a href="{{ route('content.normativa-pac') }}" class="hover:text-white transition-colors">Normativa PAC</a></li>
                    <li><a href="{{ url('/subvenciones-pac-2024') }}" class="hover:text-white transition-colors">Subvenciones PAC</a></li>
                    <li><a href="{{ url('/registro-fitosanitarios') }}" class="hover:text-white transition-colors">Fitosanitarios</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Empresa</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('faqs') }}" class="hover:text-white transition-colors">FAQs</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacidad</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Términos</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-white/10 mt-8 pt-8 text-center text-white/70 text-sm">
            <p>&copy; {{ date('Y') }} Agro365. Software de gestión agrícola para viticultores en España.</p>
        </div>
    </div>
</footer>



