<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2); // e.g. A, B, C
            $table->string('description');
            $table->decimal('rate', 5, 2); // e.g. 16.00, 0.00
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down() {
        Schema::dropIfExists('tax_groups');
    }
}; 