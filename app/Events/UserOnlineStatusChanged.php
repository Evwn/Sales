<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnlineStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $isOnline;
    public $lastSeenAt;
    public $onlineStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $isOnline, $lastSeenAt = null, $onlineStatus = null)
    {
        $this->userId = $userId;
        $this->isOnline = $isOnline;
        $this->lastSeenAt = $lastSeenAt;
        $this->onlineStatus = $onlineStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'user.status.changed';
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'is_online' => $this->isOnline,
            'last_seen_at' => $this->lastSeenAt,
            'online_status' => $this->onlineStatus,
            'timestamp' => now()->toISOString()
        ];
    }
}
