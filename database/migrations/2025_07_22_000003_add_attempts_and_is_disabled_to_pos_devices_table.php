<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->boolean('is_disabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'is_disabled']);
        });
    }
}; 