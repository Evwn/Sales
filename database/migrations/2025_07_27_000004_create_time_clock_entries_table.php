<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('time_clock_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }
    public function down() {
        Schema::dropIfExists('time_clock_entries');
    }
}; 