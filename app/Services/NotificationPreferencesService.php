<?php

namespace App\Services;

use App\Models\User;

class NotificationPreferencesService
{
    /**
     * Categorías disponibles, agrupadas por sección UI.
     * Cada categoría tiene: label, description, canales disponibles, canales por defecto.
     */
    public const CATEGORIES = [
        'field' => [
            'label' => __('Campo y Cuaderno'),
            'items' => [
                'withdrawal_period' => [
                    'label' => __('Plazos de seguridad'),
                    'description' => __('Alertas cuando un tratamiento fitosanitario se acerca al fin del periodo de carencia'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'remote_sensing' => [
                    'label' => __('Teledetección (NDVI)'),
                    'description' => __('Alertas cuando el vigor de una parcela baja del umbral configurado'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'notebook_access' => [
                    'label' => __('Acceso al cuaderno'),
                    'description' => __('Solicitudes y respuestas de acceso al cuaderno de campo'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'harvest_alerts' => [
                    'label' => __('Alertas de vendimia'),
                    'description' => __('Inicio de campaña, límites de rendimiento, entregas de uva'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
            ],
        ],
        'winery' => [
            'label' => __('Bodega'),
            'items' => [
                'container_maintenance' => [
                    'label' => __('Mantenimiento de contenedores'),
                    'description' => __('Alertas de mantenimiento programado próximo a vencer'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database'],
                ],
                'harvest_delivery' => [
                    'label' => __('Entregas de uva'),
                    'description' => __('Creación, conciliación, disputas y resolución de entregas'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'regulatory' => [
                    'label' => __('Plazos regulatorios'),
                    'description' => __('Recordatorios de INFOVI, SILICIE y otras obligaciones'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
            ],
        ],
        'business' => [
            'label' => __('Negocio'),
            'items' => [
                'payment' => [
                    'label' => __('Pagos'),
                    'description' => __('Confirmaciones de pago y cambios de suscripción'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'invoice' => [
                    'label' => __('Facturas'),
                    'description' => __('Emisión de facturas de compra de uva'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'export' => [
                    'label' => __('Exportaciones'),
                    'description' => __('Notificación cuando una exportación de datos está lista'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'report' => [
                    'label' => __('Informes oficiales'),
                    'description' => __('Generación exitosa o fallida de informes'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database'],
                ],
            ],
        ],
        'system' => [
            'label' => __('Sistema'),
            'items' => [
                'subscription' => [
                    'label' => __('Suscripción'),
                    'description' => __('Vencimiento próximo de la suscripción'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'support' => [
                    'label' => __('Soporte'),
                    'description' => __('Tickets de soporte creados o respondidos'),
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
            ],
        ],
    ];

    /**
     * Devuelve las preferencias efectivas (merge de defaults + guardadas).
     */
    public static function getEffective(User $user): array
    {
        $saved = $user->notification_preferences ?? [];
        $channels = $saved['channels'] ?? [];

        $effective = [];
        foreach (self::CATEGORIES as $section) {
            foreach ($section['items'] as $key => $cat) {
                $effective[$key] = $channels[$key] ?? $cat['default_channels'];
            }
        }

        return [
            'channels' => $effective,
            'delivery' => $saved['delivery'] ?? 'instant',
        ];
    }

    /**
     * Guarda las preferencias del usuario.
     */
    public static function save(User $user, array $channelPrefs, string $delivery = 'instant'): void
    {
        $user->update([
            'notification_preferences' => [
                'channels' => $channelPrefs,
                'delivery' => $delivery,
            ],
        ]);
    }

    /**
     * Comprueba si un canal está habilitado para una categoría.
     */
    public static function isChannelEnabled(User $user, string $category, string $channel): bool
    {
        $effective = self::getEffective($user);
        $channels = $effective['channels'][$category] ?? [];

        return in_array($channel, $channels);
    }

    /**
     * Devuelve las categorías relevantes para un rol.
     */
    public static function getCategoriesForRole(string $role): array
    {
        $roleSections = match ($role) {
            'viticulturist' => ['field', 'business', 'system'],
            'winery' => ['winery', 'business', 'system'],
            'producer' => ['campo', 'winery', 'business', 'system'],
            default => ['system'],
        };

        return array_intersect_key(self::CATEGORIES, array_flip($roleSections));
    }
}
