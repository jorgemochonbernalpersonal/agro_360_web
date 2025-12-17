<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Códigos SIGPAC"
        description="Gestiona los códigos de identificación SIGPAC"
        icon-color="from-[var(--color-agro-blue)] to-blue-700"
    />

    <!-- Búsqueda -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-[var(--color-agro-blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-[var(--color-agro-blue)]">Buscar código</h2>
        </div>
        <input 
            wire:model.live.debounce.300ms="search" 
            type="text" 
            placeholder="Buscar por código o descripción..."
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-blue)] focus:border-transparent transition-all"
        >
    </div>

    <!-- Tabla de códigos -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-xl">
        @if($codes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-50 to-blue-100/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-blue)] uppercase">Código</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-blue)] uppercase">Descripción</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-blue)] uppercase">Parcelas</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-[var(--color-agro-blue)] uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($codes as $code)
                            <tr class="hover:bg-blue-50/40 transition-all">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-gray-900">{{ $code->code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $code->description ?: 'Sin descripción' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        {{ $code->plots_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a 
                                            href="{{ route('plots.index', ['sigpac_code' => $code->id]) }}" 
                                            class="p-2 rounded-lg text-[var(--color-agro-blue)] hover:bg-blue-50 transition-all"
                                            title="Ver parcelas"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $codes->links() }}
            </div>
        @else
            <div class="p-16 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No se encontraron códigos</h3>
                <p class="text-gray-500">
                    @if($search)
                        Intenta con otro término de búsqueda
                    @else
                        No hay códigos SIGPAC registrados
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

