<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SifRecord extends Model
{
    protected $fillable = [
        'invoice_id',
        'tipo_registro',
        'csv',
        'huella',
        'registro_aeat',
        'hash_registro',
        'request_xml',
        'response_xml',
        'status',
        'error_message',
    ];

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isOk(): bool
    {
        return $this->status === 'OK';
    }

    public function isError(): bool
    {
        return $this->status === 'ER';
    }

    public function isPending(): bool
    {
        return $this->status === 'WD';
    }
}
