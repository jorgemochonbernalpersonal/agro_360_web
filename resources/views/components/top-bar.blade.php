@php
    use App\Helpers\NavigationHelper;
    use App\Helpers\BreadcrumbHelper;
    $user = auth()->user();
    $profile = $user->profile;
    $breadcrumbs = BreadcrumbHelper::generate();
@endphp

<header class="fixed top-0 right-0 left-0 lg:left-16 h-16 bg-white/80 backdrop-blur-md border-b border-zinc-200/80 shadow-xs z-30 transition-all duration-300" id="top-bar">
    <div class="h-full flex items-center justify-between px-4 lg:px-8">
        {{-- Mobile menu --}}
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-zinc-600 hover:bg-zinc-100 transition" aria-label="Toggle menu">
            <flux:icon icon="bars-3" />
        </button>

        {{-- Breadcrumbs --}}
        <div class="flex-1 flex items-center overflow-x-auto">
            <flux:breadcrumbs>
                @foreach($breadcrumbs as $index => $crumb)
                    @if($crumb['route'] && !$crumb['active'] && !($crumb['alwaysHighlight'] ?? false))
                        <flux:breadcrumbs.item href="{{ route($crumb['route']) }}" wire:navigate>
                            {{ $crumb['label'] }}
                        </flux:breadcrumbs.item>
                    @elseif($crumb['route'] && !$crumb['active'] && ($crumb['alwaysHighlight'] ?? false))
                        <flux:breadcrumbs.item href="{{ route($crumb['route']) }}" wire:navigate>
                            {{ $crumb['label'] }}
                        </flux:breadcrumbs.item>
                    @else
                        <flux:breadcrumbs.item>{{ $crumb['label'] }}</flux:breadcrumbs.item>
                    @endif
                @endforeach
            </flux:breadcrumbs>
        </div>

        {{-- User section --}}
        <div class="flex items-center gap-3">
            @persist('notifications')
                @livewire('notifications')
            @endpersist

            <flux:dropdown position="bottom" align="end">
                <flux:button variant="ghost" class="flex items-center gap-2 !px-2">
                    <span class="hidden md:block text-right">
                        <span class="block text-sm font-medium text-zinc-800">{{ $user->name }}</span>
                        <span class="block text-xs text-zinc-500">{{ NavigationHelper::getRoleName($user->role) }}</span>
                    </span>
                    @if($profile && $profile->profile_image)
                        <flux:avatar src="{{ Storage::disk('public')->url($profile->profile_image) }}" size="sm" />
                    @else
                        <flux:avatar name="{{ $user->name }}" size="sm" />
                    @endif
                </flux:button>

                <flux:menu>
                    <div class="px-3 py-2 border-b border-zinc-200 md:hidden">
                        <p class="text-sm font-medium">{{ $user->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                    </div>
                    <flux:menu.item href="{{ route('profile.show') }}" wire:navigate icon="user">Ver Perfil</flux:menu.item>
                    <flux:menu.item href="{{ route('profile.edit') }}" wire:navigate icon="pencil-square">Editar Perfil</flux:menu.item>
                    @if($user->hasViticulturistAccess())
                        <flux:menu.item href="{{ route('viticulturist.settings') }}" wire:navigate icon="cog-6-tooth">Configuración</flux:menu.item>
                    @endif
                    <flux:menu.item href="{{ route('subscription.manage') }}" wire:navigate icon="credit-card">Ver Suscripción</flux:menu.item>
                    <flux:separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle" class="!text-red-600">
                            Cerrar Sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
</header>
