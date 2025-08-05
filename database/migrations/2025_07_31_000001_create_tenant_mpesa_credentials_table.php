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
        Schema::create('branch_mpesa_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id'); // Branch ID from branches table
            $table->string('environment'); // 'sandbox' or 'live'
            $table->boolean('is_active')->default(true);
            $table->boolean('is_testing')->default(true);
            
            // M-PESA Express credentials
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('business_shortcode');
            $table->string('passkey');
            
            // Additional info
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('branch_id');
            $table->index('environment');
            $table->index('is_active');
            
            // Foreign key
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_mpesa_credentials');
    }
}; 