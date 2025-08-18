<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantOption extends Model
{
    protected $fillable = ['item_id', 'name'];
    public function item() { return $this->belongsTo(Item::class); }
    public function values() { return $this->hasMany(VariantValue::class); }
} 