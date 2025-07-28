<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    protected $fillable = ['item_id', 'name'];
    public function item() { return $this->belongsTo(Item::class); }
    public function values() { return $this->hasMany(VariantValue::class); }
} 