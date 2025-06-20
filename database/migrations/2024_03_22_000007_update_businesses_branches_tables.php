<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('businesses', 'registration_number')) {
                $table->string('registration_number', 100)->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('businesses', 'tax_pin')) {
                $table->string('tax_pin', 100)->nullable()->after('registration_number');
            }
            if (!Schema::hasColumn('businesses', 'website')) {
                $table->string('website')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('businesses', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('businesses', 'country')) {
                $table->string('country', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('businesses', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('country');
            }
            if (!Schema::hasColumn('businesses', 'terms_and_conditions')) {
                $table->text('terms_and_conditions')->nullable()->after('logo_path');
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'gps_latitude')) {
                $table->decimal('gps_latitude', 10, 8)->nullable()->after('location');
            }
            if (!Schema::hasColumn('branches', 'gps_longitude')) {
                $table->decimal('gps_longitude', 11, 8)->nullable()->after('gps_latitude');
            }
            if (!Schema::hasColumn('branches', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('branches', 'barcode_path')) {
                $table->string('barcode_path')->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'registration_number',
                'tax_pin',
                'website',
                'city',
                'country',
                'logo_path',
                'terms_and_conditions'
            ]);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'gps_latitude',
                'gps_longitude',
                'email',
                'barcode_path'
            ]);
        });
    }
}; 