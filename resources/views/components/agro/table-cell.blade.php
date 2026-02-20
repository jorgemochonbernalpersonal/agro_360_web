@props(['align' => 'left'])

<td {{ $attributes->merge(['class' => 'px-6 py-4 text-sm text-zinc-700 whitespace-nowrap text-' . $align]) }}>
    {{ $slot }}
</td>
