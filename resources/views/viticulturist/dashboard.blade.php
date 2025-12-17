@php
    $viticulturistIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    $viticulturistBadgeIcon = '<svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    $viticulturistInfoIcon = '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>';
    $plotsIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    $sigpacIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    $configIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        <!-- Header Unificado -->
        <x-page-header
            :icon="$viticulturistIcon"
            title="Dashboard Viticultor"
            description="Gestión de cuadrillas y asignaciones"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
            :badge-icon="$viticulturistBadgeIcon"
        />

        <!-- Bienvenida Premium -->
        <x-info-card
            gradient="from-[var(--color-agro-green-dark)] via-[var(--color-agro-green)] to-[var(--color-agro-green-light)]"
            :icon="$viticulturistInfoIcon"
        >
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-white/90 text-lg font-medium">Bienvenido,</span>
                </div>
                <h2 class="text-3xl font-bold text-white mb-3">
                    {{ auth()->user()->name }}
                </h2>
                <p class="text-white/90 text-lg">
                    Todo listo para gestionar tus cuadrillas y asignaciones. ¡Que tengas un excelente día!
                </p>
            </div>
        </x-info-card>

        <!-- Cards de Funcionalidades -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-feature-card
                href="{{ route('plots.index') }}"
                :icon="$plotsIcon"
                title="Parcelas"
                description="Gestiona y visualiza todas tus parcelas asignadas con información detallada"
                icon-gradient="from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]"
            />

            <x-feature-card
                href="{{ route('sigpac.index') }}"
                :icon="$sigpacIcon"
                title="SIGPACs"
                description="Administra datos SIGPAC y registros geográficos de tus terrenos"
                icon-gradient="from-[var(--color-agro-blue)] to-blue-700"
                hover-border="hover:border-[var(--color-agro-blue)]/50"
                hover-text="group-hover:text-[var(--color-agro-blue)]"
            />

            <x-feature-card
                href="{{ route('config.index') }}"
                :icon="$configIcon"
                title="Configuración"
                description="Ajusta preferencias y configuraciones del sistema a tu medida"
                icon-gradient="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
                hover-border="hover:border-[var(--color-agro-brown)]/50"
                hover-text="group-hover:text-[var(--color-agro-brown)]"
            />
        </div>
    </div>
</x-app-layout>
