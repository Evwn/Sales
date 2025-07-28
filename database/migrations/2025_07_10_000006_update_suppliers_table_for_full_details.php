<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('account_name')->nullable()->collation('utf8mb4_unicode_ci')->after('address');
            $table->string('account_number')->nullable()->collation('utf8mb4_unicode_ci')->after('account_name');
            $table->string('bank_name')->nullable()->collation('utf8mb4_unicode_ci')->after('account_number');
            $table->string('bank_code')->nullable()->collation('utf8mb4_unicode_ci')->after('bank_name');
            $table->string('name')->unique()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['account_name', 'account_number', 'bank_name', 'bank_code']);
            $table->dropUnique(['name']);
        });
    }
}; 