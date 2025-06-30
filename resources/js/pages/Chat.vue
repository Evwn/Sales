<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Chat
        <span v-if="totalUnreadCount > 0" class="ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded-full">
          {{ totalUnreadCount }}
        </span>
      </h2>
    </template>

    <div class="chat-container">
      <!-- Owner View: Show seller list -->
      <div v-if="userType === 'owner' && !selectedChat" class="seller-list">
        <div class="sellers-container">
          <!-- AI Assistant (always first) -->
          <div @click="selectAI" class="seller-item ai-item">
            <div class="ai-avatar">🤖</div>
            <div class="seller-info">
              <div class="seller-name">AI Assistant</div>
              <div class="last-message">{{ aiLastMessage || 'Ask me anything!' }}</div>
            </div>
          </div>
          
          <!-- Sellers -->
          <div v-for="chat in chats" :key="chat.id" @click="selectSeller(chat)" class="seller-item">
            <img :src="chat.avatar || defaultAvatar" class="avatar" />
            <div class="seller-info">
              <div class="seller-name">{{ chat.name }}</div>
              <div class="last-message">{{ chat.last_message_preview || 'No messages yet' }}</div>
            </div>
            <div v-if="chat.unread_count > 0" class="unread-badge">
              {{ chat.unread_count }}
            </div>
          </div>
          
          <div v-if="chats.length === 0" class="no-sellers">
            <p>No sellers found. Add sellers to start chatting.</p>
          </div>
        </div>
      </div>

      <!-- Seller View: Show chat options -->
      <div v-else-if="userType === 'seller' && !selectedChat" class="seller-list">
        <div class="sellers-container">
          <!-- Owner Chat -->
          <div v-if="ownerChat" @click="selectOwner" class="seller-item">
            <img :src="ownerChat.owner.avatar || defaultAvatar" class="avatar" />
            <div class="seller-info">
              <div class="seller-name">{{ ownerChat.owner.name }}</div>
              <div class="last-message">{{ ownerChat.last_message_preview || 'No messages yet' }}</div>
            </div>
            <div v-if="ownerChat.unread_count > 0" class="unread-badge">
              {{ ownerChat.unread_count }}
            </div>
          </div>
          
          <div v-if="!ownerChat" class="no-sellers">
            <p>No owner chat available.</p>
          </div>
        </div>
      </div>

      <!-- Chat View: Show actual chat -->
      <div v-else class="chat-view">
        <div class="chat-header" :class="{ 'ai-header': isAIChat }">
          <button @click="backToChats" class="back-btn">← Back</button>
          <div v-if="isAIChat" class="ai-avatar">🤖</div>
          <img v-else :src="chatPartner.avatar || defaultAvatar" class="avatar" />
          <div class="chat-user-info">
            <div class="chat-user-name">{{ chatPartner.name }}</div>
            <div class="chat-user-status">
              <span v-if="isAIChat">AI Assistant</span>
              <span v-else-if="isTyping" class="typing-indicator">typing...</span>
              <span v-else>{{ onlineStatus }}</span>
            </div>
          </div>
        </div>
        <div class="chat-messages" ref="messagesContainer" @scroll="onMessagesScroll">
          <div v-if="loadingMessages">
            <div v-for="n in skeletonArray" :key="n" :class="['skeleton-bubble', n % 2 === 0 ? 'skeleton-sent' : 'skeleton-received']"
              :style="{ animationDelay: (n * 0.3) + 's' }">
              <div class="skeleton-avatar" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
              <div class="skeleton-content">
                <div class="skeleton-message-line" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
                <div class="skeleton-message-line short" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
                <div class="skeleton-meta-action">
                  <div class="skeleton-meta" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
                  <div class="skeleton-actions">
                    <div class="skeleton-action-btn" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
                    <div class="skeleton-action-btn" :style="{ animationDelay: (n * 0.3) + 's' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else>
            <div v-for="msg in messages" :key="msg.id" :class="['chat-bubble', msg.user_id === user.id ? 'sent' : 'received', { 'optimistic': msg.is_optimistic, 'error': msg.is_error, 'processing': msg.is_processing }]">
              <div v-if="msg.is_deleted" class="deleted-message">
                {{ msg.delete_type === 'for_all' ? 'This message was deleted' : 'You deleted this message' }}
              </div>
              <div v-else-if="msg.is_error" class="message-content error-message">
                {{ msg.message }}
              </div>
              <div v-else-if="msg.is_processing" class="message-content processing-message">
                {{ msg.message }}
                <span class="processing-indicator">🤔</span>
              </div>
              <div v-else-if="msg.message_type === 'text'" class="message-content">
                {{ msg.message }}
                <span v-if="msg.is_edited" class="edited-indicator">(edited)</span>
                <span v-if="msg.is_optimistic" class="optimistic-indicator">⏳</span>
              </div>
              <img v-else-if="msg.message_type === 'image'" :src="msg.image_url" class="chat-image" />
              <audio v-else-if="msg.message_type === 'audio'" :src="msg.audio_url" controls class="chat-audio" />
              <div class="chat-meta">{{ formatTime(msg.created_at) }}</div>
              
              <!-- Message actions for sent messages -->
              <div v-if="msg.user_id === user.id && !msg.is_deleted && !msg.is_optimistic && !msg.is_error && !msg.is_processing" class="message-actions">
                <button v-if="canEditMessage(msg)" @click="editMessage(msg)" class="action-btn">Edit</button>
                <button @click="deleteMessage(msg, 'for_me')" class="action-btn">Delete for me</button>
                <button @click="deleteMessage(msg, 'for_all')" class="action-btn">Delete for all</button>
              </div>
            </div>
            <div v-if="isLoadingMore" class="skeleton-message"></div>
          </div>
          <!-- Scroll to bottom button -->
          <button v-if="showScrollToBottom" class="scroll-to-bottom-btn" @click="scrollToBottom">
            ⬇️ Recent
          </button>
        </div>
        <div class="chat-input-area">
          <input 
            v-model="newMessage" 
            @keyup.enter="sendMessage" 
            @input="onTyping"
            placeholder="Type a message..." 
            class="chat-input" 
          />
          <button @click="toggleEmojiPicker" class="emoji-btn">😊</button>
          <input type="file" ref="fileInput" @change="handleFileUpload" style="display:none" />
          <button @click="() => fileInput.click()" class="attach-btn">📎</button>
          <button @click="sendMessage" class="send-btn">Send</button>
          <div v-if="showEmojiPicker" class="emoji-picker">
            <span v-for="emoji in emojis" :key="emoji" @click="addEmoji(emoji)">{{ emoji }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, onUnmounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

// Configure SweetAlert2 for better UX
Swal.mixin({
  customClass: {
    confirmButton: 'swal-confirm-btn',
    cancelButton: 'swal-cancel-btn'
  },
  buttonsStyling: false
});

const props = defineProps({
  auth: Object
});

const user = computed(() => props.auth?.user);
const userType = ref('');
const chats = ref([]);
const selectedChat = ref(null);
const messages = ref([]);
const newMessage = ref('');
const showEmojiPicker = ref(false);
const messagesContainer = ref(null);
const fileInput = ref(null);
const defaultAvatar = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDQiIGhlaWdodD0iNDQiIHZpZXdCb3g9IjAgMCA0NCA0NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjIiIGN5PSIyMiIgcj0iMjIiIGZpbGw9IiNEN0Q5RDAiLz4KPHBhdGggZD0iTTIyIDIyQzI0LjIwOTEgMjIgMjYgMjAuMjA5MSAyNiAxOEMyNiAxNS43OTA5IDI0LjIwOTEgMTQgMjIgMTRDMTkuNzkwOSAxNCAxOCAxNS43OTA5IDE4IDE4QzE4IDIwLjIwOTEgMTkuNzkwOSAyMiAyMiAyMloiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTIyIDI0QzE3LjU4MjUgMjQgMTQgMjcuNTgyNSAxNCAzMkMxNCAzNC4yMDkxIDE1Ljc5MDkgMzYgMTggMzZIMjZDMjguMjA5MSAzNiAzMCAzNC4yMDkxIDMwIDMyQzMwIDI3LjU4MjUgMjYuNDE3NSAyNCAyMiAyNFoiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
const isAIChat = ref(false);
const chatPartner = ref({});
const aiLastMessage = ref('');
const ownerChat = ref(null);
const totalUnreadCount = ref(0);
const isTyping = ref(false);
const onlineStatus = ref('Online');
const typingTimeout = ref(null);
const loadingMessages = ref(false);
const showScrollToBottom = ref(false);
const hasMoreMessages = ref(true);
const isLoadingMore = ref(false);
const skeletonCount = 10;

const emojis = ['😊', '😂', '❤️', '👍', '👎', '😍', '😭', '😡', '🤔', '👋'];

// Echo setup for real-time events
let echo = null;

onMounted(async () => {
  await fetchChatData();
  fetchUnreadCount();
  // Don't call setupEcho here - it will be called when a chat is selected
});

onUnmounted(() => {
  // Clean up Echo connection
  if (echo) {
    echo.disconnect();
    echo = null;
  }
  
  // Clean up typing timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
  
  // Clean up online status polling
  if (window.onlineStatusInterval) {
    clearInterval(window.onlineStatusInterval);
    window.onlineStatusInterval = null;
  }
});

const setupEcho = () => {
  if (window.Echo && selectedChat.value && selectedChat.value.id !== 'ai') {
    try {
      // Disconnect any existing connections
      if (echo) {
        echo.disconnect();
      }
      
      echo = window.Echo;
      
      // Listen for new messages on the specific chat channel
      echo.private(`chat.${selectedChat.value.id}`)
        .listen('MessageSent', (e) => {
          messages.value.push(e.message);
          scrollToBottom();
          fetchUnreadCount();
        })
        .listen('MessageRead', (e) => {
          // Update read status
          fetchUnreadCount();
        })
        .listen('UserTyping', (e) => {
          if (e.user_id !== user.value.id) {
            isTyping.value = e.is_typing;
          }
        })
        .listen('MessageEdited', (e) => {
          const messageIndex = messages.value.findIndex(m => m.id === e.message.id);
          if (messageIndex !== -1) {
            messages.value[messageIndex] = e.message;
          }
        })
        .listen('MessageDeleted', (e) => {
          const messageIndex = messages.value.findIndex(m => m.id === e.message_id);
          if (messageIndex !== -1) {
            messages.value[messageIndex].is_deleted = true;
            messages.value[messageIndex].delete_type = e.delete_type;
          }
        });
        
    } catch (error) {
    }
  }
};

const fetchChatData = async () => {
  try {
    const response = await axios.get('/chat/messages');
    const data = response.data;
    
    userType.value = data.type;
    
    if (data.type === 'owner') {
      chats.value = data.chats.map(chat => ({
        ...chat,
        unread_count: chat.unread_count || 0
      }));
      aiLastMessage.value = data.ai_chat?.last_message_preview || '';
    } else if (data.type === 'seller') {
      ownerChat.value = data.owner_chat;
      aiLastMessage.value = '';
    }
  } catch (error) {
  }
};

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/chat/unread-count');
    totalUnreadCount.value = response.data.unread_count;
  } catch (error) {
  }
};

