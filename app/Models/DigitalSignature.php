<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class DigitalSignature extends Model
{
    protected $fillable = [
        'user_id',
        'signature_password',
    ];

    protected $hidden = [
        'signature_password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar la contraseña de firma
     */
    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->signature_password);
    }

    /**
     * Establecer la contraseña de firma (se hashea automáticamente)
     */
    public function setSignaturePasswordAttribute($value): void
    {
        $this->attributes['signature_password'] = Hash::make($value);
    }

    /**
     * Obtener o crear la firma digital para un usuario
     */
    public static function forUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Crear o actualizar la firma digital de un usuario
     * El mutator setSignaturePasswordAttribute hashea automáticamente la contraseña
     */
    public static function createOrUpdateForUser(int $userId, string $password): self
    {
        $signature = static::forUser($userId);

        if ($signature) {
            // El mutator hasheará automáticamente la contraseña
            $signature->signature_password = $password;
            $signature->save();
            return $signature->fresh();
        }

        // El mutator hasheará automáticamente la contraseña
        return static::create([
            'user_id' => $userId,
            'signature_password' => $password,
        ]);
    }
}
