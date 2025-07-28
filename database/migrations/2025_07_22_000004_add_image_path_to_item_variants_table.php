<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('barcode');
        });
    }
    public function down()
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
}; 