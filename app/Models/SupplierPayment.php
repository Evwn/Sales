<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
     protected $fillable = [
        'invoice_id', 'supplier_id', 'amount',
        'method', 'reference', 'payment_date', 'status'
    ];

    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'invoice_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
