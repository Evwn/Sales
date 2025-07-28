<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0)->change();
            }
        });
    }
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->change();
        });
    }
};
