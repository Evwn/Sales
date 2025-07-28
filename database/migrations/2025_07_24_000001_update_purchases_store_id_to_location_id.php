<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['store_id']);
            // Rename the column
            $table->renameColumn('store_id', 'location_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            // Add the new foreign key
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->renameColumn('location_id', 'store_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade')->onDelete('set null');
        });
    }
}; 