<footer class="bg-[var(--color-agro-green-dark)] text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-xl font-bold">Agro365</span>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30">BETA</span>
                </div>
                <p class="text-white/70 mb-4">
                    Software de gestión agrícola profesional para viticultores en España.
                </p>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Enlaces</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Inicio</a></li>
                    <li><a href="{{ route('faqs') }}" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                    <li><a href="{{ route('content.sigpac') }}" class="hover:text-white transition-colors">Qué es SIGPAC</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Prueba Gratis</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold text-lg mb-4">Legal</h4>
                <ul class="space-y-2 text-white/70 text-sm">
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacidad</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Términos</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-white/10 mt-8 pt-8 text-center text-white/70 text-sm">
            <p>&copy; {{ date('Y') }} Agro365. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

