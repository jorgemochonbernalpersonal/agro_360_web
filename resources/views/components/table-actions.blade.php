@props(['align' => 'right'])

<td class="px-6 py-4">
    <div class="flex items-center justify-{{ $align }} gap-2">
        {{ $slot }}
    </div>
</td>

