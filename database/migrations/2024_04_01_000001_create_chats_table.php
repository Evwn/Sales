<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'ai' or 'seller'
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('chats');
    }
}; 