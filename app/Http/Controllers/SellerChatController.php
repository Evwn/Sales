<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\ChatParticipant;
use Illuminate\Support\Facades\DB;
use App\Events\MessageRead;
use App\Events\UserTyping;
use App\Events\UserOnlineStatusChanged;
use App\Events\MessageEdited;
use App\Events\MessageDeleted;

class SellerChatController extends Controller
{
    // Get or create a chat between owner and seller
    protected function getOrCreateSellerChat($ownerId, $sellerId)
    {
        // Try to find existing chat first
        $chat = Chat::where('owner_id', $ownerId)
                   ->where('seller_id', $sellerId)
                   ->where('type', 'seller')
                   ->first();
        
        if ($chat) {
            return $chat;
        }
        
        // Create new chat if not found
        return Chat::create([
            'owner_id' => $ownerId,
            'seller_id' => $sellerId,
            'type' => 'seller',
            'unread_count_owner' => 0,
            'unread_count_seller' => 0
        ]);
    }

    // Get chat history (last 50 messages)
    public function history(Request $request, $sellerId)
    {
        $user = $request->user();
        $seller = User::findOrFail($sellerId);
        
        $isOwner = $user->roles()->where('name', 'owner')->exists() && 
                   in_array($seller->business_id, $user->ownedBusinesses()->pluck('id')->toArray());
        $isSeller = $user->id === $seller->id && $user->roles()->where('name', 'seller')->exists();
        
        if (!$isOwner && !$isSeller) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        
        $chat = $this->getOrCreateSellerChat($user->id, $seller->id);
        $messages = $chat->messages()->orderBy('created_at')->take(50)->get();
        return response()->json($messages);
    }

