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
            'label' => 'Campo y Cuaderno',
            'items' => [
                'withdrawal_period' => [
                    'label' => 'Plazos de seguridad',
                    'description' => 'Alertas cuando un tratamiento fitosanitario se acerca al fin del periodo de carencia',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'remote_sensing' => [
                    'label' => 'Teledetección (NDVI)',
                    'description' => 'Alertas cuando el vigor de una parcela baja del umbral configurado',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'notebook_access' => [
                    'label' => 'Acceso al cuaderno',
                    'description' => 'Solicitudes y respuestas de acceso al cuaderno de campo',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'harvest_alerts' => [
                    'label' => 'Alertas de vendimia',
                    'description' => 'Inicio de campaña, límites de rendimiento, entregas de uva',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
            ],
        ],
        'winery' => [
            'label' => 'Bodega',
            'items' => [
                'container_maintenance' => [
                    'label' => 'Mantenimiento de contenedores',
                    'description' => 'Alertas de mantenimiento programado próximo a vencer',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database'],
                ],
                'harvest_delivery' => [
                    'label' => 'Entregas de uva',
                    'description' => 'Creación, conciliación, disputas y resolución de entregas',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'regulatory' => [
                    'label' => 'Plazos regulatorios',
                    'description' => 'Recordatorios de INFOVI, SILICIE y otras obligaciones',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
            ],
        ],
        'business' => [
            'label' => 'Negocio',
            'items' => [
                'payment' => [
                    'label' => 'Pagos',
                    'description' => 'Confirmaciones de pago y cambios de suscripción',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'invoice' => [
                    'label' => 'Facturas',
                    'description' => 'Emisión de facturas de compra de uva',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'export' => [
                    'label' => 'Exportaciones',
                    'description' => 'Notificación cuando una exportación de datos está lista',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'report' => [
                    'label' => 'Informes oficiales',
                    'description' => 'Generación exitosa o fallida de informes',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database'],
                ],
            ],
        ],
        'system' => [
            'label' => 'Sistema',
            'items' => [
                'subscription' => [
                    'label' => 'Suscripción',
                    'description' => 'Vencimiento próximo de la suscripción',
                    'available_channels' => ['database', 'mail'],
                    'default_channels' => ['database', 'mail'],
                ],
                'support' => [
                    'label' => 'Soporte',
                    'description' => 'Tickets de soporte creados o respondidos',
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
