@props(['for' => null])

<label
    @if($for) for="{{ $for }}" @endif
    class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1"
>
    {{ $slot }}
</label>
