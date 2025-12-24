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
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-4ERJB9C431"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-4ERJB9C431');
    </script>
    
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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
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
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-[-100%]"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-[-100%]"
                class="glass-card rounded-xl p-4 shadow-xl border-l-4 flex items-start gap-3"
                :class="{
                    'bg-green-50 border-green-600': notification.type === 'success',
                    'bg-red-50 border-red-600': notification.type === 'error',
                    'bg-blue-50 border-blue-600': notification.type === 'info',
                    'bg-yellow-50 border-yellow-600': notification.type === 'warning'
                }"
            >
                <div class="flex-shrink-0">
                    <svg 
                        x-show="notification.type === 'success'"
                        class="w-6 h-6 text-green-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg 
                        x-show="notification.type === 'error'"
                        class="w-6 h-6 text-red-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg 
                        x-show="notification.type === 'info'"
                        class="w-6 h-6 text-blue-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg 
                        x-show="notification.type === 'warning'"
                        class="w-6 h-6 text-yellow-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
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
                    @click="removeNotification(index)"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
    function toastNotifications() {
        return {
            notifications: [],
            listenerSetup: false,
            
            init() {
                // Mostrar mensajes flash de sesión si existen (para compatibilidad con redirects)
                @if(session('message'))
                    this.show('success', '{{ session('message') }}');
                @endif
                
                @if(session('error'))
                    this.show('error', '{{ session('error') }}');
                @endif
                
                // Intentar configurar el listener inmediatamente
                this.setupLivewireListener();
                
                // También intentar después de que Livewire se cargue completamente
                document.addEventListener('livewire:init', () => {
                    this.setupLivewireListener();
                });
                
                // Fallback: intentar después de un delay
                setTimeout(() => {
                    this.setupLivewireListener();
                }, 500);
            },
            
            setupLivewireListener() {
                // Solo configurar una vez
                if (this.listenerSetup) return;
                
                // Verificar que Livewire esté disponible
                if (typeof Livewire === 'undefined') {
                    return;
                }
                
                try {
                    // Registrar el listener de eventos
                    Livewire.on('toast', (data) => {
                        this.handleToastEvent(data);
                    });
                    
                    this.listenerSetup = true;
                } catch (e) {
                    // Error silencioso - el toast no funcionará si Livewire no está disponible
                }
            },
            
            handleToastEvent(data) {
                // En Livewire v3, los datos pueden venir como objeto con propiedades nombradas
                let type = 'success';
                let message = '';
                
                if (data && typeof data === 'object') {
                    // Si viene como objeto con propiedades (Livewire v3 named parameters)
                    if (data.type !== undefined) {
                        type = data.type;
                    }
                    if (data.message !== undefined) {
                        message = data.message;
                    }
                    // Si viene como array [type, message]
                    if (Array.isArray(data) && data.length >= 2) {
                        type = data[0] || 'success';
                        message = data[1] || '';
                    }
                } else if (typeof data === 'string') {
                    message = data;
                }
                
                if (message) {
                    this.show(type, message);
                }
            },
            
            show(type, message) {
                if (!message) {
                    return;
                }
                
                // Prevenir duplicados: verificar si ya existe una notificación idéntica reciente
                const now = Date.now();
                const recentDuplicate = this.notifications.find(n => 
                    n.message === message && 
                    n.type === type && 
                    n.createdAt && 
                    (now - n.createdAt) < 1000 // Dentro del último segundo
                );
                
                if (recentDuplicate) {
                    return; // No mostrar duplicado
                }
                
                const notification = {
                    type: type || 'success',
                    message: message,
                    show: true,
                    id: now + Math.random(),
                    createdAt: now
                };
                
                this.notifications.push(notification);
                
                // Auto-remover después de 5 segundos
                setTimeout(() => {
                    this.removeNotificationById(notification.id);
                }, 5000);
            },
            
            removeNotification(index) {
                this.notifications[index].show = false;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 200);
            },
            
            removeNotificationById(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.removeNotification(index);
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
    
    <!-- Script para ajustar el main con el sidebar colapsable -->
    <script>
        if (typeof window.mainContentObserver === 'undefined') {
            window.mainContentObserver = setInterval(() => {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                
                if (sidebar && mainContent && window.innerWidth >= 1024) {
                    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    
                    if (isCollapsed) {
                        mainContent.classList.remove('lg:pl-72');
                        mainContent.classList.add('lg:pl-20');
                    } else {
                        mainContent.classList.remove('lg:pl-20');
                        mainContent.classList.add('lg:pl-72');
                    }
                }
            }, 100);
        }
    </script>
    
    @stack('scripts')
</body>
</html>

