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
        Schema::table('sales', function (Blueprint $table) {
            // Make customer_id nullable to allow sales without customers
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            
            // Change payment_method from VARCHAR to JSON to store array of payment methods
            $table->json('payment_methods')->nullable()->after('payment_method');
            
            // Drop the old payment_method column after creating the new one
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Revert customer_id back to not nullable
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            
            // Recreate the old payment_method column
            $table->string('payment_method')->nullable()->after('payment_status');
            
            // Drop the new payment_methods column
            $table->dropColumn('payment_methods');
        });
    }
};
