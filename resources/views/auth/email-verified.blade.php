<x-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 p-4 sm:p-6 lg:p-8">
        <!-- Elementos decorativos -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-agro-green-light)]/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--color-agro-green)]/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="w-full max-w-md relative z-10">
            <!-- Logo -->
            <div class="text-center mb-6">
                <div class="inline-block max-w-[200px] mx-auto mb-4">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365 Logo" 
                        class="w-full h-auto object-contain drop-shadow-2xl"
                    >
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <!-- Icono de éxito -->
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Cuenta Verificada!</h2>
                    <p class="text-gray-600 text-sm">
                        Tu email ha sido verificado exitosamente. Ya puedes acceder a todas las funcionalidades de Agro365.
                    </p>
                </div>

                <!-- Mensaje de bienvenida -->
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    <p class="text-sm font-medium text-center">
                        Bienvenido a Agro365, <strong>{{ auth()->user()->name }}</strong>
                    </p>
                </div>

                <!-- Botón para ir al dashboard -->
                <div class="space-y-3">
                    <a 
                        href="{{ route($dashboardRoute) }}"
                        class="block w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg text-center"
                    >
                        Ir al Dashboard
                    </a>
                    
                    <p class="text-xs text-gray-500 text-center">
                        Serás redirigido automáticamente en <span id="countdown">5</span> segundos...
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirección después de 5 segundos
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const dashboardUrl = '{{ route($dashboardRoute) }}';
        const REDIRECT_KEY = 'agro365_redirecting';
        let isRedirecting = false;

        // Notificar a otras pestañas que estamos redirigiendo
        localStorage.setItem(REDIRECT_KEY, 'true');
        localStorage.setItem('agro365_email_verified', dashboardUrl);
        
        // Limpiar después de 5 segundos
        setTimeout(() => {
            localStorage.removeItem(REDIRECT_KEY);
        }, 5000);

        const timer = setInterval(() => {
            if (isRedirecting) {
                clearInterval(timer);
                return;
            }
            
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                isRedirecting = true;
                window.location.href = dashboardUrl;
            }
        }, 1000);
        
        // Escuchar si otra pestaña ya redirigió
        window.addEventListener('storage', function(e) {
            if (e.key === REDIRECT_KEY && !e.newValue && !isRedirecting) {
                // Otra pestaña completó la redirección, redirigir también
                isRedirecting = true;
                clearInterval(timer);
                window.location.href = dashboardUrl;
            }
        });
    </script>
</x-app-layout>

