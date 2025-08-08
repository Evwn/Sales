<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Location;


class StockTransfer extends Model
{
        protected $fillable = [
    'reference',
	'from_location_id',
	'to_location_id',
	'status',
	'notes',
    ];
        public static function generateReference()
    {
        return 'ST-' . strtoupper(uniqid());
    }
    /**
     * Get the items associated with the stock transfer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
        public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function fromStore()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toStore()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }    

} 