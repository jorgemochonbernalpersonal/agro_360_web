@props(['message' => 'No hay registros', 'description' => null, 'icon' => 'inbox'])

<div class="p-12 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-agro-50 mb-4">
        <flux:icon :$icon class="size-8 text-agro-400" />
    </div>
    <flux:heading size="lg">{{ $message }}</flux:heading>
    @if($description)
        <flux:subheading class="mt-1">{{ $description }}</flux:subheading>
    @endif
    @if(isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
