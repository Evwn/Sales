<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $chatId;
    public $deleteType;
    public $deletedBy;

    /**
     * Create a new event instance.
     */
    public function __construct($messageId, $chatId, $deleteType, $deletedBy)
    {
        $this->messageId = $messageId;
        $this->chatId = $chatId;
        $this->deleteType = $deleteType;
        $this->deletedBy = $deletedBy;
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
        return 'message.deleted';
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
            'delete_type' => $this->deleteType,
            'deleted_by' => $this->deletedBy,
            'timestamp' => now()->toISOString()
        ];
    }
}