    // Send a message (text, image, or audio)
    public function send(Request $request, $sellerId)
    {
        $user = $request->user();
        $seller = User::findOrFail($sellerId);
        
        $isOwner = $user->roles()->where('name', 'owner')->exists() && 
                   in_array($seller->business_id, $user->ownedBusinesses()->pluck('id')->toArray());
        $isSeller = $user->id === $seller->id && $user->roles()->where('name', 'seller')->exists();
        
        if (!$isOwner && !$isSeller) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        
        $chat = $this->getOrCreateSellerChat($user->id, $seller->id);
        $data = $request->only(['message', 'message_type', 'image_url', 'audio_url', 'file_url', 'metadata']);
        if (empty($data['message']) && empty($data['image_url']) && empty($data['audio_url']) && empty($data['file_url'])) {
            return response()->json(['error' => 'Message or media required.'], 422);
        }
        $data['user_id'] = $user->id;
        $data['sender'] = $isOwner ? 'owner' : 'seller';
        $data['message_type'] = $data['message_type'] ?? 'text';
        DB::beginTransaction();
        try {
            $msg = $chat->messages()->create($data);
            // Broadcast event for real-time updates
            event(new \App\Events\MessageSent($msg));
            // Update chat preview
            $preview = $data['message'] ?? ($data['image_url'] ? '[Image]' : ($data['audio_url'] ? '[Audio]' : '[File]'));
            $chat->last_message_at = now();
            $chat->last_message_preview = $preview;
            $chat->save();
            DB::commit();
            return response()->json($msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to send message.'], 500);
        }
    }

    // Get general chat history (last 50 messages) - for general chat
    public function generalHistory(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        
        // Check if user is owner or seller
        $isOwner = $user->roles()->where('name', 'owner')->exists();
        $isSeller = $user->roles()->where('name', 'seller')->exists();
        
        if (!$isOwner && !$isSeller) {
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }
        
        if ($isOwner) {
            // Owner: get all sellers from their businesses through branches
            $sellers = User::whereHas('roles', function($query) {
                            $query->where('name', 'seller');
                        })
                        ->whereHas('branch', function($query) use ($user) {
                            $query->whereHas('business', function($businessQuery) use ($user) {
                                $businessQuery->where('owner_id', $user->id);
                            });
                        })
                        ->with(['branch.business'])
                        ->get();
            
            // Get existing chats for last message previews and unread counts
            $existingChats = Chat::where('owner_id', $user->id)
                            ->where('type', 'seller')
                            ->with(['seller', 'messages' => function($query) {
                                $query->orderBy('created_at', 'desc')->take(1);
                            }])
                            ->get()
                            ->keyBy('seller_id');
            
            // Build seller list with chat previews and unread counts
            $sellerChatsArray = $sellers->map(function($seller) use ($existingChats, $user) {
                $chat = $existingChats->get($seller->id);
                $lastMessage = $chat ? $chat->messages->first() : null;
                
                // Format name with branch: "Jane (Kilinani)"
                $branchName = $seller->branch ? $seller->branch->name : 'Unknown Branch';
                $displayName = $seller->name . ' (' . $branchName . ')';
                
                return [
                    'id' => $seller->id,
                    'chat_id' => $chat ? $chat->id : null,
                    'name' => $displayName,
                    'email' => $seller->email,
                    'avatar' => $seller->avatar,
                    'last_message_preview' => $lastMessage ? $lastMessage->message : 'No messages yet',
                    'unread_count' => $chat ? $chat->getUnreadCountForUser($user->id) : 0,
                    'is_ai' => false,
                    'branch_name' => $branchName
                ];
            })->toArray();
            
            // Get AI chat history
            $aiChat = Chat::firstOrCreate([
                'owner_id' => $user->id,
                'type' => 'ai',
            ]);
            
            $aiMessages = $aiChat->messages()->orderBy('created_at', 'desc')->take(1)->get();
            
            // Get last AI message for preview
            $lastAiMessage = $aiMessages->first();
            
            $aiChatData = [
                'id' => 'ai', // Use 'ai' as identifier
                'type' => 'ai',
                'name' => 'AI Assistant',
                'avatar' => null,
                'last_message_preview' => $lastAiMessage ? $lastAiMessage->message : 'Ask me anything!',
                'unread_count' => 0, // AI chat doesn't have unread counts
                'is_ai' => true
            ];
            
            return response()->json([
                'type' => 'owner',
                'chats' => $sellerChatsArray,
                'ai_chat' => $aiChatData,
                'user' => $user
            ]);
        } else {
            // Seller: get chat with their owner only (no AI access)
            $ownerChat = Chat::where('seller_id', $user->id)
                       ->where('type', 'seller')
                       ->with(['owner', 'messages' => function($query) {
                           $query->orderBy('created_at')->take(50);
                       }])
                       ->first();
            
            if ($ownerChat) {
                $ownerChat->unread_count = $ownerChat->getUnreadCountForUser($user->id);
            }
            
            return response()->json([
                'type' => 'seller',
                'owner_chat' => $ownerChat,
                'user' => $user
            ]);
        }
    }

    // Get specific chat messages between owner and seller
    public function getChatMessages(Request $request, $sellerId = null)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized - No user found.'], 401);
        }
        
        $isOwner = $user->roles()->where('name', 'owner')->exists();
        $isSeller = $user->roles()->where('name', 'seller')->exists();
        
        if (!$isOwner && !$isSeller) {
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }
        
        $limit = intval($request->input('limit', 20));
        $limit = min(max($limit, 1), 100); // Clamp between 1 and 100
        $before = $request->input('before');

        // Check if this is an AI chat request
        if ($sellerId === 'ai') {
            // Only owners can access AI chat
            if (!$isOwner) {
                return response()->json(['error' => 'Unauthorized - Only owners can access AI chat.'], 403);
            }
            
            // Get or create AI chat for the user
            $aiChat = Chat::firstOrCreate([
                'owner_id' => $user->id,
                'type' => 'ai',
            ]);
            $query = $aiChat->messages()->orderByDesc('id');
            if ($before) {
                $query->where('id', '<', $before);
            }
            $queryStart = microtime(true);
            $messages = $query->take($limit + 1)->get();
            $queryEnd = microtime(true);
            $hasMore = $messages->count() > $limit;
            $messages = $messages->take($limit)->reverse()->values();
            return response()->json([
                'chat' => $aiChat,
                'messages' => $messages,
                'is_ai' => true,
                'ai_name' => 'AI Assistant',
                'has_more' => $hasMore
            ]);
        }
        
