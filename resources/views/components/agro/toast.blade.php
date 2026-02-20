{{-- Toast notification container — JS logic in resources/js/toast.js --}}
<div
    x-data="toastNotifications()"
    x-init="init()"
    class="fixed bottom-4 left-4 z-[9999] space-y-3"
    style="max-width: 400px;"
>
    <template x-for="(notification, index) in notifications" :key="notification.id">
        <div
            x-show="notification.show"
            x-cloak
            @mouseenter="pause(notification.id)"
            @mouseleave="resume(notification.id)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-full"
            class="rounded-xl p-4 shadow-lg border-l-4 flex items-start gap-3 relative overflow-hidden"
            :class="{
                'bg-green-50 border-green-600 text-green-600': notification.type === 'success',
                'bg-red-50 border-red-600 text-red-600': notification.type === 'error',
                'bg-blue-50 border-blue-600 text-blue-600': notification.type === 'info',
                'bg-yellow-50 border-yellow-600 text-yellow-600': notification.type === 'warning'
            }"
        >
            <div class="flex-shrink-0">
                <template x-if="notification.type === 'success'">
                    <flux:icon icon="check-circle" variant="outline" class="size-5" />
                </template>
                <template x-if="notification.type === 'error'">
                    <flux:icon icon="x-circle" variant="outline" class="size-5" />
                </template>
                <template x-if="notification.type === 'info'">
                    <flux:icon icon="information-circle" variant="outline" class="size-5" />
                </template>
                <template x-if="notification.type === 'warning'">
                    <flux:icon icon="exclamation-triangle" variant="outline" class="size-5" />
                </template>
            </div>

            <p
                class="flex-1 text-sm font-medium"
                :class="{
                    'text-green-800': notification.type === 'success',
                    'text-red-800': notification.type === 'error',
                    'text-blue-800': notification.type === 'info',
                    'text-yellow-800': notification.type === 'warning'
                }"
                x-text="notification.message"
            ></p>

            <button @click="remove(notification.id)" class="text-zinc-400 hover:text-zinc-600">
                <flux:icon icon="x-mark" variant="micro" />
            </button>

            <div
                class="toast-progress"
                :class="{ 'toast-progress-paused': notification.paused }"
            ></div>
        </div>
    </template>
</div>
