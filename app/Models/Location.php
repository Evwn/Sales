<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'location_type_id',
        'business_id',
        'branch_id',
        'address',
        'phone',
        'status',
    ];

    public function locationType()
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
public function branch()
{
    return $this->belongsTo(Branch::class, 'branch_id', 'id');
}


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

     public function StockItems(){
        return $this->hasMany(StockItem::class);
    }
} 