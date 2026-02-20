@props(['title', 'description' => null])

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
    <div>
        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
        @if($description)
            <flux:subheading>{{ $description }}</flux:subheading>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
