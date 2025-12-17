@php
    $sigpacIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    $sigpacBadgeIcon = '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    $infoCardIcon = '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $usosIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    $codigosIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    $coordenadasIcon = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        <!-- Header Unificado -->
        <x-page-header
            :icon="$sigpacIcon"
            title="Gestión de SIGPACs"
            description="Sistema de Información Geográfica de Parcelas Agrícolas"
            icon-color="from-[var(--color-agro-blue)] to-blue-700"
            :badge-icon="$sigpacBadgeIcon"
        />

        <!-- Info Card -->
        <x-info-card
            title="Información de SIGPACs"
            gradient="from-[var(--color-agro-blue)] via-blue-600 to-blue-700"
            :icon="$infoCardIcon"
        >
            Gestiona datos geográficos, usos de suelo, códigos SIGPAC y coordenadas de tus parcelas agrícolas.
        </x-info-card>

        <!-- Cards de Funcionalidades -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-feature-card
                :icon="$usosIcon"
                title="Usos SIGPAC"
                description="Gestiona y cataloga los diferentes tipos de usos del suelo disponibles en el sistema SIGPAC"
                icon-gradient="from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]"
            />

            <x-feature-card
                :icon="$codigosIcon"
                title="Códigos SIGPAC"
                description="Administra los códigos de identificación SIGPAC y sus clasificaciones correspondientes"
                icon-gradient="from-[var(--color-agro-blue)] to-blue-700"
                hover-border="hover:border-[var(--color-agro-blue)]/50"
                hover-text="group-hover:text-[var(--color-agro-blue)]"
            />

            <x-feature-card
                :icon="$coordenadasIcon"
                title="Coordenadas Multiparte"
                description="Visualiza y gestiona coordenadas geográficas multiparte de tus parcelas"
                icon-gradient="from-green-500 to-green-700"
                hover-border="hover:border-green-500/50"
                hover-text="group-hover:text-green-600"
            />
        </div>
    </div>
</x-app-layout>
