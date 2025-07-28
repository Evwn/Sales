<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('batch_size', 10, 2)->nullable()->after('unit_value');
        });
    }
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('batch_size');
        });
    }
}; 