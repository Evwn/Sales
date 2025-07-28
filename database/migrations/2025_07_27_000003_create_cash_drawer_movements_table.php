<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cash_drawer_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 15, 2);
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('cash_drawer_movements');
    }
}; 