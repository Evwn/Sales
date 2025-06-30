<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIHandlerService;

class AIAssistantController extends Controller
{
    public function handle(Request $request, AIHandlerService $aiHandler)
    {
        $user = $request->user();
        if (!$user || !$user->roles()->where('name', 'owner')->exists()) {
            return response()->json(['answer' => 'Access denied. Only owners can use the AI assistant.']);
        }

        $question = $request->input('question');
        $answer = $aiHandler->handle($user, $question);

        try {
            \DB::beginTransaction();
            // Save to persistent AI chat
            $chat = \App\Models\Chat::firstOrCreate([
                'owner_id' => $user->id,
                'type' => 'ai',
            ]);
            // Save user message
            $userMsg = $chat->messages()->create([
                'user_id' => $user->id,
                'sender' => 'user',
                'message' => $question,
                'message_type' => 'text',
            ]);
            // Save AI answer
            $aiMsg = $chat->messages()->create([
                'user_id' => null,
                'sender' => 'ai',
                'message' => $answer,
                'message_type' => 'text',
            ]);
            // Update chat preview
            $chat->last_message_at = now();
            $chat->last_message_preview = $answer;
            $chat->save();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            // No logging
        }

        return response()->json(['answer' => $answer]);
    }
} 