<div class="min-h-screen flex items-center justify-center bg-agro-50 py-6 px-4" x-data="{ showPassword: false }">
    <div class="w-full max-w-md mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="180" height="96" class="mx-auto max-h-24 object-contain group-hover:scale-105 transition-transform">
            </a>
            <flux:subheading class="mt-2">Cuaderno de campo digital para viticultores</flux:subheading>
        </div>

        {{-- Form --}}
        <x-agro.card>
            <div class="text-center mb-6">
                <flux:heading size="xl">Iniciar Sesión</flux:heading>
                <flux:subheading>Ingresa tus credenciales para continuar</flux:subheading>
            </div>

            <form wire:submit="login" class="space-y-5">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="correo@ejemplo.com" required />
                    <flux:error name="email" />
                </flux:field>

                {{-- Honeypot --}}
                <div style="position: absolute; left: -9999px;" aria-hidden="true" tabindex="-1">
                    <flux:input wire:model="honeypot" type="text" autocomplete="off" tabindex="-1" />
                </div>

                <flux:field>
                    <flux:label>Contraseña</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password" :type="'password'" x-bind:type="showPassword ? 'text' : 'password'" placeholder="••••••••" required />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                            <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="password" />
                </flux:field>

                {{-- reCAPTCHA --}}
                @if($showCaptcha)
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.text>Por motivos de seguridad, verifica que no eres un robot</flux:callout.text>
                        <div class="flex justify-center mt-3"
                             x-data="{
                                 widgetId: null,
                                 init() {
                                     if (typeof grecaptcha === 'undefined') {
                                         const s = document.createElement('script');
                                         s.src = 'https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit';
                                         s.async = true; s.defer = true;
                                         document.head.appendChild(s);
                                     } else { this.renderCaptcha(); }
                                     window.onRecaptchaLoad = () => this.renderCaptcha();
                                 },
                                 renderCaptcha() {
                                     if (this.widgetId === null && typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                                         this.widgetId = grecaptcha.render('recaptcha-container', {
                                             'sitekey': '{{ config('services.recaptcha.site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}',
                                             'callback': (token) => @this.set('recaptchaToken', token),
                                             'expired-callback': () => @this.set('recaptchaToken', ''),
                                         });
                                     }
                                 }
                             }">
                            <div id="recaptcha-container"></div>
                        </div>
                    </flux:callout>
                @endif

                <div class="flex items-center justify-between">
                    <flux:checkbox wire:model="remember" label="Recordarme" />
                    <a href="{{ route('password.request') }}" class="text-sm text-agro-700 hover:underline font-medium">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <flux:button type="submit" variant="primary" class="w-full">
                    Iniciar Sesión
                </flux:button>
            </form>

            <div class="mt-6 text-center">
                <flux:subheading>
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="text-agro-700 hover:underline font-semibold">Regístrate aquí</a>
                </flux:subheading>
            </div>
        </x-agro.card>
    </div>
</div>
