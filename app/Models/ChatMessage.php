<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_id', 'user_id', 'sender', 'message', 'message_type', 'image_url', 'audio_url', 'file_url', 'metadata',
        'is_edited', 'is_deleted', 'delete_type', 'original_message', 'read_by'
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_by' => 'array',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    public function chat() { return $this->belongsTo(Chat::class); }
    public function user() { return $this->belongsTo(User::class); }

    // Helper methods for message management
    public function markAsRead($userId)
    {
        $readBy = $this->read_by ?? [];
        if (!in_array($userId, $readBy)) {
            $readBy[] = $userId;
            $this->update(['read_by' => $readBy]);
        }
    }

    public function isReadBy($userId)
    {
        $readBy = $this->read_by ?? [];
        return in_array($userId, $readBy);
    }

    public function editMessage($newMessage)
    {
        if (!$this->is_edited) {
            $this->update([
                'original_message' => $this->message,
                'is_edited' => true
            ]);
        }
        $this->update(['message' => $newMessage]);
    }

    public function deleteMessage($deleteType = 'for_me')
    {
        $this->update([
            'is_deleted' => true,
            'delete_type' => $deleteType
        ]);
    }

    public function getDisplayMessage()
    {
        if ($this->is_deleted) {
            if ($this->delete_type === 'for_all') {
                return 'This message was deleted';
            } else {
                return 'You deleted this message';
            }
        }
        return $this->message;
    }
} 