        if ($isOwner && $sellerId) {
            // Owner requesting chat with specific seller
            $seller = User::findOrFail($sellerId);
            
            // Check if seller belongs to owner through: Seller → Branch → Business → Owner
            if (!$seller->branch || !$seller->branch->business || $seller->branch->business->owner_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - Seller not in your business.'], 403);
            }
            
            $chat = $this->getOrCreateSellerChat($user->id, $seller->id);
            $query = $chat->messages()->orderByDesc('id');
            if ($before) {
                $query->where('id', '<', $before);
            }
            $queryStart = microtime(true);
            $messages = $query->take($limit + 1)->get();
            $queryEnd = microtime(true);
            $hasMore = $messages->count() > $limit;
            $messages = $messages->take($limit)->reverse()->values();
            return response()->json([
                'chat' => $chat,
                'messages' => $messages,
                'seller' => $seller,
                'is_ai' => false,
                'has_more' => $hasMore
            ]);
        } else if ($isSeller) {
            // Seller requesting their chat with owner
            $chat = Chat::where('seller_id', $user->id)
                       ->where('type', 'seller')
                       ->first();
            
            if (!$chat) {
                return response()->json(['error' => 'Chat not found.'], 404);
            }
            
            $query = $chat->messages()->orderByDesc('id');
            if ($before) {
                $query->where('id', '<', $before);
            }
            $queryStart = microtime(true);
            $messages = $query->take($limit + 1)->get();
            $queryEnd = microtime(true);
            $hasMore = $messages->count() > $limit;
            $messages = $messages->take($limit)->reverse()->values();
            return response()->json([
                'chat' => $chat,
                'messages' => $messages,
                'owner' => $chat->owner,
                'is_ai' => false,
                'has_more' => $hasMore
            ]);
        }
        
