<div class="w-full max-w-md mx-auto" x-data="{ showPassword: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
        <p class="mt-2 text-sm text-zinc-500">Cuaderno de campo digital para viticultores</p>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <flux:heading size="xl">Iniciar Sesión</flux:heading>
            <flux:subheading>Accede a tu cuenta de Agro365</flux:subheading>
        </div>

        {{-- Error de autenticación (credenciales, rate limit, captcha) --}}
        @error('email')
            <flux:callout variant="danger" icon="x-circle" class="mb-5">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        {{-- Honeypot --}}
        <div style="position: absolute; left: -9999px;" aria-hidden="true" tabindex="-1">
            <flux:input wire:model="honeypot" type="text" autocomplete="off" tabindex="-1" />
        </div>

        <form wire:submit="login" class="space-y-4">

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input wire:model="email" type="email"
                            placeholder="correo@ejemplo.com"
                            required autocomplete="email" autofocus />
            </flux:field>

            <flux:field>
                <div class="flex items-center justify-between mb-1">
                    <flux:label>Contraseña</flux:label>
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-agro-700 hover:text-agro-900 hover:underline font-medium">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <div class="relative">
                    <flux:input wire:model="password"
                                :type="'password'"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                required autocomplete="current-password" />
                    <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                        <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
            </flux:field>

            {{-- reCAPTCHA tras varios intentos fallidos --}}
            @if($showCaptcha)
                <flux:callout variant="warning" icon="shield-exclamation">
                    <flux:callout.text>Por seguridad, verifica que no eres un robot</flux:callout.text>
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

            <div class="flex items-center">
                <flux:checkbox wire:model="remember" label="Recordarme en este dispositivo" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    Iniciando sesión...
                </span>
            </flux:button>

        </form>

        <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
            <flux:subheading>
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="text-agro-700 hover:text-agro-900 hover:underline font-semibold">
                    Regístrate aquí
                </a>
            </flux:subheading>
        </div>

    </x-agro.card>

</div>
