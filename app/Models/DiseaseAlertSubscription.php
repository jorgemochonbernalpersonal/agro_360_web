<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Suscripción pública (sin cuenta) a las alertas de mildiu y oídio de un municipio.
 *
 * @property string              $email
 * @property int                 $municipality_id
 * @property float               $lat
 * @property float               $lng
 * @property string              $token
 * @property \Carbon\Carbon|null $last_mildew_alert_at
 * @property \Carbon\Carbon|null $last_powdery_alert_at
 * @property \Carbon\Carbon|null $unsubscribed_at
 * @property string|null         $confirmation_token
 * @property \Carbon\Carbon|null $confirmed_at
 */
class DiseaseAlertSubscription extends Model
{
    protected $fillable = [
        'email',
        'municipality_id',
        'lat',
        'lng',
        'token',
        'last_mildew_alert_at',
        'last_powdery_alert_at',
        'unsubscribed_at',
        'confirmation_token',
        'confirmed_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'last_mildew_alert_at' => 'datetime',
        'last_powdery_alert_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /** @return BelongsTo<Municipality, $this> */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    /** @param Builder<DiseaseAlertSubscription> $query */
    public function scopeActive(Builder $query): void
    {
        // Doble opt-in: sin confirmar el email no se envían alertas reales a un
        // tercero que alguien haya podido suscribir sin su permiso.
        $query->whereNull('unsubscribed_at')->whereNotNull('confirmed_at');
    }

    public function unsubscribeUrl(): string
    {
        return route('tools.disease-alerts.unsubscribe', $this->token);
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscription) {
            $subscription->token ??= Str::random(48);
        });
    }
}
