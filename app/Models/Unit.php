<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    protected $fillable = ['name', 'short_code', 'description', 'is_active', 'owner_id'];
    public function items() { return $this->hasMany(Item::class); }
    public function itemVariants() { return $this->hasMany(ItemVariant::class); }
} 