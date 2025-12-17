@props(['hover' => true])

<tr class="{{ $hover ? 'hover:bg-[var(--color-agro-green-bg)]/40 transition-all duration-200 group' : '' }}">
    {{ $slot }}
</tr>

