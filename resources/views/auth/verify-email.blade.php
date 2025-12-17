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
                <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Verifica tu Email</h2>
                
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        <p class="text-sm font-medium">Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.</p>
                    </div>
                @else
                    <p class="text-gray-600 text-sm mb-6 text-center">
                        Gracias por registrarte. Antes de continuar, por favor verifica tu email haciendo clic en el enlace que te enviamos a <strong>{{ auth()->user()->email }}</strong>.
                    </p>
                @endif

                <!-- Indicador de verificación automática -->
                <div id="verification-status" class="mb-4 hidden">
                    <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm font-medium">Verificando estado de tu cuenta...</p>
                        </div>
                    </div>
                </div>

                @if (session('message'))
                    <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                        <p class="text-sm font-medium">{{ session('message') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                    @csrf
                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
                    >
                        Reenviar Email de Verificación
                    </button>
                </form>

                <div class="mt-6 text-center space-y-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                            Cerrar Sesión
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-4">
                        ¿No recibiste el email? Revisa tu carpeta de spam o solicita un nuevo enlace.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh para detectar cuando el email está verificado
        let checkInterval;
        let checkCount = 0;
        const maxChecks = 120; // Máximo 10 minutos (120 * 5 segundos)
        const verificationStatus = document.getElementById('verification-status');
        const STORAGE_KEY = 'agro365_email_verified';
        const REDIRECT_KEY = 'agro365_redirecting';
        let isRedirecting = false;
        let isTabActive = true;
        
        // Verificar si ya hay una redirección en curso (otra pestaña)
        if (localStorage.getItem(REDIRECT_KEY)) {
            // Otra pestaña ya está redirigiendo, solo mostrar mensaje
            verificationStatus.classList.remove('hidden');
            verificationStatus.querySelector('p').textContent = 'Email verificado en otra pestaña. Redirigiendo...';
            setTimeout(() => {
                const redirectUrl = localStorage.getItem(STORAGE_KEY);
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }, 1000);
        }
        
        // Page Visibility API: solo hacer polling en pestaña activa
        document.addEventListener('visibilitychange', function() {
            isTabActive = !document.hidden;
            
            if (isTabActive) {
                // Pestaña activa: reanudar polling si no está redirigiendo
                if (!isRedirecting && !checkInterval) {
                    startPolling();
                }
            } else {
                // Pestaña inactiva: pausar polling
                if (checkInterval) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                }
            }
        });
        
        // Escuchar cambios en localStorage (sincronización entre pestañas)
        window.addEventListener('storage', function(e) {
            if (e.key === STORAGE_KEY && e.newValue) {
                // Otra pestaña detectó verificación
                handleVerificationDetected(e.newValue);
            }
        });
        
        // Función para iniciar polling
        function startPolling() {
            if (checkInterval) return; // Ya está corriendo
            
            checkInterval = setInterval(() => {
                if (isTabActive && !isRedirecting) {
                    checkVerificationStatus();
                }
            }, 5000);
            
            // Verificar inmediatamente
            checkVerificationStatus();
        }
        
        // Función para manejar cuando se detecta verificación
        function handleVerificationDetected(redirectUrl) {
            if (isRedirecting) return; // Ya estamos redirigiendo
            
            isRedirecting = true;
            clearInterval(checkInterval);
            checkInterval = null;
            verificationStatus.classList.remove('hidden');
            
            // Mostrar mensaje de éxito
            const successMessage = document.createElement('div');
            successMessage.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg';
            successMessage.innerHTML = '<p class="text-sm font-medium">¡Email verificado! Redirigiendo...</p>';
            const form = document.querySelector('form');
            if (form) {
                form.parentNode.insertBefore(successMessage, form);
            }
            
            // Redirigir después de 1 segundo
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1000);
        }
        
        // Función para verificar el estado de verificación
        async function checkVerificationStatus() {
            if (isRedirecting || !isTabActive) return;
            
            try {
                const response = await fetch('{{ route("verification.check") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.verified) {
                    // Email verificado
                    clearInterval(checkInterval);
                    checkInterval = null;
                    
                    // Guardar en localStorage para sincronizar con otras pestañas
                    localStorage.setItem(STORAGE_KEY, data.redirect_url);
                    localStorage.setItem(REDIRECT_KEY, 'true');
                    
                    // Limpiar después de 5 segundos (tiempo suficiente para que otras pestañas lo detecten)
                    setTimeout(() => {
                        localStorage.removeItem(REDIRECT_KEY);
                    }, 5000);
                    
                    // Manejar redirección
                    handleVerificationDetected(data.redirect_url);
                } else {
                    // Mostrar indicador después de 3 intentos (15 segundos)
                    if (checkCount >= 3) {
                        verificationStatus.classList.remove('hidden');
                    }
                }
                
                checkCount++;
                
                // Detener después de maxChecks intentos
                if (checkCount >= maxChecks) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                    verificationStatus.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error verificando estado:', error);
            }
        }

        // Iniciar polling solo si la pestaña está activa
        if (isTabActive) {
            startPolling();
        }
    </script>
</x-app-layout>

