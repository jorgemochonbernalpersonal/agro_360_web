<?php

namespace App\Notifications\Concerns;

use App\Models\User;

/**
 * Trait para que las notificaciones respeten las preferencias del usuario.
 *
 * Cada notificación declara una categoría (`notificationCategory()`).
 * El trait filtra los canales según las preferencias almacenadas en
 * `users.notification_preferences`.
 *
 * Estructura de preferencias:
 * [
 *     'channels' => [
 *         'category_key' => ['database', 'mail'],   // activos
 *     ],
 *     'delivery' => 'instant' | 'digest',
 *     'quiet_hours' => ['start' => '22:00', 'end' => '08:00'],
 * ]
 */
trait RespectsPreferences
{
    /**
     * Categoría de esta notificación. Sobrescribir en cada clase.
     */
    public function notificationCategory(): string
    {
        return 'general';
    }

    /**
     * Filtra canales según las preferencias del usuario.
     * Si no hay preferencias, devuelve los canales originales (opt-out model).
     */
    protected function filterChannelsByPreferences(object $notifiable, array $defaultChannels): array
    {
        if (! $notifiable instanceof User) {
            return $defaultChannels;
        }

        $prefs = $notifiable->notification_preferences;

        if (empty($prefs) || ! isset($prefs['channels'])) {
            return $defaultChannels;
        }

        $category = $this->notificationCategory();
        $allowedChannels = $prefs['channels'][$category] ?? null;

        // Si la categoría no está en preferencias, usar defaults (opt-out)
        if ($allowedChannels === null) {
            return $defaultChannels;
        }

        // Si el usuario desactivó todo para esta categoría
        if (empty($allowedChannels)) {
            // Siempre mantener database para que quede registro
            return ['database'];
        }

        $channels = array_values(array_intersect($defaultChannels, $allowedChannels));

        // Digest mode: defer mail to daily digest command, keep only database
        $delivery = $prefs['delivery'] ?? 'instant';
        if ($delivery === 'digest') {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        return $channels;
    }
}
