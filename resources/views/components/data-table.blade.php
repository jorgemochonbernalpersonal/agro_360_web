@props(['headers', 'emptyMessage' => 'No hay registros', 'emptyDescription' => null, 'emptyIcon' => null, 'color' => 'green'])

@php
    $colorClasses = [
        'green' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-bright)]/30',
            'text' => 'text-[var(--color-agro-green-dark)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-green-bg)]/30 to-transparent',
        ],
        'brown' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-brown-bg)] to-[var(--color-agro-brown-bright)]/30',
            'text' => 'text-[var(--color-agro-brown-dark)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-brown-bg)]/30 to-transparent',
        ],
        'blue' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-blue)]/20 to-blue-50',
            'text' => 'text-[var(--color-agro-blue)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-blue)]/20 to-transparent',
        ],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['green'];
@endphp

<div class="glass-card rounded-2xl overflow-hidden shadow-xl">
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="{{ $colors['header'] }}">
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-6 py-4 text-left text-xs font-bold {{ $colors['text'] }} uppercase tracking-wider {{ is_string($header) && str_contains($header, 'Acciones') ? 'text-right' : '' }}">
                                @if(is_array($header))
                                    <div class="flex items-center gap-2">
                                        @if(isset($header['icon']))
                                            {!! $header['icon'] !!}
                                        @endif
                                        {{ $header['label'] }}
                                    </div>
                                @else
                                    {{ $header }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
        
        @if(isset($pagination))
            <div class="px-6 py-4 border-t border-gray-200 {{ $colors['pagination'] }}">
                {{ $pagination }}
            </div>
        @endif
    @else
        <x-empty-state 
            :message="$emptyMessage" 
            :description="$emptyDescription"
            :icon="$emptyIcon"
        >
            @if(isset($emptyAction))
                <x-slot name="action">
                    {{ $emptyAction }}
                </x-slot>
            @endif
        </x-empty-state>
    @endif
</div>

