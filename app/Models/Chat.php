<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'owner_id', 'seller_id', 'type', 'last_message_at', 'last_message_preview',
        'unread_count_owner', 'unread_count_seller', 'last_read_owner', 'last_read_seller'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_read_owner' => 'datetime',
        'last_read_seller' => 'datetime',
        'unread_count_owner' => 'integer',
        'unread_count_seller' => 'integer'
    ];

    public function messages() { return $this->hasMany(ChatMessage::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function participants() { return $this->hasMany(ChatParticipant::class); }

    // Helper methods for unread counts
    public function getUnreadCountForUser($userId)
    {
        if ($this->owner_id == $userId) {
            return $this->unread_count_owner;
        } elseif ($this->seller_id == $userId) {
            return $this->unread_count_seller;
        }
        return 0;
    }

    public function markAsReadForUser($userId)
    {
        if ($this->owner_id == $userId) {
            $this->update([
                'unread_count_owner' => 0,
                'last_read_owner' => now()
            ]);
        } elseif ($this->seller_id == $userId) {
            $this->update([
                'unread_count_seller' => 0,
                'last_read_seller' => now()
            ]);
        }
    }

    public function incrementUnreadCountForUser($userId)
    {
        if ($this->owner_id == $userId) {
            $this->increment('unread_count_owner');
        } elseif ($this->seller_id == $userId) {
            $this->increment('unread_count_seller');
        }
    }
} 