// Fetch the latest chunk of messages for a chat
const fetchMessagesChunk = async (chatId, isAI = false, before = null, limit = 20) => {
  let url = isAI ? `/chat/messages/ai?limit=${limit}` : `/chat/messages/${chatId}?limit=${limit}`;
  if (before) url += `&before=${before}`;
  isLoadingMore.value = true;
  try {
    const response = await axios.get(url);
    const newMessages = response.data.messages || [];
    hasMoreMessages.value = response.data.has_more;
    if (before) {
      // Prepend older messages
      messages.value = [...newMessages, ...messages.value];
    } else {
      messages.value = newMessages;
      await nextTick();
      scrollToBottom();
    }
  } finally {
    isLoadingMore.value = false;
  }
};

// When opening a chat, fetch only the latest chunk
const selectAI = async () => {
  isAIChat.value = true;
  chatPartner.value = { name: 'AI Assistant' };
  selectedChat.value = { id: 'ai', type: 'ai' };
  loadingMessages.value = true;
  setupEcho();
  await fetchMessagesChunk('ai', true);
  loadingMessages.value = false;
};

const selectSeller = async (seller) => {
  isAIChat.value = false;
  chatPartner.value = seller;
  const chatId = seller.chat_id || seller.id;
  selectedChat.value = { id: chatId, type: 'seller' };
  loadingMessages.value = true;
  startOnlineStatusPolling();
  markAsRead();
  await fetchMessagesChunk(chatId, false);
  loadingMessages.value = false;
};

