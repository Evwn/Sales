<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('businesses', 'phone')) {
                $table->string('phone', 50)->after('description');
            }
            if (!Schema::hasColumn('businesses', 'email')) {
                $table->string('email', 255)->after('phone');
            }
            if (!Schema::hasColumn('businesses', 'tax_number')) {
                $table->string('tax_number', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('businesses', 'registration_number')) {
                $table->string('registration_number', 50)->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('businesses', 'industry')) {
                $table->string('industry', 100)->nullable()->after('registration_number');
            }
            if (!Schema::hasColumn('businesses', 'address')) {
                $table->string('address', 255)->after('industry');
            }
            if (!Schema::hasColumn('businesses', 'city')) {
                $table->string('city', 100)->after('address');
            }
            if (!Schema::hasColumn('businesses', 'state')) {
                $table->string('state', 100)->after('city');
            }
            if (!Schema::hasColumn('businesses', 'country')) {
                $table->string('country', 100)->after('state');
            }
            if (!Schema::hasColumn('businesses', 'postal_code')) {
                $table->string('postal_code', 20)->after('country');
            }
            if (!Schema::hasColumn('businesses', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('businesses', 'tax_document_path')) {
                $table->string('tax_document_path')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('businesses', 'registration_document_path')) {
                $table->string('registration_document_path')->nullable()->after('tax_document_path');
            }
            if (!Schema::hasColumn('businesses', 'owner_id')) {
                $table->foreignId('owner_id')->after('registration_document_path')->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'owner_id')) {
                $table->dropForeign(['owner_id']);
            }
            $columns = [
                'description',
                'phone',
                'email',
                'tax_number',
                'registration_number',
                'industry',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'logo_path',
                'tax_document_path',
                'registration_document_path',
                'owner_id',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('businesses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 