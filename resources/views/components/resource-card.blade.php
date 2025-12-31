@props([
    'title' => null,
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'green',
    'hoverBorderColor' => '[var(--color-agro-green-light)]',
])

@php
    $badgeColors = [
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'orange' => 'bg-orange-100 text-orange-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ];
    
    $badgeClass = $badgeColors[$badgeColor] ?? $badgeColors['gray'];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-{{ $hoverBorderColor }} transition-all p-6">
    
    {{-- Header Section --}}
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            @if(isset($header))
                {{ $header }}
            @else
                @if($title)
                    <h3 class="font-semibold text-gray-900 text-lg">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
        
        @if(isset($badge))
            {{ $badge }}
        @elseif($badge)
            <span class="text-xs font-medium px-2.5 py-1 rounded-full flex-shrink-0 {{ $badgeClass }}">
                {{ $badge }}
            </span>
        @endif
    </div>

    {{-- Content Section --}}
    @if(isset($content))
        <div class="mb-4">
            {{ $content }}
        </div>
    @endif

    {{-- Footer Section --}}
    @if(isset($footer) || isset($actions))
        <div class="pt-3 border-t border-gray-100">
            {{ $footer ?? $actions }}
        </div>
    @endif
</div>
