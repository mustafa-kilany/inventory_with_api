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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Workflow tracking fields
            $table->enum('workflow_status', [
                'pending_department_head',
                'pending_manager', 
                'pending_purchase_department',
                'pending_stock_keeper',
                'approved',
                'rejected',
                'fulfilled',
                'cancelled'
            ])->default('pending_department_head')->after('status');
            
            $table->enum('workflow_type', ['in_stock', 'out_of_stock'])->nullable()->after('workflow_status');
            $table->json('approval_chain')->nullable()->after('workflow_type');
            $table->integer('current_approval_step')->default(0)->after('approval_chain');
            
            // Department head approval
            $table->foreignId('department_head_id')->nullable()->constrained('users')->onDelete('no action')->after('current_approval_step');
            $table->timestamp('department_head_approved_at')->nullable()->after('department_head_id');
            $table->text('department_head_notes')->nullable()->after('department_head_approved_at');
            
            // Manager approval (for out of stock items)
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('no action')->after('department_head_notes');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_id');
            $table->text('manager_notes')->nullable()->after('manager_approved_at');
            
            // Purchase department approval
            $table->foreignId('purchase_department_id')->nullable()->constrained('users')->onDelete('no action')->after('manager_notes');
            $table->timestamp('purchase_department_approved_at')->nullable()->after('purchase_department_id');
            $table->text('purchase_department_notes')->nullable()->after('purchase_department_approved_at');
            
            // Stock keeper fields
            $table->foreignId('stock_keeper_id')->nullable()->constrained('users')->onDelete('no action')->after('purchase_department_notes');
            $table->timestamp('stock_keeper_approved_at')->nullable()->after('stock_keeper_id');
            $table->text('stock_keeper_notes')->nullable()->after('stock_keeper_approved_at');
            
            // Add indexes for performance
            $table->index(['workflow_status', 'created_at']);
            $table->index(['department_head_id', 'workflow_status']);
            $table->index(['manager_id', 'workflow_status']);
            $table->index(['purchase_department_id', 'workflow_status']);
            $table->index(['stock_keeper_id', 'workflow_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropIndex(['workflow_status', 'created_at']);
            $table->dropIndex(['department_head_id', 'workflow_status']);
            $table->dropIndex(['manager_id', 'workflow_status']);
            $table->dropIndex(['purchase_department_id', 'workflow_status']);
            $table->dropIndex(['stock_keeper_id', 'workflow_status']);
            
            $table->dropForeign(['department_head_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['purchase_department_id']);
            $table->dropForeign(['stock_keeper_id']);
            
            $table->dropColumn([
                'workflow_status',
                'workflow_type', 
                'approval_chain',
                'current_approval_step',
                'department_head_id',
                'department_head_approved_at',
                'department_head_notes',
                'manager_id',
                'manager_approved_at',
                'manager_notes',
                'purchase_department_id',
                'purchase_department_approved_at',
                'purchase_department_notes',
                'stock_keeper_id',
                'stock_keeper_approved_at',
                'stock_keeper_notes'
            ]);
        });
    }
};