        return response()->json(['error' => 'Invalid request.'], 400);
    }

    // Send a general message (text, image, or audio) - for general chat
    public function generalSend(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized - No user found.'], 401);
            }
            
            $message = $request->input('message');
            $sellerId = $request->input('seller_id'); // For owner sending to specific seller
            $isAiChat = $request->input('is_ai', false); // Check if this is AI chat
            
            if (!$message) {
                return response()->json(['error' => 'Message is required.'], 422);
            }
            
            $isOwner = $user->roles()->where('name', 'owner')->exists();
            $isSeller = $user->roles()->where('name', 'seller')->exists();
            
            if (!$isOwner && !$isSeller) {
                return response()->json(['error' => 'Unauthorized role.'], 403);
            }
            
            // Route AI chat to AIChatController
            if ($isAiChat) {
                // Only owners can use AI chat
                if (!$isOwner) {
                    return response()->json(['error' => 'Unauthorized - Only owners can use AI chat.'], 403);
                }
                
                try {
                    // Get or create AI chat
                    $aiChat = Chat::firstOrCreate([
                        'owner_id' => $user->id,
                        'type' => 'ai',
                    ]);
                    
                    // Save user message immediately
                    $userMsg = $aiChat->messages()->create([
                        'user_id' => $user->id,
                        'sender' => 'user',
                        'message' => $message,
                        'message_type' => 'text',
                    ]);
                    
                    // Update chat preview immediately
                    $aiChat->update([
                        'last_message_at' => now(),
                        'last_message_preview' => $message
                    ]);
                    
                    // Try to broadcast user message immediately
                    try {
                        event(new \App\Events\MessageSent($userMsg));
                    } catch (\Exception $e) {
                        // Don't let broadcasting failure stop the message from being sent
                    }
                    
                    // Try to get AI response with timeout
                    try {
                        // Check if AI service is available
                        $aiHandler = app(\App\Services\AIHandlerService::class);
                        
                        // Get last 10 messages for context
                        $history = $aiChat->messages()->orderBy('created_at', 'desc')->take(10)->get()->reverse()->values();
                        $context = [];
                        foreach ($history as $msg) {
                            $context[] = [
                                'role' => $msg->sender,
                                'content' => $msg->message,
                            ];
                        }
                        
                        // Set a timeout for AI response (5 seconds)
                        $aiReply = null;
                        $timeout = 5; // 5 seconds timeout
                        
                        // Try to get AI response with timeout
                        $startTime = time();
                        try {
                            $aiReply = $aiHandler->chat($user, $context);
                        } catch (\Exception $e) {
                            $aiReply = "Sorry, I'm having trouble connecting to the AI service right now. Please try again later.";
                        }
                        
                        // If we got a response, save it
                        if ($aiReply) {
                            $aiMsg = $aiChat->messages()->create([
                                'user_id' => null,
                                'sender' => 'ai',
                                'message' => $aiReply,
                                'message_type' => 'text',
                            ]);
                            
                            // Update chat preview
                            $aiChat->update([
                                'last_message_at' => now(),
                                'last_message_preview' => $aiReply
                            ]);
                            
                            // Broadcast AI message
                            try {
                                event(new \App\Events\MessageSent($aiMsg));
                            } catch (\Exception $e) {
                                // Don't let broadcasting failure stop the message from being sent
                            }
                            
                            return response()->json([
                                'user' => $userMsg,
                                'ai' => $aiMsg,
                                'ai_processing' => false
                            ]);
                        }
                        
                    } catch (\Exception $e) {
                        // Don't let AI processing error stop the message from being sent
                    }
                    
                    // If AI service is not available or times out, return error message
                    $errorMsg = $aiChat->messages()->create([
                        'user_id' => null,
                        'sender' => 'ai',
                        'message' => "AI service will be available soon. Please try again later.",
                        'message_type' => 'text',
                    ]);
                    
                    // Update chat preview
                    $aiChat->update([
                        'last_message_at' => now(),
                        'last_message_preview' => $errorMsg->message
                    ]);
                    
                    // Broadcast error message
                    try {
                        event(new \App\Events\MessageSent($errorMsg));
                    } catch (\Exception $e) {
                        // Don't let broadcasting failure stop the message from being sent
                    }
                    
                    return response()->json([
                        'user' => $userMsg,
                        'ai' => $errorMsg,
                        'ai_processing' => false
                    ]);
                    
                } catch (\Exception $e) {
                    return response()->json(['error' => 'AI service error: ' . $e->getMessage()], 500);
                }
            }
            
            // Regular chat message - simplified and optimized
            if ($isOwner && $sellerId) {
                // Owner sending message to specific seller
                $seller = User::findOrFail($sellerId);
                
                // Quick authorization check
                if (!$seller->branch || !$seller->branch->business || $seller->branch->business->owner_id !== $user->id) {
                    return response()->json(['error' => 'Unauthorized.'], 403);
                }
                
                $chat = $this->getOrCreateSellerChat($user->id, $seller->id);
                
                $msg = $chat->messages()->create([
                    'user_id' => $user->id,
                    'sender' => 'owner',
                    'message' => $message,
                    'message_type' => 'text',
                ]);
                
            } else if ($isSeller) {
                // Seller sending message to their owner
                $chat = Chat::where('seller_id', $user->id)
                           ->where('type', 'seller')
                           ->first();
                
                if (!$chat) {
                    return response()->json(['error' => 'Chat not found.'], 404);
                }
                
                $msg = $chat->messages()->create([
                    'user_id' => $user->id,
                    'sender' => 'seller',
                    'message' => $message,
                    'message_type' => 'text',
                ]);
                
            } else {
                return response()->json(['error' => 'Invalid request.'], 400);
            }
            
            // Try to broadcast event (non-blocking)
            try {
                event(new \App\Events\MessageSent($msg));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }
            
            $totalTime = microtime(true) - $startTime;
            return response()->json($msg);
            
        } catch (\Exception $e) {
            $totalTime = microtime(true) - $startTime;
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // Send a message (text, image, or audio) - for specific seller chat
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized - No user found.'], 401);
        }
        
        $sellerId = $request->input('seller_id');
        $message = $request->input('message');
        $messageType = $request->input('message_type');
        $imageUrl = $request->input('image_url');
        $audioUrl = $request->input('audio_url');
        $fileUrl = $request->input('file_url');
        $metadata = $request->input('metadata');
        
        if (empty($message) && empty($imageUrl) && empty($audioUrl) && empty($fileUrl)) {
            return response()->json(['error' => 'Message or media required.'], 422);
        }
        
        $isOwner = $user->roles()->where('name', 'owner')->exists();
        $isSeller = $user->roles()->where('name', 'seller')->exists();
        
        if (!$isOwner && !$isSeller) {
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }
        
        if ($isOwner && $sellerId) {
            // Owner sending message to specific seller
            $seller = User::findOrFail($sellerId);
            
            // Check if seller belongs to owner through: Seller → Branch → Business → Owner
            if (!$seller->branch || !$seller->branch->business || $seller->branch->business->owner_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
            
            $chat = $this->getOrCreateSellerChat($user->id, $seller->id);
            $data = [
                'user_id' => $user->id,
                'sender' => 'owner',
                'message' => $message,
                'message_type' => $messageType,
                'image_url' => $imageUrl,
                'audio_url' => $audioUrl,
                'file_url' => $fileUrl,
                'metadata' => $metadata
            ];
        } else if ($isSeller) {
            // Seller sending message to their owner
            $chat = Chat::where('seller_id', $user->id)
                       ->where('type', 'seller')
                       ->first();
            
            if (!$chat) {
                return response()->json(['error' => 'Chat not found.'], 404);
            }
            
            $data = [
                'user_id' => $user->id,
                'sender' => 'seller',
                'message' => $message,
                'message_type' => $messageType,
                'image_url' => $imageUrl,
                'audio_url' => $audioUrl,
                'file_url' => $fileUrl,
                'metadata' => $metadata
            ];
        } else {
            return response()->json(['error' => 'Invalid request.'], 400);
        }
        
        DB::beginTransaction();
        try {
            $msg = $chat->messages()->create($data);
            // Broadcast event for real-time updates
            try {
                event(new \App\Events\MessageSent($msg));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }
            
            // Update chat preview
            $preview = $message ?? ($imageUrl ? '[Image]' : ($audioUrl ? '[Audio]' : ($fileUrl ? '[File]' : 'No message')));
            $chat->last_message_at = now();
            $chat->last_message_preview = $preview;
            
            // Increment unread count for the recipient
            if ($isOwner && $sellerId) {
                // Owner sent message, increment seller's unread count
                $chat->incrementUnreadCountForUser($sellerId);
            } elseif ($isSeller) {
                // Seller sent message, increment owner's unread count
                $chat->incrementUnreadCountForUser($chat->owner_id);
            }
            
            $chat->save();
            
            DB::commit();
            return response()->json($msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

    // Mark messages as read
    public function markAsRead(Request $request, $chatId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            $chat = Chat::findOrFail($chatId);
            
            // Check if user is participant in this chat
            $isParticipant = ($chat->owner_id == $user->id) || ($chat->seller_id == $user->id);
            if (!$isParticipant) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            // Update chat's unread count for this user
            $chat->markAsReadForUser($user->id);
            
            // Try to broadcast read event (but don't fail if it doesn't work)
            try {
                event(new MessageRead($chat->id, $user->id));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // Update typing status
    public function updateTypingStatus(Request $request, $chatId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            $isTyping = $request->input('is_typing', false);
            
            // Update typing status directly on user model
            if ($isTyping) {
                $user->update([
                    'typing_in_chat_id' => $chatId
                ]);
            } else {
                $user->update([
                    'typing_in_chat_id' => null
                ]);
            }

            // Try to broadcast typing event (but don't fail if it doesn't work)
            try {
                event(new UserTyping($chatId, $user->id, $isTyping));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // Update online status
    public function updateOnlineStatus(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $isOnline = $request->input('is_online', true);
        
        if ($isOnline) {
            $user->markAsOnline();
        } else {
            $user->markAsOffline();
        }

        // Broadcast online status change
        event(new UserOnlineStatusChanged($user->id, $isOnline));

        return response()->json(['success' => true]);
    }

    // Edit message
    public function editMessage(Request $request, $messageId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            $message = ChatMessage::findOrFail($messageId);
            
            // Check if user owns this message
            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            // Check if message is within edit time limit (e.g., 15 minutes)
            $editTimeLimit = now()->subMinutes(15);
            if ($message->created_at < $editTimeLimit) {
                return response()->json(['error' => 'Message cannot be edited after 15 minutes.'], 422);
            }

            $newContent = $request->input('message');
            if (empty($newContent)) {
                return response()->json(['error' => 'Message content is required.'], 422);
            }

            // Update message immediately
            $message->update([
                'message' => $newContent,
                'is_edited' => true
            ]);

            // Try to broadcast edit event (but don't wait for it)
            try {
                event(new MessageEdited($message));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to edit message'], 500);
        }
    }

    // Delete message
    public function deleteMessage(Request $request, $messageId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            $message = ChatMessage::findOrFail($messageId);
            
            // Check if user owns this message
            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            $deleteType = $request->input('delete_type', 'for_me');
            
            // Get chat before updating message
            $chat = $message->chat;
            if (!$chat) {
                return response()->json(['error' => 'Chat not found.'], 404);
            }
            
            // Soft delete the message immediately
            $message->update([
                'is_deleted' => true,
                'delete_type' => $deleteType
            ]);

            // Try to broadcast delete event (but don't wait for it)
            try {
                event(new MessageDeleted($message->id, $chat->id, $deleteType, $user->id));
            } catch (\Exception $e) {
                // Don't let broadcasting failure stop the message from being sent
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete message'], 500);
        }
    }

    // Get online status of users
    public function getOnlineStatus(Request $request, $chatId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $chat = Chat::findOrFail($chatId);
        
        // Check if user is participant in this chat
        $isParticipant = ($chat->owner_id == $user->id) || ($chat->seller_id == $user->id);
        if (!$isParticipant) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $participants = [];
        
        // Add owner
        if ($chat->owner_id) {
            $owner = User::find($chat->owner_id);
            if ($owner) {
                $participants[] = [
                    'user_id' => $owner->id,
                    'name' => $owner->name,
                    'online_status' => $owner->getOnlineStatus(),
                    'is_typing' => $owner->typing_in_chat_id == $chat->id
                ];
            }
        }
        
        // Add seller
        if ($chat->seller_id) {
            $seller = User::find($chat->seller_id);
            if ($seller) {
                $participants[] = [
                    'user_id' => $seller->id,
                    'name' => $seller->name,
                    'online_status' => $seller->getOnlineStatus(),
                    'is_typing' => $seller->typing_in_chat_id == $chat->id
                ];
            }
        }

        return response()->json($participants);
    }

    // Get unread message count
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $isOwner = $user->roles()->where('name', 'owner')->exists();
        $isSeller = $user->roles()->where('name', 'seller')->exists();

        if ($isOwner) {
            // Get all chats where user is owner and sum up unread counts for owner
            $chats = Chat::where('owner_id', $user->id)->get();
            $totalUnread = 0;
            foreach ($chats as $chat) {
                $totalUnread += $chat->getUnreadCountForUser($user->id);
            }
        } elseif ($isSeller) {
            // Get chat where user is seller and get unread count for seller
            $chat = Chat::where('seller_id', $user->id)->first();
            $totalUnread = $chat ? $chat->getUnreadCountForUser($user->id) : 0;
        } else {
            $totalUnread = 0;
        }

        return response()->json(['unread_count' => $totalUnread]);
    }
} 