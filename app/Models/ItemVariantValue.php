<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVariantValue extends Model
{
    protected $fillable = ['item_variant_id', 'variant_value_id'];
    public function itemVariant() { return $this->belongsTo(ItemVariant::class); }
    public function variantValue() { return $this->belongsTo(VariantValue::class); }
} 