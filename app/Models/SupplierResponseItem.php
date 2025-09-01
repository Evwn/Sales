<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class SupplierResponseItem extends Model
{   use SoftDeletes;
        protected $fillable = ['supplier_response_id','quotation_item_id','unit_price','quantity','line_total'];

    public function response() { return $this->belongsTo(SupplierResponse::class); }
    public function quotationItem() { return $this->belongsTo(QuotationItem::class); }
}
