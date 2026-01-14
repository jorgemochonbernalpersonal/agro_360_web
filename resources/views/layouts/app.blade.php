<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NGTPTZSQ');</script>
    <!-- End Google Tag Manager -->
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $appUrl = config('app.url');
        $currentUrl = $appUrl . request()->getRequestUri();
        $currentPath = request()->path();
        $pageTitle = $title ?? 'Agro365 - Software de Gestión Agrícola para Viñedos';
        $pageDescription = $description ?? \App\Helpers\SeoHelper::getMetaDescription('/' . $currentPath);
        $pageImage = $image ?? asset('images/logo.png');
    @endphp
    
    <!-- Hreflang for Spain -->
    <link rel="alternate" hreflang="es" href="{{ $currentUrl }}">
    <link rel="alternate" hreflang="es-ES" href="{{ $currentUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="author" content="Agro365">
    <meta name="publisher" content="Agro365">
    <meta name="theme-color" content="#10b981">
    
    <!-- SEO Meta Tags -->
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">
    
    <!-- Canonical URL - SEO: indica que agro365.es es el dominio principal -->
    <link rel="canonical" href="{{ $currentUrl }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $currentUrl }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts - Optimized -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- ✅ Preload de Assets Críticos - Mejora tiempo de carga inicial -->
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $appJs = $manifest['resources/js/app.js']['file'] ?? null;
        $appCss = $manifest['resources/css/app.css']['file'] ?? null;
    @endphp
    @if($appJs)
        <link rel="preload" href="{{ asset('build/' . $appJs) }}" as="script">
    @endif
    @if($appCss)
        <link rel="preload" href="{{ asset('build/' . $appCss) }}" as="style">
    @endif
    
    <!-- Styles / Scripts -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NGTPTZSQ"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    @auth
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Top Bar -->
        <x-top-bar />

        <!-- Banner de Impersonación -->
        @if(session('impersonating'))
            <div class="fixed top-16 left-0 right-0 z-50 bg-red-600 text-white shadow-lg">
                <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="font-semibold">
                            ⚠️ Estás viendo como: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                        </span>
                    </div>
                    <form method="POST" action="{{ route('admin.users.stop-impersonate') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-red-50 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Volver a Admin
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endauth
    
    <!-- Main Content -->
    <main class="min-h-screen transition-all duration-300 @auth @if(session('impersonating')) pt-24 @else pt-16 @endif lg:pl-72 @endauth" id="main-content">
        <div class="@auth p-4 lg:p-8 @else p-0 @endauth">
            {{ $slot }}
        </div>
    </main>
    
    @livewireScripts
    
    <!-- Sistema de Notificaciones Toast -->
    <div 
        x-data="toastNotifications()" 
        x-init="init()"
        class="fixed bottom-4 left-4 z-[9999] space-y-3"
        style="max-width: 400px;"
    >
        <template x-for="(notification, index) in notifications" :key="notification.id">
            <div
                x-show="notification.show"
                x-cloak
                @mouseenter="pauseNotification(notification.id)"
                @mouseleave="resumeNotification(notification.id)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-[-100%]"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-[-100%]"
                class="glass-card rounded-xl p-4 shadow-xl border-l-4 flex items-start gap-3 relative overflow-hidden transition-all duration-300 hover:scale-[1.02]"
                :class="{
                    'bg-green-50/90 border-green-600 text-green-600': notification.type === 'success',
                    'bg-red-50/90 border-red-600 text-red-600': notification.type === 'error',
                    'bg-blue-50/90 border-blue-600 text-blue-600': notification.type === 'info',
                    'bg-yellow-50/90 border-yellow-600 text-yellow-600': notification.type === 'warning'
                }"
            >
                <div class="flex-shrink-0">
                    <svg x-show="notification.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg x-show="notification.type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg x-show="notification.type === 'info'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg x-show="notification.type === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0 pr-4">
                    <p 
                        class="text-sm font-semibold"
                        :class="{
                            'text-green-800': notification.type === 'success',
                            'text-red-800': notification.type === 'error',
                            'text-blue-800': notification.type === 'info',
                            'text-yellow-800': notification.type === 'warning'
                        }"
                        x-text="notification.message"
                    ></p>
                </div>
                <button
                    @click="removeNotificationById(notification.id)"
                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <!-- Barra de Progreso -->
                <div 
                    class="toast-progress" 
                    :class="{ 'toast-progress-paused': notification.paused }"
                    :style="{ animationDuration: '5000ms' }"
                ></div>
            </div>
        </template>
    </div>

    <script>
    function toastNotifications() {
        return {
            notifications: [],
            listenerSetup: false,
            audioCtx: null,
            
            init() {
                // Cargar sonidos si el navegador lo permite
                this.audioCtx = null;
                
                // Mostrar mensajes flash de sesión si existen
                @if(session('message')) this.show('success', '{{ session('message') }}'); @endif
                @if(session('error')) this.show('error', '{{ session('error') }}'); @endif
                @if(session('info')) this.show('info', '{{ session('info') }}'); @endif
                @if(session('warning')) this.show('warning', '{{ session('warning') }}'); @endif
                
                // Listen for Livewire events
                Livewire.on('toast', (data) => {
                    // Ensure data is an array and has at least one element
                    if (Array.isArray(data) && data.length > 0) {
                        // Assuming the first element of the array is the toast data object
                        const toastData = data[0];
                        this.add(toastData.message, toastData.type || 'success');
                        this.playSound();
                    } else if (typeof data === 'string') {
                        // Handle cases where only a message string is passed
                        this.add(data, 'success');
                        this.playSound();
                    }
                });
            },
            
            // The original setupLivewireListener and handleToastEvent are replaced by the direct Livewire.on in init.
            // Keeping them commented out or removed based on user's intent.
            // setupLivewireListener() {
            //     if (this.listenerSetup || typeof Livewire === 'undefined') return;
            //     try {
            //         Livewire.on('toast', (data) => this.handleToastEvent(data));
            //         this.listenerSetup = true;
            //     } catch (e) {}
            // },
            
            // handleToastEvent(data) {
            //     let type = 'success';
            //     let message = '';
            //     if (data && typeof data === 'object') {
            //         type = data.type || 'success';
            //         message = data.message || '';
            //         if (Array.isArray(data) && data.length >= 2) {
            //             type = data[0];
            //             message = data[1];
            //         }
            //     } else if (typeof data === 'string') {
            //         message = data;
            //     }
            //     if (message) this.show(type, message);
            // },
            
            show(type, message) {
                if (!message) return;
                const now = Date.now();
                if (this.notifications.some(n => n.message === message && n.type === type && (now - n.createdAt) < 1000)) return;
                
                const notification = {
                    type: type || 'success',
                    message: message,
                    show: true,
                    paused: false,
                    id: now + Math.random(),
                    createdAt: now,
                    timeout: null
                };
                
                this.notifications.push(notification);
                this.startTimer(notification.id);
            },

            add(message, type = 'success') {
                const id = Date.now();
                this.notifications.push({ // Changed from this.toasts to this.notifications
                    id,
                    message,
                    type,
                    show: false,
                    progress: 100 // This property is not used in the existing template, but kept as per instruction
                });

                // Trigger sound for auto-added (Livewire) toasts
                // Session-based toasts don't trigger sound on mount to avoid annoyance
                
                this.$nextTick(() => {
                    const index = this.notifications.findIndex(t => t.id === id); // Changed from this.toasts to this.notifications
                    if (index !== -1) {
                        this.notifications[index].show = true;
                        this.startTimer(id);
                    }
                });
            },

            playSound() {
                try {
                    if (!this.audioCtx) {
                        this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }

                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }

                    const oscillator = this.audioCtx.createOscillator();
                    const gainNode = this.audioCtx.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(880, this.audioCtx.currentTime); // A5

                    gainNode.gain.setValueAtTime(0, this.audioCtx.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.05, this.audioCtx.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.0001, this.audioCtx.currentTime + 0.3);

                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioCtx.destination);

                    oscillator.start();
                    oscillator.stop(this.audioCtx.currentTime + 0.3);
                } catch (e) {
                    console.warn('Could not play notification sound:', e);
                }
            },

            startTimer(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index === -1) return;
                
                this.notifications[index].timeout = setTimeout(() => {
                    this.removeNotificationById(id);
                }, 5000);
            },

            pauseNotification(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].paused = true;
                    clearTimeout(this.notifications[index].timeout);
                }
            },

            resumeNotification(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].paused = false;
                    // En un sistema real ideal, calcularíamos el tiempo restante.
                    // Aquí simplificamos reiniciando el temporizador de 5s para una mejor UX si el usuario quiere leerlo.
                    this.startTimer(id);
                }
            },
            
            removeNotificationById(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].show = false;
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 300);
                }
            }
        }
    }
    </script>
    
    <!-- Manejo de errores CSRF expirados (419) -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) { // CSRF Token Mismatch
                        preventDefault();
                        
                        // Mostrar mensaje y recargar
                        if (confirm('Tu sesión ha expirado. ¿Recargar la página para continuar?')) {
                            window.location.reload();
                        }
                    }
                });
            });
        });
    </script>
    
    <!-- Script para ajustar el main con el sidebar colapsable - Optimizado -->
    <script>
        (function() {
            if (typeof window.mainContentObserver !== 'undefined') return;
            
            let lastState = null;
            let rafId = null;
            
            function updateLayout() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                
                if (!sidebar || !mainContent || window.innerWidth < 1024) {
                    rafId = requestAnimationFrame(updateLayout);
                    return;
                }
                
                const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                
                // Solo actualizar si el estado cambió
                if (lastState !== isCollapsed) {
                    lastState = isCollapsed;
                    
                    if (isCollapsed) {
                        mainContent.classList.remove('lg:pl-72');
                        mainContent.classList.add('lg:pl-20');
                    } else {
                        mainContent.classList.remove('lg:pl-20');
                        mainContent.classList.add('lg:pl-72');
                    }
                }
                
                rafId = requestAnimationFrame(updateLayout);
            }
            
            // Iniciar solo cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    rafId = requestAnimationFrame(updateLayout);
                });
            } else {
                rafId = requestAnimationFrame(updateLayout);
            }
            
            window.mainContentObserver = { cancel: () => cancelAnimationFrame(rafId) };
        })();
    </script>
    
    @stack('scripts')
</body>
</html>

