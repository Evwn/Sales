<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // ITEMS TABLE
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('items', 'tax_group_id')) {
                $table->unsignedBigInteger('tax_group_id')->nullable()->after('is_taxable');
                $table->foreign('tax_group_id')->references('id')->on('tax_groups')->onDelete('set null');
            }
            if (Schema::hasColumn('items', 'business_id')) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            }
            if (Schema::hasColumn('items', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
        // TAX GROUPS TABLE
        Schema::table('tax_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_groups', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }
    public function down(): void {
        // ITEMS TABLE
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'business_id')) {
                $table->unsignedBigInteger('business_id')->after('id');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            }
            if (!Schema::hasColumn('items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('is_taxable');
            }
            if (Schema::hasColumn('items', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('items', 'tax_group_id')) {
                $table->dropForeign(['tax_group_id']);
                $table->dropColumn('tax_group_id');
            }
        });
        // TAX GROUPS TABLE
        Schema::table('tax_groups', function (Blueprint $table) {
            if (Schema::hasColumn('tax_groups', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
}; 