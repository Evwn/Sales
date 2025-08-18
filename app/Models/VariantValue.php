<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantValue extends Model
{
    protected $fillable = ['variant_option_id', 'value'];
    public function option() { return $this->belongsTo(VariantOption::class, 'variant_option_id'); }
    public function itemVariantValues() { return $this->hasMany(ItemVariantValue::class); }
} 