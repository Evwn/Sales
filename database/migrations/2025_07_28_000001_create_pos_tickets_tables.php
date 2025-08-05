<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pos_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->json('payment_details')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_ticket_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->decimal('qty', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        Schema::create('pos_ticket_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('method'); // cash, card, mpesa
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->json('meta')->nullable(); // card/mpesa details
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_ticket_payments');
        Schema::dropIfExists('pos_ticket_items');
        Schema::dropIfExists('pos_tickets');
    }
}; 