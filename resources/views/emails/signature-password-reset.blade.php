@component('mail::message')
# 🔐 Contraseña de Firma Digital Reseteada

Hola **{{ $userName }}**,

Te informamos que tu **contraseña de firma digital** ha sido reseteada exitosamente.

## 📊 Detalles del Reseteo

- **Fecha y hora:** {{ $resetDate }}
- **Dirección IP:** {{ $ipAddress }}
- **Navegador:** {{ $browser }}
- **Dispositivo:** {{ $device }}

## 🔒 ¿Qué significa esto?

Tu contraseña anterior para firmar documentos oficiales ha sido eliminada. Ahora puedes crear una nueva contraseña de firma desde tu panel de configuración.

@component('mail::button', ['url' => route('viticulturist.settings', ['tab' => 'signature'])])
Crear Nueva Contraseña de Firma
@endcomponent

## ⚠️ ¿No fuiste tú?

Si **NO realizaste** este cambio, tu cuenta podría estar comprometida. Por favor:

1. Cambia inmediatamente tu contraseña de login
2. Revisa tu actividad reciente
3. Contacta con nuestro equipo de soporte

@component('mail::panel')
**Recordatorio:** La contraseña de firma digital es diferente a tu contraseña de login. Se usa exclusivamente para firmar documentos oficiales y tiene un nivel adicional de seguridad.
@endcomponent

---

Gracias por usar Agro365,<br>
El equipo de {{ config('app.name') }}

@component('mail::subcopy')
Si tienes problemas con el botón, copia y pega esta URL en tu navegador:
{{ route('viticulturist.settings', ['tab' => 'signature']) }}
@endcomponent
@endcomponent
