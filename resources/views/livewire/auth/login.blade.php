<div class="w-full max-w-md mx-auto"
     x-data="{ showPassword: false, captchaVerified: false }"
     @captcha-verified.window="captchaVerified = true"
     @captcha-expired.window="captchaVerified = false"
     @captcha-reset.window="captchaVerified = false">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
        <p class="mt-2 text-sm text-zinc-500">{{ __('Cuaderno de campo digital para viticultores') }}</p>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <flux:heading size="xl">{{ __('Iniciar Sesión') }}</flux:heading>
            <flux:subheading>{{ __('Accede a tu cuenta de Agro365') }}</flux:subheading>
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

        <form wire:submit="login" @submit.prevent method="post" class="space-y-4">

            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model="email" type="email"
                            :placeholder="__('correo@ejemplo.com')"
                            required autocomplete="email" autofocus />
            </flux:field>

            <flux:field>
                <div class="flex items-center justify-between mb-1">
                    <flux:label>{{ __('Contraseña') }}</flux:label>
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-agro-700 hover:text-agro-900 hover:underline font-medium">
                        {{ __('¿Olvidaste tu contraseña?') }}
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
                    <flux:callout.text>{{ __('Por seguridad, verifica que no eres un robot') }}</flux:callout.text>
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
                                 $wire.on('recaptcha-reset', () => {
                                     if (this.widgetId !== null && typeof grecaptcha !== 'undefined') {
                                         grecaptcha.reset(this.widgetId);
                                     }
                                     window.dispatchEvent(new CustomEvent('captcha-reset'));
                                 });
                             },
                             renderCaptcha() {
                                 if (this.widgetId === null && typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                                     this.widgetId = grecaptcha.render('recaptcha-container', {
                                         'sitekey': '{{ config('services.recaptcha.site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}',
                                         'callback': (token) => {
                                             @this.set('recaptchaToken', token);
                                             window.dispatchEvent(new CustomEvent('captcha-verified'));
                                         },
                                         'expired-callback': () => {
                                             @this.set('recaptchaToken', '');
                                             window.dispatchEvent(new CustomEvent('captcha-expired'));
                                         },
                                     });
                                 }
                             }
                         }">
                        <div id="recaptcha-container"></div>
                    </div>
                    <p x-show="captchaVerified" x-cloak
                       class="text-center text-sm text-green-600 font-medium mt-2 flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Verificado. Ahora pulsa Iniciar Sesión.') }}
                    </p>
                </flux:callout>
            @endif

            <div class="flex items-center">
                <flux:checkbox wire:model="remember" :label="__('Recordarme en este dispositivo')" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full"
                wire:loading.attr="disabled"
                x-bind:disabled="$wire.showCaptcha && !captchaVerified">
                <span wire:loading.remove wire:target="login">{{ __('Iniciar Sesión') }}</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    {{ __('Iniciando sesión...') }}
                </span>
            </flux:button>

        </form>

        {{-- Google Sign-in --}}
        <div class="mt-5 pt-5 border-t border-zinc-100">
            <a href="{{ route('auth.google.redirect') }}"
               class="flex items-center justify-center gap-3 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 hover:border-zinc-300 transition-colors">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                {{ __('Continuar con Google') }}
            </a>
        </div>

        <div class="mt-5 text-center">
            <flux:subheading>
                {{ __('¿No tienes cuenta?') }}
                <a href="{{ route('register') }}" class="text-agro-700 hover:text-agro-900 hover:underline font-semibold">
                    {{ __('Regístrate aquí') }}
                </a>
            </flux:subheading>
        </div>

    </x-agro.card>

</div>
