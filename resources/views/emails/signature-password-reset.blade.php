@component('mail::message')
# 🔐 {{ __('Contraseña de Firma Digital Reseteada') }}

{{ __('Hola') }} **{{ $userName }}**,

{{ __('Te informamos que tu **contraseña de firma digital** ha sido reseteada exitosamente.') }}

## 📊 {{ __('Detalles del Reseteo') }}

- **{{ __('Fecha y hora:') }}** {{ $resetDate }}
- **{{ __('Dirección IP:') }}** {{ $ipAddress }}
- **{{ __('Navegador:') }}** {{ $browser }}
- **{{ __('Dispositivo:') }}** {{ $device }}

## 🔒 {{ __('¿Qué significa esto?') }}

{{ __('Tu contraseña anterior para firmar documentos oficiales ha sido eliminada. Ahora puedes crear una nueva contraseña de firma desde tu panel de configuración.') }}

@component('mail::button', ['url' => route('viticulturist.settings', ['tab' => 'signature'])])
{{ __('Crear Nueva Contraseña de Firma') }}
@endcomponent

## ⚠️ {{ __('¿No fuiste tú?') }}

{{ __('Si **NO realizaste** este cambio, tu cuenta podría estar comprometida. Por favor:') }}

1. {{ __('Cambia inmediatamente tu contraseña de login') }}
2. {{ __('Revisa tu actividad reciente') }}
3. {{ __('Contacta con nuestro equipo de soporte') }}

@component('mail::panel')
**{{ __('Recordatorio:') }}** {{ __('La contraseña de firma digital es diferente a tu contraseña de login. Se usa exclusivamente para firmar documentos oficiales y tiene un nivel adicional de seguridad.') }}
@endcomponent

---

{{ __('Gracias por usar :app,', ['app' => 'Agro365']) }}<br>
{{ __('El equipo de :app', ['app' => config('app.name')]) }}

@component('mail::subcopy')
{{ __('Si tienes problemas con el botón, copia y pega esta URL en tu navegador:') }}
{{ route('viticulturist.settings', ['tab' => 'signature']) }}
@endcomponent
@endcomponent
