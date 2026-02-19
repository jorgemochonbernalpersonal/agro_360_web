@props(['status' => null, 'active' => true, 'label' => null, 'type' => 'default'])

@php
    if($status !== null) {
        $active = $status;
    }
    $label = $label ?? ($active ? 'Activa' : 'Inactiva');

    if($type === 'default') {
        $color = $active ? 'green' : null;
        $icon = $active ? 'check-circle' : 'x-circle';
    } else {
        $colorMap = [
            'success' => 'green',
            'warning' => 'yellow',
            'danger'  => 'red',
            'info'    => 'blue',
            'gray'    => null,
        ];
        $color = $colorMap[$type] ?? null;
        $icon = null;
    }
@endphp

<flux:badge :$color :$icon size="sm">{{ $label }}</flux:badge>
