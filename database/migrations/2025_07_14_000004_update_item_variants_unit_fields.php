<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('item_variants', function (Blueprint $table) {
            if (Schema::hasColumn('item_variants', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
            $table->decimal('unit_value', 10, 2)->nullable()->after('track_stock');
            $table->string('barcode')->nullable()->change();
        });
    }
    public function down()
    {
        Schema::table('item_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('track_stock');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->dropColumn('unit_value');
            $table->string('barcode')->nullable()->change();
        });
    }
}; 