const selectOwner = async () => {
  isAIChat.value = false;
  chatPartner.value = ownerChat.value.owner;
  const chatId = ownerChat.value.id;
  selectedChat.value = { id: chatId, type: 'owner' };
  loadingMessages.value = true;
  startOnlineStatusPolling();
  markAsRead();
  await fetchMessagesChunk(chatId, false);
  loadingMessages.value = false;
};

const markAsRead = async () => {
  if (selectedChat.value && selectedChat.value.id && selectedChat.value.id !== 'ai') {
    try {
      await axios.post(`/chat/${selectedChat.value.id}/mark-read`);
      
      // Update the unread count in the chat list immediately
      if (userType.value === 'owner') {
        const chatIndex = chats.value.findIndex(chat => 
          chat.id === selectedChat.value.id || chat.chat_id === selectedChat.value.id
        );
        if (chatIndex !== -1) {
          chats.value[chatIndex].unread_count = 0;
        }
      } else if (userType.value === 'seller' && ownerChat.value) {
        ownerChat.value.unread_count = 0;
      }
      
      // Refresh the total unread count
      await fetchUnreadCount();
      
    } catch (error) {
    }
  }
};

const onTyping = () => {
  if (selectedChat.value && selectedChat.value.id !== 'ai') {
    // Send typing status with error handling
    axios.post(`/chat/${selectedChat.value.id}/typing`, { is_typing: true })
      .catch(() => {
      });
    
    // Clear typing status after 3 seconds
    if (typingTimeout.value) {
      clearTimeout(typingTimeout.value);
    }
    typingTimeout.value = setTimeout(() => {
      axios.post(`/chat/${selectedChat.value.id}/typing`, { is_typing: false })
        .catch(() => {
        });
    }, 3000);
  }
};

