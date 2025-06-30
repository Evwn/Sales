<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;
use App\Models\ChatParticipant;

class UpdateUnreadMessageCount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $excludeUserId;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $excludeUserId = null)
    {
        $this->chatId = $chatId;
        $this->excludeUserId = $excludeUserId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chat = Chat::find($this->chatId);
        if (!$chat) {
            return;
        }

        // Get all participants except the sender
        $participants = $chat->participants();
        if ($this->excludeUserId) {
            $participants = $participants->where('user_id', '!=', $this->excludeUserId);
        }

        $participants = $participants->get();

        foreach ($participants as $participant) {
            // Count unread messages for this participant
            $unreadCount = $chat->messages()
                ->where('user_id', '!=', $participant->user_id)
                ->where('created_at', '>', $participant->last_read_at ?? '1970-01-01')
                ->count();

            // Update participant's unread count
            $participant->update(['unread_count' => $unreadCount]);
        }

        // Update chat's total unread count
        $totalUnread = $chat->participants()->sum('unread_count');
        $chat->update(['unread_count' => $totalUnread]);
    }
}
