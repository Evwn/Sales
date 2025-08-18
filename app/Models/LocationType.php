<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'user_id',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class, 'location_type_id');
    }
} 