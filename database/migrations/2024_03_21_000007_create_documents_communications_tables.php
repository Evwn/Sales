<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->decimal('total_amount', 15, 2);
                $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
                $table->date('due_date');
                $table->timestamps();
                $table->softDeletes();
                $table->index('customer_id');
                $table->index('sale_id');
            });
        }

        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->decimal('total_amount', 15, 2);
                $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
                $table->dateTime('valid_until');
                $table->timestamps();
                $table->softDeletes();
                $table->index('customer_id');
            });
        }

        if (!Schema::hasTable('delivery_notes')) {
            Schema::create('delivery_notes', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->enum('status', ['draft', 'sent', 'delivered'])->default('draft');
                $table->timestamps();
                $table->softDeletes();
                $table->index('sale_id');
            });
        }

        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->enum('recipient_type', ['customer', 'supplier']);
                $table->unsignedBigInteger('recipient_id');
                $table->text('message');
                $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index(['recipient_type', 'recipient_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('invoices');
    }
}; 