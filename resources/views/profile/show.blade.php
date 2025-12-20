@php
    $profileIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    $infoIcon = '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $user = auth()->user();
    $profile = $user->profile;
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        {{-- Header --}}
        <x-page-header
            :icon="$profileIcon"
            title="Mi Perfil"
            description="Vista general de tu información personal y configuración"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Profile Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Personal Information --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Información Personal</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-4">
                            {{-- Mostrar imagen de perfil o inicial --}}
                            @if($profile && $profile->profile_image)
                                <img src="{{ Storage::disk('public')->url($profile->profile_image) }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-gray-200 shadow-lg flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center flex-shrink-0 shadow-lg" style="display: none;">
                                    <span class="text-white text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <span class="text-white text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Nombre Completo</p>
                                <p class="text-lg font-bold text-gray-900 truncate">{{ $user->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="text-base font-semibold text-gray-900 truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                            <svg class="w-6 h-6 text-[var(--color-agro-green-dark)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-green-700">Rol en el Sistema</p>
                                <p class="text-base font-bold text-green-900">{{ \App\Helpers\NavigationHelper::getRoleName($user->role) }}</p>
                            </div>
                        </div>

                        {{-- Información de Contacto Integrada --}}
                        @if($profile)
                            <div class="pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 mb-3">Información de Contacto</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @if($profile->phone)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Teléfono</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $profile->phone }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($profile->city)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Ciudad</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $profile->city }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($profile->province_id)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Provincia</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $profile->province->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($profile->address)
                                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg md:col-span-2">
                                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Dirección</p>
                                                <p class="text-sm font-semibold text-gray-900">{{ $profile->address }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar Actions --}}
            <div class="space-y-6">
                {{-- Quick Actions --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition-all duration-200 group">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Editar Perfil</p>
                                <p class="text-xs text-gray-500">Actualiza tu información</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ route('subscription.manage') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-50 transition-all duration-200 group">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Suscripción</p>
                                <p class="text-xs text-gray-500">Gestiona tu plan</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Account Info --}}
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Información de Cuenta</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Estado de email</span>
                            @if($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Verificado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                    Pendiente
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Miembro desde</span>
                            <span class="font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
