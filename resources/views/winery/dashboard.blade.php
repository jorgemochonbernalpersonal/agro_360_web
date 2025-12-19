<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        <!-- Header Unificado -->
        <x-page-header
            :icon='<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
            title="Dashboard Bodega"
            description="Gestión integral de la bodega y producción"
            icon-color="from-rose-500 to-rose-700"
            :badge-icon='<svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>'
        />

        <!-- Bienvenida Premium -->
        <x-info-card
            gradient="from-rose-600 via-rose-500 to-pink-500"
            :icon='<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        >
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-white/90 text-lg font-medium">Bienvenido,</span>
                </div>
                <h2 class="text-3xl font-bold text-white mb-3">
                    {{ auth()->user()->name }}
                </h2>
                <p class="text-white/90 text-lg">
                    Gestiona tu bodega, viticultores y parcelas desde un solo lugar. ¡Éxito en la producción!
                </p>
            </div>
        </x-info-card>

        <!-- Cards de Funcionalidades -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-feature-card
                href="{{ route('plots.index') }}"
                :icon='<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'
                title="Parcelas"
                description="Gestiona las parcelas asociadas a tu bodega"
                icon-gradient="from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]"
            />

            <x-feature-card
                href="{{ route('plots.plantings.index') }}"
                :icon='<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/>
                </svg>'
                title="Plantaciones"
                description="Consulta y analiza las plantaciones de variedades en tus parcelas"
                icon-gradient="from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]"
            />

            <x-feature-card
                href="{{ route('sigpac.index') }}"
                :icon='<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                title="SIGPACs"
                description="Consulta datos SIGPAC y coordenadas geográficas"
                icon-gradient="from-[var(--color-agro-blue)] to-blue-700"
                hover-border="hover:border-[var(--color-agro-blue)]/50"
                hover-text="group-hover:text-[var(--color-agro-blue)]"
            />

            <x-feature-card
                href="{{ route('config.index') }}"
                :icon='<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                title="Configuración"
                description="Ajusta preferencias y configuraciones del sistema"
                icon-gradient="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
                hover-border="hover:border-[var(--color-agro-brown)]/50"
                hover-text="group-hover:text-[var(--color-agro-brown)]"
            />
        </div>
    </div>
</x-app-layout>
