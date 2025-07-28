<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('shift_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->decimal('expected_close_cash', 15, 2)->nullable();
            $table->decimal('real_close_cash', 15, 2)->nullable();
            $table->string('opening_note', 255)->nullable();
            $table->string('closing_note', 255)->nullable();
            $table->string('closing_reason', 255)->nullable();
            $table->timestamps();
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('shift_balances');
    }
}; 