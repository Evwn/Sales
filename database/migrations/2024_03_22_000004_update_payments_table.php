<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'business_id')) {
                $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('payments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['business_id', 'branch_id', 'created_by', 'deleted_at']);
        });
    }
}; 