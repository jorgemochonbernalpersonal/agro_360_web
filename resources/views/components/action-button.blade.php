@props(['variant' => 'view', 'href' => null, 'wireClick' => null, 'wireTarget' => null, 'wireConfirm' => null, 'disabled' => false])

@php
    $variants = [
        'view' => 'text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)]',
        'edit' => 'text-blue-600 hover:bg-blue-50',
        'delete' => 'text-red-600 hover:bg-red-50',
        'activate' => 'text-green-600 hover:bg-green-50',
        'deactivate' => 'text-orange-600 hover:bg-orange-50',
        'info' => 'text-purple-600 hover:bg-purple-50',
        'archive' => 'text-orange-600 hover:bg-orange-50',
        'map' => 'text-green-600 hover:bg-green-50',
        'generate' => 'text-blue-600 hover:bg-blue-50',
        'history' => 'text-gray-600 hover:bg-gray-100',
        'planting' => 'text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)]',
    ];
    
    $icons = [
        'view' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'delete' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
        'activate' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'deactivate' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'archive' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
        'map' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
        'generate' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>',
        'history' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'planting' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
    ];
    
    $titles = [
        'view' => 'Ver detalles',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'activate' => 'Activar',
        'deactivate' => 'Desactivar',
        'info' => 'Más información',
        'archive' => 'Archivar',
        'map' => 'Ver mapa',
        'generate' => 'Generar mapa',
        'history' => 'Ver historial',
        'planting' => 'Gestión de plantaciones',
    ];
    
    $classes = 'p-2 rounded-lg transition-all duration-200 group/btn relative ' . $variants[$variant];
    if($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed pointer-events-none';
    }

    $target = $wireTarget ?: $wireClick;
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" class="{{ $classes }}" title="{{ $titles[$variant] }}">
        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$variant] !!}
        </svg>
    </a>
@elseif($wireClick && !$disabled)
    <button 
        wire:click="{{ $wireClick }}"
        @if($wireTarget) wire:target="{{ $wireTarget }}" @endif
        @if($wireConfirm) wire:confirm="{{ $wireConfirm }}" @endif
        wire:loading.attr="disabled"
        class="{{ $classes }}"
        title="{{ $titles[$variant] }}"
    >
        <span @if($target) wire:loading.remove wire:target="{{ $target }}" @endif>
            <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icons[$variant] !!}
            </svg>
        </span>
        @if($target)
            <span wire:loading wire:target="{{ $target }}">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        @endif
    </button>
@else
    <button class="{{ $classes }}" title="{{ $titles[$variant] }}" @if($disabled) disabled @endif>
        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$variant] !!}
        </svg>
    </button>
@endif

