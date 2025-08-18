<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Requisition extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'location_id', 'user_id', 'approved_by', 'reference',
        'requisition_date', 'priority', 'notes', 'status'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

public static function generateReference()
{
    $prefix = 'REQ';
    $date = now()->format('Ymd');

    $lastReceipt = self::where('reference', 'like', "{$prefix}-{$date}-%")
        ->orderBy('reference', 'desc')
        ->first();

    if ($lastReceipt) {
        $lastNumber = (int) substr($lastReceipt->reference, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '0001';
    }

    return "{$prefix}-{$date}-{$newNumber}";
}

}
