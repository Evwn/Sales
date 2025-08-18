<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTicket extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'status', 'total_amount', 'amount_paid', 'amount_due', 'payment_details'
    ];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(PosTicketItem::class, 'ticket_id');
    }

    public function payments()
    {
        return $this->hasMany(PosTicketPayment::class, 'ticket_id');
    }
} 