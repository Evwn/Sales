<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\AIHandlerService;
use Illuminate\Support\Facades\DB;

class AIChatController extends Controller
{
    // Get or create the AI chat for the owner
    protected function getOrCreateAIChat($user)
    {
        return Chat::firstOrCreate([
            'owner_id' => $user->id,
            'type' => 'ai',
        ]);
    }

    // Get chat history (last 50 messages)
    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->roles()->where('name', 'owner')->exists()) {
            return response()->json(['error' => 'Only owners can use the AI assistant.'], 403);
        }
        $chat = $this->getOrCreateAIChat($user);
        $messages = $chat->messages()->orderBy('created_at')->take(50)->get();
        return response()->json($messages);
    }

    // Send a message to the AI
    public function send(Request $request, AIHandlerService $aiHandler)
    {
        $user = $request->user();
        if (!$user || !$user->roles()->where('name', 'owner')->exists()) {
            return response()->json(['error' => 'Only owners can use the AI assistant.'], 403);
        }
        $message = $request->input('message');
        if (!$message) {
            return response()->json(['error' => 'Message is required.'], 422);
        }
        $chat = $this->getOrCreateAIChat($user);
        DB::beginTransaction();
        try {
            // Save user message
            $userMsg = $chat->messages()->create([
                'user_id' => $user->id,
                'sender' => 'user',
                'message' => $message,
                'message_type' => 'text',
            ]);
            // Get last 10 messages for context
            $history = $chat->messages()->orderBy('created_at', 'desc')->take(10)->get()->reverse()->values();
            $context = [];
            foreach ($history as $msg) {
                $context[] = [
                    'role' => $msg->sender,
                    'content' => $msg->message,
                ];
            }
            // Get AI response
            $aiReply = $aiHandler->chat($user, $context);
            // Save AI message
            $aiMsg = $chat->messages()->create([
                'user_id' => null,
                'sender' => 'ai',
                'message' => $aiReply,
                'message_type' => 'text',
            ]);
            // Update chat preview
            $chat->last_message_at = now();
            $chat->last_message_preview = $aiReply;
            $chat->save();
            DB::commit();
            return response()->json([
                'user' => $userMsg,
                'ai' => $aiMsg,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to send message.'], 500);
        }
    }
} 