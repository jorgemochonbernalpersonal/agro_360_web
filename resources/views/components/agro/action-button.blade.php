@props(['variant' => 'view', 'href' => null, 'wireClick' => null, 'wireConfirm' => null, 'disabled' => false])

@php
    $iconMap = [
        'view'       => 'eye',
        'edit'       => 'pencil-square',
        'delete'     => 'trash',
        'activate'   => 'check-circle',
        'deactivate' => 'no-symbol',
        'info'       => 'information-circle',
        'archive'    => 'archive-box',
        'map'        => 'map',
        'generate'   => 'sparkles',
        'history'    => 'clock',
        'planting'   => 'book-open',
    ];

    $titleMap = [
        'view'       => 'Ver detalles',
        'edit'       => 'Editar',
        'delete'     => 'Eliminar',
        'activate'   => 'Activar',
        'deactivate' => 'Desactivar',
        'info'       => 'Más información',
        'archive'    => 'Archivar',
        'map'        => 'Ver mapa',
        'generate'   => 'Generar',
        'history'    => 'Ver historial',
        'planting'   => 'Plantaciones',
    ];

    $icon = $iconMap[$variant] ?? 'ellipsis-horizontal';
    $title = $titleMap[$variant] ?? '';
    $isDanger = $variant === 'delete';
@endphp

@if($href && !$disabled)
    <flux:button :$href variant="ghost" size="sm" :$icon :tooltip="$title" />
@elseif($wireClick && !$disabled)
    <flux:button
        variant="ghost"
        size="sm"
        :$icon
        :tooltip="$title"
        wire:click="{{ $wireClick }}"
        @if($wireConfirm) wire:confirm="{{ $wireConfirm }}" @endif
    />
@else
    <flux:button variant="ghost" size="sm" :$icon :tooltip="$title" :disabled="$disabled" />
@endif
