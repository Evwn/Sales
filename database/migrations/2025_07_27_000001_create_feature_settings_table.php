<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('feature_key', 64);
            $table->boolean('is_enabled')->default(1);
            $table->timestamps();
            $table->unique(['business_id', 'branch_id', 'feature_key'], 'unique_feature');
        });
    }
    public function down() {
        Schema::dropIfExists('feature_settings');
    }
}; 