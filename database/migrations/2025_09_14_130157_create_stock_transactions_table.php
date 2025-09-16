<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('item_id')->constrained()->onDelete('no action');
            $table->enum('type', ['in', 'out', 'adjustment']); // in = stock received, out = stock issued, adjustment = manual adjustment
            $table->integer('quantity'); // positive for 'in', negative for 'out'
            $table->integer('quantity_before'); // stock level before transaction
            $table->integer('quantity_after'); // stock level after transaction
            $table->string('reference_type')->nullable(); // 'purchase_request', 'procurement', 'manual', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record (purchase_request_id, etc.)
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('no action');
            $table->timestamp('transaction_date');
            $table->timestamps();
            
            $table->index(['item_id', 'transaction_date']);
            $table->index(['type', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['performed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
