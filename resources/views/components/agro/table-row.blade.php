@props(['highlighted' => false])

<tr {{ $attributes->merge(['class' => 'transition-colors hover:bg-zinc-50' . ($highlighted ? ' bg-agro-50' : '')]) }}>
    {{ $slot }}
</tr>
