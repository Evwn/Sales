<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('sender'); // 'user', 'ai', or 'seller'
            $table->text('message')->nullable();
            $table->string('message_type')->default('text'); // 'text', 'image', 'audio', 'file', etc.
            $table->string('image_url')->nullable();
            $table->string('audio_url')->nullable();
            $table->string('file_url')->nullable();
            $table->json('metadata')->nullable(); // for future features (reactions, edits, etc.)
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}; 