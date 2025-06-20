<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'business_id')) {
                $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('sales', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['business_id', 'branch_id']);
        });
    }
}; 