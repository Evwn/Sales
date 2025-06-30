<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;
    public $userId;
    public $isTyping;
    public $userName;

    /**
     * Create a new event instance.
     */
    public function __construct($chatId, $userId, $isTyping, $userName = null)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->isTyping = $isTyping;
        $this->userName = $userName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    public function broadcastAs()
    {
        return 'user.typing';
    }

    public function broadcastWith()
    {
        return [
            'chat_id' => $this->chatId,
            'user_id' => $this->userId,
            'is_typing' => $this->isTyping,
            'user_name' => $this->userName,
            'timestamp' => now()->toISOString()
        ];
    }
}
