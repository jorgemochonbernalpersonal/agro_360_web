@php
    use App\Helpers\NavigationHelper;
    use App\Helpers\BreadcrumbHelper;
    $user = auth()->user();
    $profile = $user->profile;
    $breadcrumbs = BreadcrumbHelper::generate();
@endphp

<!-- Top Bar Premium con Breadcrumbs -->
<header class="fixed top-0 right-0 left-0 lg:left-72 h-16 bg-white/95 backdrop-blur-md shadow-md border-b-2 border-[var(--color-agro-green-light)]/30 z-30 transition-all duration-300" id="top-bar">
    <div class="h-full flex items-center justify-between px-4 lg:px-8">
        <!-- Mobile Menu Button -->
        <button 
            onclick="toggleSidebar()"
            class="lg:hidden p-2 rounded-lg text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] transition-all duration-200"
            aria-label="Toggle menu"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Breadcrumbs -->
        <div class="flex-1 flex items-center overflow-x-auto">
            <nav class="flex items-center space-x-2 text-sm">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($index > 0)
                        <!-- Separador -->
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif

                    @if($crumb['route'] && !$crumb['active'] && !($crumb['alwaysHighlight'] ?? false))
                        <!-- Breadcrumb clickeable -->
                        <a 
                            href="{{ route($crumb['route']) }}" 
                            wire:navigate
                            class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-gray-600 hover:text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] transition-all duration-200 group whitespace-nowrap"
                        >
                            <span class="text-gray-500 group-hover:text-[var(--color-agro-green-dark)]">
                                {!! $crumb['icon'] !!}
                            </span>
                            <span class="font-medium">{{ $crumb['label'] }}</span>
                        </a>
                    @else
                        <!-- Breadcrumb activo (no clickeable) o siempre destacado -->
                        @if($crumb['route'] && !$crumb['active'] && ($crumb['alwaysHighlight'] ?? false))
                            <!-- Breadcrumb destacado pero clickeable (para Parcelas cuando estás en create/edit) -->
                            <a 
                                href="{{ route($crumb['route']) }}" 
                                wire:navigate
                                class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green)] hover:text-white transition-all duration-200 whitespace-nowrap"
                            >
                                <span>{!! $crumb['icon'] !!}</span>
                                <span class="font-bold">{{ $crumb['label'] }}</span>
                            </a>
                        @else
                            <!-- Breadcrumb activo (no clickeable) -->
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] whitespace-nowrap">
                                <span>{!! $crumb['icon'] !!}</span>
                                <span class="font-bold">{{ $crumb['label'] }}</span>
                            </div>
                        @endif
                    @endif
                @endforeach

                {{-- Indicador de Tab para Configuración --}}
                @if(request()->routeIs('viticulturist.settings*'))
                    @php
                        $currentTab = request()->get('tab', 'taxes');
                        $tabs = [
                            'taxes' => ['label' => 'Impuestos', 'icon' => '💰'],
                            'invoicing' => ['label' => 'Numeración', 'icon' => '🔢'],
                            'signature' => ['label' => 'Firma Digital', 'icon' => '🔐'],
                        ];
                    @endphp
                    <span class="text-gray-400 mx-2">|</span>
                    <span 
                        id="settings-tab-indicator"
                        x-data="{
                            currentTab: '{{ $currentTab }}',
                            tabs: {
                                'taxes': { label: 'Impuestos', icon: '💰' },
                                'invoicing': { label: 'Numeración', icon: '🔢' },
                                'signature': { label: 'Firma Digital', icon: '🔐' }
                            },
                            updateTab() {
                                const urlParams = new URLSearchParams(window.location.search);
                                const newTab = urlParams.get('tab') || 'taxes';
                                if (this.currentTab !== newTab) {
                                    this.currentTab = newTab;
                                }
                            },
                            init() {
                                // Actualizar tab inicial
                                this.updateTab();
                                
                                // Escuchar cuando Livewire actualiza la URL después de cada actualización
                                if (typeof Livewire !== 'undefined') {
                                    Livewire.hook('morph.updated', () => {
                                        setTimeout(() => this.updateTab(), 100);
                                    });
                                }
                                
                                // Escuchar navegación de Livewire
                                document.addEventListener('livewire:navigated', () => {
                                    this.updateTab();
                                });
                                
                                // Escuchar cambios directos en la URL (botones atrás/adelante del navegador)
                                window.addEventListener('popstate', () => this.updateTab());
                            }
                        }"
                        class="px-2 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-xs font-semibold flex items-center gap-1"
                    >
                        <span x-text="tabs[currentTab]?.icon || '💰'"></span>
                        <span x-text="tabs[currentTab]?.label || 'Impuestos'"></span>
                    </span>
                @endif
            </nav>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-3 lg:space-x-6">
            <!-- Notifications Component -->
            @livewire('notifications')

            <!-- User Dropdown -->
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button 
                    @click="open = !open"
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-[var(--color-agro-green-bg)] transition-all duration-200"
                >
                    <!-- User Info Desktop -->
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-[var(--color-agro-green-dark)]">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ NavigationHelper::getRoleName($user->role) }}</p>
                    </div>
                    
                    <!-- Avatar con foto real -->
                    @if($profile && $profile->profile_image)
                        <img 
                            src="{{ Storage::disk('public')->url($profile->profile_image) }}" 
                            alt="{{ $user->name }}" 
                            width="40"
                            height="40"
                            loading="eager"
                            decoding="async"
                            class="w-10 h-10 rounded-full object-cover border-2 border-[var(--color-agro-green)] shadow-md" 
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-md" style="display: none;">
                            <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-md">
                            <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @endif

                    <!-- Chevron -->
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 py-2 z-50"
                    style="display: none;"
                >
                    <!-- User Info Mobile -->
                    <div class="px-4 py-3 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-xs font-medium rounded-full">
                            {{ NavigationHelper::getRoleName($user->role) }}
                        </span>
                    </div>

                    <!-- Menu Items -->
                    <a href="{{ route('profile.show') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)] transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Ver Perfil
                    </a>

                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)] transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Perfil
                    </a>

                    @if($user->role === 'viticulturist')
                        <a href="{{ route('viticulturist.settings') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)] transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Configuración
                        </a>
                    @endif

                    <a href="{{ route('subscription.manage') }}" wire:navigate class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)] transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Ver Suscripción
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Escuchar cambios en el sidebar para ajustar el top-bar
    if (typeof window.sidebarObserver === 'undefined') {
        window.sidebarObserver = setInterval(() => {
            const sidebar = document.getElementById('sidebar');
            const topBar = document.getElementById('top-bar');
            
            if (sidebar && topBar && window.innerWidth >= 1024) {
                const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                
                if (isCollapsed) {
                    topBar.classList.remove('lg:left-72');
                    topBar.classList.add('lg:left-20');
                } else {
                    topBar.classList.remove('lg:left-20');
                    topBar.classList.add('lg:left-72');
                }
            }
        }, 100);
    }
</script>
