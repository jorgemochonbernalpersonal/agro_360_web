<x-app-layout>
    <div class="p-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border-2 border-[var(--color-agro-green-light)]/30">
        <div class="flex items-center gap-4 mb-6">
            <div class="text-5xl">👤</div>
            <div>
                <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)]">
                    Perfil
                </h1>
                <p class="text-[var(--color-agro-green)] text-lg">
                    Información de tu cuenta
                </p>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-bright)]/50 rounded-xl p-6 mb-6 border border-[var(--color-agro-green-light)]">
            <p class="text-[var(--color-agro-green-dark)] text-lg mb-2">
                👤 Bienvenido, <strong class="text-xl">{{ auth()->user()->name }}</strong>
            </p>
            <p class="text-[var(--color-agro-green)] font-semibold">
                Rol: <span class="bg-white px-3 py-1 rounded-full">{{ auth()->user()->role }}</span>
            </p>
            <p class="text-gray-700 mt-4">
                Email: {{ auth()->user()->email }}
            </p>
        </div>
    </div>
</x-app-layout>

