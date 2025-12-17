# Configuración de PayPal - Agro365

## Descripción

Este sistema permite a los usuarios suscribirse a Agro365 mediante PayPal con dos planes disponibles:
- **Plan Mensual**: 8€/mes
- **Plan Anual**: 90€/año (ahorro de 6€)

## Configuración

### 1. Variables de Entorno

Agrega las siguientes variables a tu archivo `.env`:

```env
# PayPal Configuration
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=tu_client_id_sandbox
PAYPAL_SANDBOX_CLIENT_SECRET=tu_client_secret_sandbox
PAYPAL_LIVE_CLIENT_ID=tu_client_id_live
PAYPAL_LIVE_CLIENT_SECRET=tu_client_secret_live
PAYPAL_CURRENCY=EUR
PAYPAL_LOCALE=es_ES
```

### 2. Obtener Credenciales de PayPal

#### Modo Sandbox (Desarrollo/Pruebas):
1. Ve a [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Inicia sesión con tu cuenta de PayPal
3. Ve a "My Apps & Credentials"
4. Crea una nueva aplicación o usa la existente
5. Copia el **Client ID** y **Secret** de la aplicación Sandbox

#### Modo Live (Producción):
1. En el mismo dashboard, cambia a "Live"
2. Crea una aplicación para producción
3. Copia el **Client ID** y **Secret** de la aplicación Live

### 3. Configurar URLs de Retorno

En la configuración de tu aplicación PayPal, asegúrate de que las URLs de retorno sean:
- **Return URL**: `https://tudominio.com/payment/success`
- **Cancel URL**: `https://tudominio.com/payment/cancel`

## Estructura de Base de Datos

El sistema crea tres tablas nuevas:

### `user_profiles`
Almacena información adicional del usuario (dirección, teléfono, etc.)

### `subscriptions`
Gestiona las suscripciones activas de los usuarios

### `payments`
Registra todos los pagos realizados

## Funcionalidades

### Perfil de Usuario
- Los usuarios pueden editar su perfil y agregar información de dirección
- Ruta: `/profile/edit`

### Gestión de Suscripciones
- Los usuarios pueden ver sus planes disponibles y suscribirse
- Ruta: `/subscription/manage`
- Los usuarios pueden cancelar su suscripción activa

### Procesamiento de Pagos
- Los pagos se procesan automáticamente cuando el usuario completa el pago en PayPal
- Los callbacks se manejan en `/payment/success` y `/payment/cancel`

## Flujo de Pago

1. Usuario selecciona un plan (mensual o anual)
2. Usuario hace clic en "Pagar con PayPal"
3. Se crea una orden de pago en PayPal
4. Usuario es redirigido a PayPal para completar el pago
5. Después del pago, PayPal redirige de vuelta a `/payment/success`
6. El sistema captura el pago y crea la suscripción
7. Usuario es redirigido a la página de gestión de suscripciones

## Testing

Para probar en modo sandbox:
1. Usa las credenciales de sandbox en `.env`
2. Usa las tarjetas de prueba de PayPal:
   - Tarjeta: 4111 1111 1111 1111
   - CVV: Cualquier número de 3 dígitos
   - Fecha: Cualquier fecha futura

## Notas Importantes

- Asegúrate de cambiar `PAYPAL_MODE=live` en producción
- Las credenciales de sandbox y live son diferentes
- Los pagos en sandbox no son reales
- En producción, PayPal procesará pagos reales

