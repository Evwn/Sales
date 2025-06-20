<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0); // Tax rate as percentage
            $table->boolean('is_taxable')->default(true); // Whether item is taxable
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'is_taxable']);
        });
    }
}; 