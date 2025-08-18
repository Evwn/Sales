<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModifierOption extends Model
{
    protected $fillable = ['modifier_id', 'name', 'price'];
    public function modifier() { return $this->belongsTo(Modifier::class); }
} 