<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chat;
use App\Models\ChatParticipant;

class ChatParticipantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing chats
        $chats = Chat::all();

        foreach ($chats as $chat) {
            // Add owner as participant
            if ($chat->owner_id) {
                ChatParticipant::firstOrCreate([
                    'chat_id' => $chat->id,
                    'user_id' => $chat->owner_id,
                ], [
                    'last_read_at' => $chat->last_message_at,
                    'unread_count' => 0
                ]);
            }

            // Add seller as participant
            if ($chat->seller_id) {
                ChatParticipant::firstOrCreate([
                    'chat_id' => $chat->id,
                    'user_id' => $chat->seller_id,
                ], [
                    'last_read_at' => $chat->last_message_at,
                    'unread_count' => 0
                ]);
            }
        }

        $this->command->info('Chat participants seeded successfully!');
    }
}
