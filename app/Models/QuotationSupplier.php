<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class QuotationSupplier extends Model
{ 
    protected $fillable = ['quotation_id','supplier_id','status'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function responses() { return $this->hasMany(QuotationResponse::class); }
        public function items() { return $this->hasMany(SupplierResponseItem::class); }
}
