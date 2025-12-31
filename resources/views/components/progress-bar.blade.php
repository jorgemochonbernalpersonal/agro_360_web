@props([
    'percentage' => 0,
    'label' => null,
    'currentValue' => null,
    'maxValue' => null,
    'unit' => null,
    'showValues' => true,
])

@php
    // Determine color based on percentage
    $colorClass = match(true) {
        $percentage >= 90 => 'bg-red-500',
        $percentage >= 70 => 'bg-orange-500',
        $percentage >= 50 => 'bg-yellow-500',
        default => 'bg-green-500',
    };
@endphp

<div>
    @if($label || $percentage !== null)
        <div class="flex justify-between text-sm text-gray-600 mb-1">
            @if($label)
                <span>{{ $label }}</span>
            @endif
            @if($percentage !== null)
                <span class="font-semibold">{{ number_format($percentage, 0) }}%</span>
            @endif
        </div>
    @endif
    
    <div class="w-full bg-gray-200 rounded-full h-2">
        <div class="h-2 rounded-full transition-all {{ $colorClass }}"
             style="width: {{ min($percentage, 100) }}%">
        </div>
    </div>
    
    @if($showValues && $currentValue !== null && $maxValue !== null)
        <p class="text-xs text-gray-500 mt-1">
            {{ is_numeric($currentValue) ? number_format($currentValue, 0) : $currentValue }} / 
            {{ is_numeric($maxValue) ? number_format($maxValue, 0) : $maxValue }}
            @if($unit) {{ $unit }}@endif
        </p>
    @endif
</div>
