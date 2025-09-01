<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuotationResponse extends Model
{
        protected $fillable = ['item_id','unit_price','quotation_supplier_id','status'];

    public function supplier() { return $this->belongsTo(QuotationSupplier::class); }
}
