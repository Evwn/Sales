<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_receipts', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->after('total');
            }
            if (!Schema::hasColumn('sales_receipts', 'payment_due_date')) {
                $table->date('payment_due_date')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('sales_receipts', 'notes')) {
                $table->text('notes')->nullable()->after('payment_due_date');
            }
        });
    }

    public function down()
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_due_date', 'notes']);
        });
    }
}; 