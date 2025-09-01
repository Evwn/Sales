<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SupplierResponse extends Model
{   use SoftDeletes;
        protected $fillable = ['quotation_id','supplier_id','total_amount','notes','status'];

    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(SupplierResponseItem::class); }
}
