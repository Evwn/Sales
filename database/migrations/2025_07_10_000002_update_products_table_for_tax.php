<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_group_id')->nullable()->after('branch_id');
            $table->boolean('tax_enabled')->default(false)->after('tax_group_id');
            $table->foreign('tax_group_id')->references('id')->on('tax_groups')->onDelete('set null');
        });
    }
    public function down() {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn(['tax_group_id', 'tax_enabled']);
        });
    }
}; 