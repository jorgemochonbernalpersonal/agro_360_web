<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700']) }}>
    @if(isset($header))
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $header }}
        </h3>
    </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if(isset($footer))
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
        {{ $footer }}
    </div>
    @endif
</div>
