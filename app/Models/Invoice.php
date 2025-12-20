<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'client_address_id',
        'invoice_number',
        'delivery_note_code',
        'current_invoice_code',
        'current_delivery_note_code',
        'invoice_code_generated_at',
        'invoice_date',
        'due_date',
        'delivery_note_date',
        'payment_date',
        'order_date',
        'billing_address',
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_company_name',
        'billing_company_document',
        'billing_postal_code',
        'billing_city',
        'billing_state',
        'billing_country',
        'subtotal',
        'discount_amount',
        'tax_base',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_type',
        'payment_details',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_routing_number',
        'bank_payment_status',
        'delivery_status',
        'tracking_code',
        'sif_status',
        'sif_uuid',
        'sif_hash',
        'sif_sent_at',
        'sif_response',
        'sif_excluded',
        'is_verified_aet',
        'sent',
        'viewed',
        'delivery_viewed',
        'payment_status_viewed',
        'corrective',
        'gift',
        'observations',
        'observations_invoice',
        'invoice_group_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'delivery_note_date' => 'datetime',
        'payment_date' => 'datetime',
        'order_date' => 'datetime',
        'invoice_code_generated_at' => 'datetime',
        'sif_sent_at' => 'datetime',
        'subtotal' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'tax_base' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'bank_payment_status' => 'boolean',
        'sif_excluded' => 'boolean',
        'is_verified_aet' => 'boolean',
        'sent' => 'boolean',
        'viewed' => 'boolean',
        'delivery_viewed' => 'boolean',
        'payment_status_viewed' => 'boolean',
        'corrective' => 'boolean',
        'gift' => 'boolean',
    ];

    /**
     * Usuario (viticultor) propietario de la factura
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Dirección del cliente (snapshot)
     */
    public function clientAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class);
    }

    /**
     * Grupo de facturas
     */
    public function invoiceGroup(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class);
    }

    /**
     * Items de la factura
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Verificar si está pagada
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Verificar si está vencida
     */
    public function isOverdue(): bool
    {
        return $this->payment_status === 'overdue' || 
               ($this->due_date && $this->due_date->isPast() && $this->payment_status !== 'paid');
    }

    /**
     * Verificar si es borrador
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Scope para facturas de un usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para facturas pagadas
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope para facturas pendientes
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope para facturas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function($q) {
                $q->where('due_date', '<', now())
                  ->where('payment_status', '!=', 'paid');
            });
    }
}
