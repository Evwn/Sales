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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false)->after('metadata');
            $table->boolean('is_deleted')->default(false)->after('is_edited');
            $table->enum('delete_type', ['for_me', 'for_all'])->nullable()->after('is_deleted');
            $table->text('original_message')->nullable()->after('delete_type');
            $table->json('read_by')->nullable()->after('original_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'is_deleted', 'delete_type', 'original_message', 'read_by']);
        });
    }
};
