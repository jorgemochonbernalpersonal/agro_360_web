<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicingSetting extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_prefix',
        'invoice_padding',
        'invoice_counter',
        'invoice_year_reset',
        'delivery_note_prefix',
        'delivery_note_padding',
        'delivery_note_counter',
        'delivery_note_year_reset',
        'last_reset_year',
    ];

    protected $casts = [
        'invoice_padding' => 'integer',
        'invoice_counter' => 'integer',
        'invoice_year_reset' => 'boolean',
        'delivery_note_padding' => 'integer',
        'delivery_note_counter' => 'integer',
        'delivery_note_year_reset' => 'boolean',
        'last_reset_year' => 'integer',
    ];

    /**
     * Usuario propietario de la configuración
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para obtener configuración de un usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Generar próximo código de factura
     */
    public function generateInvoiceCode(): string
    {
        $this->checkYearReset('invoice');
        
        $code = $this->replaceVariables($this->invoice_prefix) . 
                str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);
        
        return $code;
    }

    /**
     * Generar próximo código de albarán
     */
    public function generateDeliveryNoteCode(): string
    {
        $this->checkYearReset('delivery_note');
        
        $code = $this->replaceVariables($this->delivery_note_prefix) . 
                str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
        
        return $code;
    }

    /**
     * Incrementar contador de facturas
     */
    public function incrementInvoiceCounter(): void
    {
        $this->increment('invoice_counter');
    }

    /**
     * Incrementar contador de albaranes
     */
    public function incrementDeliveryNoteCounter(): void
    {
        $this->increment('delivery_note_counter');
    }

    /**
     * Resetear contador de facturas
     */
    public function resetInvoiceCounter(): void
    {
        $this->update([
            'invoice_counter' => 1,
            'last_reset_year' => now()->year
        ]);
    }

    /**
     * Resetear contador de albaranes
     */
    public function resetDeliveryNoteCounter(): void
    {
        $this->update([
            'delivery_note_counter' => 1,
            'last_reset_year' => now()->year
        ]);
    }
    
    /**
     * Generar código de factura e incrementar contador ATÓMICAMENTE
     * Previene race conditions usando lockForUpdate()
     */
    public function generateAndIncrementInvoiceCode(): string
    {
        return \DB::transaction(function () {
            // Lock row para prevenir race conditions
            $this->lockForUpdate()->fresh();
            
            $code = $this->generateInvoiceCode();
            $this->incrementInvoiceCounter();
            
            return $code;
        });
    }
    
    /**
     * Generar código de albarán e incrementar contador ATÓMICAMENTE
     * Previene race conditions usando lockForUpdate()
     */
    public function generateAndIncrementDeliveryNoteCode(): string
    {
        return \DB::transaction(function () {
            // Lock row para prevenir race conditions
            $this->lockForUpdate()->fresh();
            
            $code = $this->generateDeliveryNoteCode();
            $this->incrementDeliveryNoteCounter();
            
            return $code;
        });
    }

    /**
     * Verificar si necesita reseteo por año
     */
    protected function checkYearReset(string $type): void
    {
        $currentYear = now()->year;
        $shouldReset = $type === 'invoice' ? $this->invoice_year_reset : $this->delivery_note_year_reset;
        
        if ($shouldReset && $this->last_reset_year != $currentYear) {
            if ($type === 'invoice') {
                $this->resetInvoiceCounter();
            } else {
                $this->resetDeliveryNoteCounter();
            }
        }
    }

    /**
     * Reemplazar variables en el prefijo
     */
    protected function replaceVariables(string $prefix): string
    {
        $now = now();
        
        return str_replace(
            ['{YEAR}', '{MONTH}', '{DAY}'],
            [$now->format('Y'), $now->format('m'), $now->format('d')],
            $prefix
        );
    }

    /**
     * Obtener vista previa del código de factura
     */
    public function getInvoicePreview(): string
    {
        return $this->replaceVariables($this->invoice_prefix) . 
               str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener vista previa del código de albarán
     */
    public function getDeliveryNotePreview(): string
    {
        return $this->replaceVariables($this->delivery_note_prefix) . 
               str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
    }

    /**
     * Crear configuración por defecto para un usuario
     */
    public static function createDefaultForUser(int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'invoice_prefix' => 'FAC-' . date('Y') . '-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'invoice_year_reset' => true,
            'delivery_note_prefix' => 'ALB-' . date('Y') . '-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
            'delivery_note_year_reset' => true,
            'last_reset_year' => date('Y'),
        ]);
    }

    /**
     * Obtener o crear configuración para un usuario
     */
    public static function getOrCreateForUser(int $userId): self
    {
        $settings = self::forUser($userId)->first();
        
        if (!$settings) {
            $settings = self::createDefaultForUser($userId);
        }
        
        return $settings;
    }
}
