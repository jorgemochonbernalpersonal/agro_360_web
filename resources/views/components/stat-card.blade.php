@props(['icon', 'value', 'label', 'trend' => null, 'color' => 'blue'])

@php
$colorClasses = [
    'blue' => 'border-blue-200 dark:border-blue-800',
    'green' => 'border-green-200 dark:border-green-800',
    'red' => 'border-red-200 dark:border-red-800',
    'yellow' => 'border-yellow-200 dark:border-yellow-800',
    'purple' => 'border-purple-200 dark:border-purple-800',
];

$trendClasses = $trend > 0 ? 'text-green-600' : ($trend < 0 ? 'text-red-600' : 'text-gray-600');
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border-l-4 p-6 ' . ($colorClasses[$color] ?? $colorClasses['blue'])]) }}>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ $label }}
            </p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                {{ $value }}
            </p>
            
            @if($trend !== null)
            <p class="mt-2 flex items-center text-sm {{ $trendClasses }}">
                @if($trend > 0)
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                @elseif($trend < 0)
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
                <span>{{ abs($trend) }}%</span>
            </p>
            @endif
        </div>

        @if($icon)
        <div class="flex-shrink-0">
            <div class="p-3 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/20 rounded-lg">
                {!! $icon !!}
            </div>
        </div>
        @endif
    </div>
</div>
