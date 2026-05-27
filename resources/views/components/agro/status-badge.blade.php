@props(['status' => null, 'active' => true, 'label' => null, 'type' => 'default'])

@php
    if($status !== null) {
        $active = $status;
    }
    $label = $label ?? ($active ? __('Activa') : __('Inactiva'));
@endphp

@if($type === 'default')
    <span class="inline-flex items-center gap-1.5">
        <span class="relative flex size-2">
            @if($active)
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-agro-400 opacity-75"></span>
                <span class="relative inline-flex size-2 rounded-full bg-agro-500"></span>
            @else
                <span class="relative inline-flex size-2 rounded-full bg-zinc-300"></span>
            @endif
        </span>
        <span class="text-xs font-medium {{ $active ? 'text-agro-700' : 'text-zinc-400' }}">
            {{ $label }}
        </span>
    </span>
@else
    @php
        $colorMap = [
            'success' => 'green',
            'warning' => 'yellow',
            'danger'  => 'red',
            'info'    => 'blue',
            'gray'    => null,
        ];
        $color = $colorMap[$type] ?? null;
    @endphp
    <flux:badge :$color size="sm">{{ $label }}</flux:badge>
@endif
