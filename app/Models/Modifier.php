<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = ['business_id', 'name'];
    public function options() { return $this->hasMany(ModifierOption::class); }
    public function items() { return $this->belongsToMany(Item::class, 'item_modifiers'); }
} 