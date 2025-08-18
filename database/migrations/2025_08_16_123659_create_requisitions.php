<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Links requisition to the requesting location (store/branch)
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('user_id')->nullable(); // the requester
            $table->unsignedBigInteger('approved_by')->nullable(); // approver
            
            // Reference & details
            $table->string('reference')->unique(); // e.g., REQ-2025-0001
            $table->date('requisition_date')->nullable();
            $table->string('priority')->nullable(); // Low, Medium, High, Urgent
            $table->text('notes')->nullable();
            
            // Status flow
            $table->enum('status', [
                'draft',          // created but not submitted
                'pending',        // awaiting approval
                'approved',       // approved by manager
                'rejected',       // rejected by approver
                'converted',      // converted into quotation
            ])->default('draft');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('requisition_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedBigInteger('requisition_id');
            $table->unsignedBigInteger('item_id'); // links to your items/products table
            $table->decimal('quantity', 15, 2);
            $table->string('unit')->nullable(); // e.g., pcs, box, kg
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('requisition_id')->references('id')->on('requisitions')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_items');
        Schema::dropIfExists('requisitions');
    }
};
