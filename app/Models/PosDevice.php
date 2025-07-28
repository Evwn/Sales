<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDevice extends Model
{
    use HasFactory;

    protected $table = 'pos_devices';

    protected $fillable = [
        'device_uuid',
        'business_id',
        'branch_id',
        'registered_by',
        'registered_at',
        'last_seen_at',
    ];

    public $timestamps = false;

    protected $dates = [
        'registered_at',
        'last_seen_at',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
} 