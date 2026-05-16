@props([
    'label',
    'value',
    'color'       => 'zinc',
    'description' => null,
    'center'      => false,
])

@php
    $colorMap = [
        'zinc'   => ['bg' => 'bg-zinc-50',   'label' => 'text-zinc-500',  'value' => 'text-zinc-700'],
        'agro'   => ['bg' => 'bg-agro-50',   'label' => 'text-agro-600',  'value' => 'text-agro-700'],
        'green'  => ['bg' => 'bg-green-50',  'label' => 'text-green-600', 'value' => 'text-green-700'],
        'amber'  => ['bg' => 'bg-amber-50',  'label' => 'text-amber-600', 'value' => 'text-amber-700'],
        'orange' => ['bg' => 'bg-orange-50', 'label' => 'text-orange-600','value' => 'text-orange-700'],
        'blue'   => ['bg' => 'bg-blue-50',   'label' => 'text-blue-600',  'value' => 'text-blue-700'],
        'red'    => ['bg' => 'bg-red-50',    'label' => 'text-red-600',   'value' => 'text-red-700'],
        'purple' => ['bg' => 'bg-purple-50', 'label' => 'text-purple-600','value' => 'text-purple-700'],
    ];
    $c = $colorMap[$color] ?? $colorMap['zinc'];
@endphp

<div class="{{ $c['bg'] }} rounded-xl p-2.5 {{ $center ? 'text-center' : '' }}">
    <p class="text-[10px] {{ $c['label'] }} font-medium uppercase tracking-wide mb-0.5">{{ $label }}</p>
    <p class="text-sm font-bold {{ $c['value'] }} {{ $center ? '' : 'truncate' }}">{{ $value }}</p>
    @if($description)
        <p class="text-[10px] text-zinc-400 mt-0.5">{{ $description }}</p>
    @endif
</div>
