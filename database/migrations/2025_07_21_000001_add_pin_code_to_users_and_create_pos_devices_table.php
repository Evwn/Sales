<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Add pin_code to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin_code', 6)->nullable()->after('password');
            $table->unique(['business_id', 'pin_code']); // PIN must be unique within a business
        });

        // Create pos_devices table
        Schema::create('pos_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_uuid')->unique();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('registered_by'); // user_id of owner/manager
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'pin_code']);
            $table->dropColumn('pin_code');
        });
        Schema::dropIfExists('pos_devices');
    }
}; 