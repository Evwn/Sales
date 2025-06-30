<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    protected $fillable = [
        'chat_id',
        'user_id',
        'last_read_at',
        'unread_count',
        'is_typing'
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'is_typing' => 'boolean',
        'unread_count' => 'integer'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
