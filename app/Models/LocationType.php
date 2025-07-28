<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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