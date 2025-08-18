<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVariantValue extends Model
{
    protected $fillable = ['item_variant_id', 'variant_value_id'];
    public function itemVariant() { return $this->belongsTo(ItemVariant::class); }
    public function variantValue() { return $this->belongsTo(VariantValue::class); }
} 