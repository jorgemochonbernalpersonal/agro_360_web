@props(['title' => null, 'message' => null, 'description' => null, 'icon' => 'inbox'])

<div class="p-12 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-agro-50 mb-4">
        <flux:icon :$icon class="size-8 text-agro-400" />
    </div>
    <flux:heading size="lg">{{ $title ?? $message ?? 'No hay registros' }}</flux:heading>
    @if($description)
        <flux:subheading class="mt-1">{{ $description }}</flux:subheading>
    @endif
    @if(isset($action) || $slot->isNotEmpty())
        <div class="mt-6">
            {{ isset($action) ? $action : $slot }}
        </div>
    @endif
</div>
