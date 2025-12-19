<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agro365</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    @auth
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Top Bar -->
        <x-top-bar />
    @endauth
    
    <!-- Main Content -->
    <main class="min-h-screen transition-all duration-300 @auth pt-16 lg:pl-72 @endauth" id="main-content">
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
</body>
</html>

