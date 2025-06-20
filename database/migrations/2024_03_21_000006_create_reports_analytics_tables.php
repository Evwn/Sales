<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('product_movement_analysis')) {
            Schema::create('product_movement_analysis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('period');
                $table->decimal('quantity_sold', 15, 2)->default(0);
                $table->decimal('quantity_purchased', 15, 2)->default(0);
                $table->timestamps();
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('stock_valuation')) {
            Schema::create('stock_valuation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->decimal('quantity', 15, 2);
                $table->decimal('value', 15, 2);
                $table->timestamps();
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('profit_loss_reports')) {
            Schema::create('profit_loss_reports', function (Blueprint $table) {
                $table->id();
                $table->string('period');
                $table->decimal('total_sales', 15, 2);
                $table->decimal('total_purchases', 15, 2);
                $table->decimal('total_expenses', 15, 2);
                $table->decimal('gross_profit', 15, 2);
                $table->decimal('net_profit', 15, 2);
                $table->timestamps();
                $table->index('period');
            });
        }

        if (!Schema::hasTable('balance_sheet_reports')) {
            Schema::create('balance_sheet_reports', function (Blueprint $table) {
                $table->id();
                $table->string('period');
                $table->decimal('total_assets', 15, 2);
                $table->decimal('total_liabilities', 15, 2);
                $table->decimal('total_equity', 15, 2);
                $table->timestamps();
                $table->index('period');
            });
        }

        if (!Schema::hasTable('cash_flow_reports')) {
            Schema::create('cash_flow_reports', function (Blueprint $table) {
                $table->id();
                $table->string('period');
                $table->decimal('opening_balance', 15, 2);
                $table->decimal('cash_in', 15, 2);
                $table->decimal('cash_out', 15, 2);
                $table->decimal('closing_balance', 15, 2);
                $table->timestamps();
                $table->index('period');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cash_flow_reports');
        Schema::dropIfExists('balance_sheet_reports');
        Schema::dropIfExists('profit_loss_reports');
        Schema::dropIfExists('stock_valuation');
        Schema::dropIfExists('product_movement_analysis');
    }
}; 