const startOnlineStatusPolling = () => {
  if (selectedChat.value && selectedChat.value.id !== 'ai') {
    // Clear any existing interval
    if (window.onlineStatusInterval) {
      clearInterval(window.onlineStatusInterval);
    }
    
    window.onlineStatusInterval = setInterval(async () => {
      try {
        const response = await axios.get(`/chat/${selectedChat.value.id}/online-status`);
        const participants = response.data;
        const partner = participants.find(p => p.user_id !== user.value.id);
        if (partner) {
          onlineStatus.value = partner.online_status;
          isTyping.value = partner.is_typing;
        }
      } catch (error) {
      }
    }, 10000); // Poll every 10 seconds
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim()) return;
  
  // Store the message and clear input immediately
  const messageToSend = newMessage.value.trim();
  newMessage.value = '';
  
  // Create optimistic message immediately
  const optimisticMessage = {
    id: 'temp-' + Date.now(),
    message: messageToSend,
    user_id: user.value.id,
    sender: isAIChat.value ? 'user' : (userType.value === 'owner' ? 'owner' : 'seller'),
    message_type: 'text',
    created_at: new Date().toISOString(),
    is_optimistic: true
  };
  
  // Add message to UI immediately
  messages.value.push(optimisticMessage);
  scrollToBottom();
  
  // Send API request in background (non-blocking)
  const messageData = {
    message: messageToSend,
    seller_id: isAIChat.value ? null : chatPartner.value.id,
    is_ai: isAIChat.value
  };
  
  try {
    const response = await axios.post('/chat/messages', messageData);
    
    // Replace optimistic message with real message
    const optimisticIndex = messages.value.findIndex(m => m.id === optimisticMessage.id);
    if (optimisticIndex !== -1) {
      if (isAIChat.value) {
        // AI chat returns user message immediately, AI response comes later
        messages.value.splice(optimisticIndex, 1); // Remove optimistic message
        messages.value.push(response.data.user);
        
        // If AI is processing, show a temporary message
        if (response.data.ai_processing) {
          messages.value.push({
            id: 'ai-processing-' + Date.now(),
            message: 'AI is thinking...',
            user_id: null,
            sender: 'ai',
            message_type: 'text',
            created_at: new Date().toISOString(),
            is_processing: true
          });
        }
        
        // If AI response is already available
        if (response.data.ai) {
          messages.value.push(response.data.ai);
        }
      } else {
        // Replace optimistic message with real message
        messages.value[optimisticIndex] = response.data;
      }
    }
    
    scrollToBottom();
    fetchUnreadCount();
    
    // Show success notification for non-AI messages
    if (!isAIChat.value) {
      Swal.fire({
        icon: 'success',
        title: 'Message Sent!',
        text: 'Your message has been delivered.',
        timer: 1000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    }
  } catch (error) {
    // Remove optimistic message and show error
    const optimisticIndex = messages.value.findIndex(m => m.id === optimisticMessage.id);
    if (optimisticIndex !== -1) {
      messages.value.splice(optimisticIndex, 1);
    }
    
    // Add error message
    messages.value.push({
      id: 'error-' + Date.now(),
      message: 'Failed to send message. Please try again.',
      user_id: user.value.id,
      sender: 'system',
      message_type: 'text',
      created_at: new Date().toISOString(),
      is_error: true
    });
    
    // Restore the message to input for retry
    newMessage.value = messageToSend;
    
    // Show error notification
    Swal.fire({
      icon: 'error',
      title: 'Send Failed!',
      text: 'Failed to send message. Please try again.',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    });
  }
};

const editMessage = async (message) => {
  const { value: newContent } = await Swal.fire({
    title: 'Edit Message',
    input: 'textarea',
    inputValue: message.message,
    inputPlaceholder: 'Enter your message...',
    showCancelButton: true,
    confirmButtonText: 'Save',
    cancelButtonText: 'Cancel',
    inputValidator: (value) => {
      if (!value || value.trim() === '') {
        return 'Message cannot be empty!';
      }
    }
  });

  if (newContent && newContent.trim() !== message.message) {
    // Update UI immediately for better UX
    const messageIndex = messages.value.findIndex(m => m.id === message.id);
    if (messageIndex !== -1) {
      messages.value[messageIndex].message = newContent.trim();
      messages.value[messageIndex].is_edited = true;
    }

    // Show success notification immediately
    Swal.fire({
      icon: 'success',
      title: 'Message Updated!',
      text: 'Your message has been edited successfully.',
      timer: 1000,
      showConfirmButton: false
    });

    // Send request in background
    try {
      await axios.put(`/chat/messages/${message.id}/edit`, {
        message: newContent.trim()
      });
    } catch (error) {
      // Revert the change if it failed
      if (messageIndex !== -1) {
        messages.value[messageIndex].message = message.message;
        messages.value[messageIndex].is_edited = false;
      }
      
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to save changes. Please try again.',
      });
    }
  }
};

