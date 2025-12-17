<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Calendario"
        description="Planifica y gestiona tus actividades agrícolas"
        icon-color="from-[var(--color-agro-yellow)] to-yellow-600"
    />

    <!-- Contenido -->
    <div class="glass-card rounded-xl p-8 text-center">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Calendario de Actividades</h3>
        <p class="text-gray-600">Esta sección estará disponible próximamente</p>
    </div>
</div>

