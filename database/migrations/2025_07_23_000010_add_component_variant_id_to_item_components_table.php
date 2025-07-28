<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('item_components', function (Blueprint $table) {
            $table->unsignedBigInteger('component_variant_id')->nullable()->after('component_item_id');
            $table->foreign('component_variant_id')->references('id')->on('item_variants')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::table('item_components', function (Blueprint $table) {
            $table->dropForeign(['component_variant_id']);
            $table->dropColumn('component_variant_id');
        });
    }
}; 