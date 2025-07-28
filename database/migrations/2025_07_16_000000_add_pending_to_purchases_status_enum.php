<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'pending' to the status enum
        DB::statement("ALTER TABLE purchases MODIFY status ENUM('draft','pending','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending' from the status enum (revert to original)
        DB::statement("ALTER TABLE purchases MODIFY status ENUM('draft','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }
}; 