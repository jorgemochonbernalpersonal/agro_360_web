@props(['padding' => true, 'color' => null])

@php
$headerBg = match($color) {
    'agro'   => 'bg-gradient-to-br from-agro-50 to-agro-100 border-b-4 border-agro-400',
    'blue'   => 'bg-gradient-to-br from-blue-50 to-blue-100 border-b-4 border-blue-400',
    'purple' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-b-4 border-purple-400',
    'orange' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-b-4 border-orange-400',
    'red'    => 'bg-gradient-to-br from-red-50 to-red-100 border-b-4 border-red-400',
    'green'  => 'bg-gradient-to-br from-green-50 to-green-100 border-b-4 border-green-400',
    'yellow' => 'bg-gradient-to-br from-yellow-50 to-yellow-100 border-b-4 border-yellow-400',
    default  => 'bg-zinc-50 border-b border-zinc-200',
};
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group']) }}>
    @if(isset($header))
        <div class="px-6 py-4 {{ $headerBg }}">
            {{ $header }}
        </div>
    @endif

    <div @class(['px-6 py-5' => $padding, 'p-0' => !$padding])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
            {{ $footer }}
        </div>
    @endif
</div>
