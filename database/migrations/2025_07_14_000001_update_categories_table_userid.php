<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
    public function down() {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}; 