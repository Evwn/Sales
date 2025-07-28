<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('guard_name')->after('name');
            });
        }
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('guard_name')->after('name');
            });
        }
        if (!Schema::hasColumn('roles', 'owner_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->nullable()->index()->after('guard_name');
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }
        if (Schema::hasColumn('roles', 'owner_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
                $table->dropColumn('owner_id');
            });
        }
    }
}; 