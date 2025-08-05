<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTicketPayment extends Model
{
    protected $fillable = [
        'ticket_id', 'method', 'amount', 'status', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(PosTicket::class, 'ticket_id');
    }
} 