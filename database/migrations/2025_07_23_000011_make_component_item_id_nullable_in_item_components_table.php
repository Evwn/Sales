<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('item_components', function (Blueprint $table) {
            $table->unsignedBigInteger('component_item_id')->nullable()->change();
        });
    }
    public function down()
    {
        Schema::table('item_components', function (Blueprint $table) {
            $table->unsignedBigInteger('component_item_id')->nullable(false)->change();
        });
    }
}; 