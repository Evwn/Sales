<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tims_transactions')) {
            Schema::create('tims_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->enum('type', ['sale', 'purchase']);
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
                $table->string('tims_reference')->nullable();
                $table->json('response_data')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('reference');
                $table->index('type');
            });
        }

        if (!Schema::hasTable('vat_returns')) {
            Schema::create('vat_returns', function (Blueprint $table) {
                $table->id();
                $table->string('period');
                $table->decimal('total_sales', 15, 2);
                $table->decimal('total_purchases', 15, 2);
                $table->decimal('vat_amount', 15, 2);
                $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
                $table->string('tims_reference')->nullable();
                $table->json('response_data')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('period');
                $table->index('status');
            });
        }

        if (!Schema::hasTable('tims_settings')) {
            Schema::create('tims_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->text('value');
                $table->string('description')->nullable();
                $table->timestamps();
                $table->unique('key');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tims_settings');
        Schema::dropIfExists('vat_returns');
        Schema::dropIfExists('tims_transactions');
    }
}; 