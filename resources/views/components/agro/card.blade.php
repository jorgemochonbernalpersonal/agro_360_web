@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-zinc-200 shadow-xs']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-zinc-200">
            {{ $header }}
        </div>
    @endif

    <div @class(['px-6 py-5' => $padding, 'p-0' => !$padding])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-xl">
            {{ $footer }}
        </div>
    @endif
</div>
