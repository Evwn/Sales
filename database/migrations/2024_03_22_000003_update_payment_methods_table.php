<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'business_id')) {
                $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payment_methods', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payment_methods', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['business_id', 'branch_id', 'deleted_at']);
        });
    }
}; 