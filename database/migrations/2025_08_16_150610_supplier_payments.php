<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up() {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['bank','mpesa','cash','cheque'])->default('bank');
            $table->string('reference')->nullable(); // e.g. Mpesa code, bank ref
            $table->date('payment_date');
            $table->enum('status', ['pending','processing','completed','failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('payments');
    }
};