const deleteMessage = async (message, deleteType) => {
  const deleteTypeText = deleteType === 'for_all' ? 'for everyone' : 'for yourself';
  
  const result = await Swal.fire({
    title: 'Delete Message',
    text: `Are you sure you want to delete this message ${deleteTypeText}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    // Update UI immediately for better UX
    const messageIndex = messages.value.findIndex(m => m.id === message.id);
    if (messageIndex !== -1) {
      messages.value[messageIndex].is_deleted = true;
      messages.value[messageIndex].delete_type = deleteType;
    }

    // Show success notification immediately
    Swal.fire({
      icon: 'success',
      title: 'Deleted!',
      text: `Message has been deleted ${deleteTypeText}.`,
      timer: 1000,
      showConfirmButton: false
    });

    // Send request in background
    try {
      await axios.delete(`/chat/messages/${message.id}`, {
        data: { delete_type: deleteType }
      });
    } catch (error) {
      // Revert the change if it failed
      if (messageIndex !== -1) {
        messages.value[messageIndex].is_deleted = false;
        messages.value[messageIndex].delete_type = null;
      }
      
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to delete message. Please try again.',
      });
    }
  }
};

const canEditMessage = (message) => {
  const editTimeLimit = new Date(message.created_at);
  editTimeLimit.setMinutes(editTimeLimit.getMinutes() + 15);
  return new Date() < editTimeLimit;
};

const backToChats = async () => {
  // Check if user is typing and confirm before leaving
  if (newMessage.value.trim()) {
    const result = await Swal.fire({
      title: 'Leave Chat?',
      text: 'You have unsent text. Are you sure you want to leave?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, leave',
      cancelButtonText: 'Stay',
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6'
    });

    if (!result.isConfirmed) {
      return;
    }
  }

  selectedChat.value = null;
  messages.value = [];
  isAIChat.value = false;
  chatPartner.value = {};
  
  // Clean up Echo connection
  if (echo) {
    echo.disconnect();
    echo = null;
  }
  
  // Clean up online status polling
  if (window.onlineStatusInterval) {
    clearInterval(window.onlineStatusInterval);
    window.onlineStatusInterval = null;
  }
  
  // Reset typing status
  isTyping.value = false;
  onlineStatus.value = 'Online';
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      showScrollToBottom.value = false;
    }
  });
};

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value;
};

const addEmoji = (emoji) => {
  newMessage.value += emoji;
  showEmojiPicker.value = false;
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Handle file upload logic here
  }
};

const onMessagesScroll = () => {
  const container = messagesContainer.value;
  if (!container) return;
  showScrollToBottom.value = container.scrollTop + container.clientHeight < container.scrollHeight - 50;
  if (container.scrollTop === 0 && hasMoreMessages.value && !isLoadingMore.value) {
    // Load older messages
    if (messages.value.length > 0) {
      const oldestId = messages.value[0].id;
      fetchMessagesChunk(selectedChat.value.id, isAIChat.value, oldestId);
    }
  }
};

// Skeleton loader for messages
const skeletonArray = computed(() => Array.from({ length: skeletonCount }));

watch(loadingMessages, async (newVal, oldVal) => {
  if (oldVal && !newVal) {
    // Loading just finished
    await nextTick();
    scrollToBottom();
  }
});
</script>

<style scoped>
.chat-container {
  max-width: 800px;
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 200px); /* Adjust for header and bottom nav */
  background: #f8f9fa;
}

.seller-list {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.sellers-container {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.seller-item {
  display: flex;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
}

.seller-item:hover {
  background-color: #f0f0f0;
}

.ai-item {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 8px;
  margin-bottom: 8px;
}

.ai-item:hover {
  background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.ai-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  margin-right: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  background: rgba(255, 255, 255, 0.2);
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  margin-right: 12px;
}

.seller-info {
  flex: 1;
  margin-left: 12px;
}

.seller-name {
  font-weight: bold;
  font-size: 1.1em;
}

.last-message {
  font-size: 0.9em;
  color: #666;
  margin-top: 4px;
}

.ai-item .last-message {
  color: rgba(255, 255, 255, 0.8);
}

.unread-badge {
  background: #ff4757;
  color: white;
  border-radius: 50%;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75em;
  font-weight: bold;
  margin-left: 8px;
}

.no-sellers {
  text-align: center;
  padding: 40px;
  color: #666;
}

.chat-view {
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #eee;
  background: #075e54;
  color: #fff;
  border-radius: 12px 12px 0 0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.ai-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.back-btn {
  background: none;
  border: none;
  color: #fff;
  font-size: 1.2em;
  cursor: pointer;
  margin-right: 12px;
  padding: 4px 8px;
  border-radius: 4px;
}

.back-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.chat-user-info {
  flex: 1;
}

.chat-user-name {
  font-weight: bold;
  font-size: 1.1em;
}

.chat-user-status {
  font-size: 0.9em;
  color: #cfd8dc;
}

.typing-indicator {
  color: #4CAF50;
  font-style: italic;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #ece5dd;
  margin-top: 0;
}

.chat-bubble {
  max-width: 70%;
  margin-bottom: 12px;
  padding: 10px 14px;
  border-radius: 18px;
  position: relative;
  font-size: 1em;
  word-break: break-word;
}

.sent {
  background: #dcf8c6;
  align-self: flex-end;
  margin-left: auto;
}

.received {
  background: #fff;
  align-self: flex-start;
  margin-right: auto;
}

.optimistic {
  opacity: 0.7;
}

.error {
  background: #ffebee;
  border: 1px solid #ffcdd2;
}

.message-content {
  position: relative;
}

.optimistic-indicator {
  margin-left: 8px;
  animation: spin 1s linear infinite;
  color: #666;
}

.error-message {
  color: #d32f2f;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.edited-indicator {
  font-size: 0.75em;
  color: #888;
  font-style: italic;
  margin-left: 4px;
}

.deleted-message {
  font-style: italic;
  color: #888;
  font-size: 0.9em;
}

.message-actions {
  position: absolute;
  top: -8px;
  right: -8px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 4px;
  display: none;
  flex-direction: column;
  gap: 2px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chat-bubble:hover .message-actions {
  display: flex;
}

.action-btn {
  background: none;
  border: none;
  padding: 4px 8px;
  font-size: 0.8em;
  cursor: pointer;
  border-radius: 4px;
  color: #333;
}

.action-btn:hover {
  background: #f0f0f0;
}

.chat-meta {
  font-size: 0.75em;
  color: #888;
  margin-top: 4px;
  text-align: right;
}

.chat-input-area {
  display: flex;
  align-items: center;
  padding: 12px;
  border-top: 1px solid #eee;
  background: #fff;
  border-radius: 0 0 12px 12px;
}

.chat-input {
  flex: 1;
  border: none;
  border-radius: 20px;
  padding: 10px 16px;
  margin-right: 8px;
  background: #f0f0f0;
  font-size: 1em;
}

.emoji-btn, .attach-btn, .send-btn {
  background: none;
  border: none;
  font-size: 1.3em;
  margin: 0 4px;
  cursor: pointer;
}

.emoji-picker {
  position: absolute;
  bottom: 60px;
  left: 20px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px;
  display: flex;
  flex-wrap: wrap;
  z-index: 10;
}

.chat-image {
  max-width: 180px;
  border-radius: 8px;
  margin: 6px 0;
}

.chat-audio {
  width: 180px;
  margin: 6px 0;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .chat-container {
    height: calc(100vh - 250px); /* More space for mobile nav */
    margin: 0 8px;
  }
  
  .chat-bubble {
    max-width: 85%;
  }
  
  .chat-input-area {
    padding: 8px;
  }
  
  .emoji-picker {
    bottom: 80px; /* Adjust for mobile bottom nav */
  }
  
  .message-actions {
    position: static;
    display: flex;
    flex-direction: row;
    margin-top: 4px;
    background: none;
    border: none;
    box-shadow: none;
  }
}

/* SweetAlert Button Styles */
.swal-confirm-btn {
  background: #28a745 !important;
  color: white !important;
  border: none !important;
  padding: 10px 24px !important;
  border-radius: 6px !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: background-color 0.2s !important;
}

.swal-confirm-btn:hover {
  background: #218838 !important;
}

.swal-cancel-btn {
  background: #dc3545 !important;
  color: white !important;
  border: none !important;
  padding: 10px 24px !important;
  border-radius: 6px !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: background-color 0.2s !important;
}

.swal-cancel-btn:hover {
  background: #c82333 !important;
}

.processing {
  opacity: 0.8;
  background: #e3f2fd;
}

.processing-message {
  color: #1976d2;
  font-style: italic;
}

.processing-indicator {
  margin-left: 8px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.chat-loading-spinner { display: flex; align-items: center; justify-content: center; height: 100%; color: #1976d2; font-size: 1.1em; }
.spinner { width: 24px; height: 24px; border: 3px solid #e3f2fd; border-top: 3px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 12px; }
@keyframes spin { 100% { transform: rotate(360deg); } }

.scroll-to-bottom-btn {
  position: absolute;
  right: 24px;
  bottom: 80px;
  z-index: 20;
  background: #1976d2;
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 8px 18px;
  font-size: 1em;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
  cursor: pointer;
  opacity: 0.92;
  transition: opacity 0.2s;
}
.scroll-to-bottom-btn:hover {
  opacity: 1;
  background: #125ea2;
}

.skeleton-bubble {
  display: flex;
  align-items: flex-end;
  margin-bottom: 20px;
  max-width: 70%;
  min-height: 64px;
  padding: 12px 16px;
  border-radius: 18px;
  position: relative;
  background: none;
}
.skeleton-sent {
  flex-direction: row-reverse;
  margin-left: auto;
  background: none;
}
.skeleton-received {
  flex-direction: row;
  margin-right: auto;
  background: none;
}
.skeleton-avatar,
.skeleton-message-line,
.skeleton-meta,
.skeleton-action-btn {
  animation: skeleton-loading 2.4s infinite linear;
}
@keyframes skeleton-loading {
  0% { background-position: -200px 0; }
  100% { background-position: calc(200px + 100%) 0; }
}
.skeleton-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.skeleton-message-line {
  height: 20px;
  width: 90%;
  border-radius: 8px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e3e3e3 50%, #f0f0f0 75%);
}
.skeleton-message-line.short {
  width: 60%;
}
.skeleton-meta-action {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
}
.skeleton-meta {
  width: 60px;
  height: 14px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e3e3e3 50%, #f0f0f0 75%);
}
.skeleton-actions {
  display: flex;
  gap: 6px;
}
.skeleton-action-btn {
  width: 32px;
  height: 18px;
  border-radius: 4px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e3e3e3 50%, #f0f0f0 75%);
}
</style>
