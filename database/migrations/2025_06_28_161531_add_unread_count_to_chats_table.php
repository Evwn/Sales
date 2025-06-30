<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->integer('unread_count_owner')->default(0)->after('last_message_preview');
            $table->integer('unread_count_seller')->default(0)->after('unread_count_owner');
            $table->timestamp('last_read_owner')->nullable()->after('unread_count_seller');
            $table->timestamp('last_read_seller')->nullable()->after('last_read_owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['unread_count_owner', 'unread_count_seller', 'last_read_owner', 'last_read_seller']);
        });
    }
};
