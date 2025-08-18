<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up() {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['unpaid','partial','paid'])->default('unpaid');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('invoices');
    }
};
