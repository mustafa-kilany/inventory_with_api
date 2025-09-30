<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            // --- Workflow meta ---
            if (!Schema::hasColumn('purchase_requests', 'workflow_status')) {
                $table->string('workflow_status')->nullable()->after('status');
            }
            if (!Schema::hasColumn('purchase_requests', 'workflow_type')) {
                $table->string('workflow_type')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('purchase_requests', 'approval_chain')) {
                $table->json('approval_chain')->nullable()->after('workflow_type');
            }
            if (!Schema::hasColumn('purchase_requests', 'current_approval_step')) {
                $table->unsignedTinyInteger('current_approval_step')->default(0)->after('approval_chain');
            }

            // --- Role approval stamps (add only if missing) ---
            if (!Schema::hasColumn('purchase_requests', 'department_head_id')) {
                $table->foreignId('department_head_id')->nullable()
                    ->constrained('users')->onDelete('no action');
                $table->timestamp('department_head_approved_at')->nullable();
                $table->text('department_head_notes')->nullable();
            }

            if (!Schema::hasColumn('purchase_requests', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()
                    ->constrained('users')->onDelete('no action');
                $table->timestamp('manager_approved_at')->nullable();
                $table->text('manager_notes')->nullable();
            }

            if (!Schema::hasColumn('purchase_requests', 'purchase_department_id')) {
                $table->foreignId('purchase_department_id')->nullable()
                    ->constrained('users')->onDelete('no action');
                $table->timestamp('purchase_department_approved_at')->nullable();
                $table->text('purchase_department_notes')->nullable();
            }

            if (!Schema::hasColumn('purchase_requests', 'stock_keeper_id')) {
                $table->foreignId('stock_keeper_id')->nullable()
                    ->constrained('users')->onDelete('no action');
                $table->timestamp('stock_keeper_approved_at')->nullable();
                $table->text('stock_keeper_notes')->nullable();
            }

            // --- NEW: Owner (Doc. Zuhair) ---
            if (!Schema::hasColumn('purchase_requests', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()
                    ->constrained('users')->onDelete('no action');
                $table->timestamp('owner_approved_at')->nullable();
                $table->text('owner_notes')->nullable();
            }

            // --- NEW: Purchase execution (by Purchase Department after owner approval) ---
            if (!Schema::hasColumn('purchase_requests', 'purchase_executed_by')) {
                $table->foreignId('purchase_executed_by')->nullable()
                    ->constrained('users')->onDelete('no action');
            }
            if (!Schema::hasColumn('purchase_requests', 'purchase_executed_at')) {
                $table->timestamp('purchase_executed_at')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'purchase_execution_notes')) {
                $table->text('purchase_execution_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Drop FKs first (if they exist)
            if (Schema::hasColumn('purchase_requests', 'department_head_id')) {
                $table->dropForeign('purchase_requests_department_head_id_foreign');
            }
            if (Schema::hasColumn('purchase_requests', 'manager_id')) {
                $table->dropForeign('purchase_requests_manager_id_foreign');
            }
            if (Schema::hasColumn('purchase_requests', 'purchase_department_id')) {
                $table->dropForeign('purchase_requests_purchase_department_id_foreign');
            }
            if (Schema::hasColumn('purchase_requests', 'stock_keeper_id')) {
                $table->dropForeign('purchase_requests_stock_keeper_id_foreign');
            }
            if (Schema::hasColumn('purchase_requests', 'owner_id')) {
                $table->dropForeign('purchase_requests_owner_id_foreign');
            }
            if (Schema::hasColumn('purchase_requests', 'purchase_executed_by')) {
                $table->dropForeign('purchase_requests_purchase_executed_by_foreign');
            }

            // Then drop columns (if present)
            $cols = [
                'purchase_executed_by',
                'purchase_executed_at',
                'purchase_execution_notes',
                'owner_id', 'owner_approved_at', 'owner_notes',
                'stock_keeper_id', 'stock_keeper_approved_at', 'stock_keeper_notes',
                'purchase_department_id', 'purchase_department_approved_at', 'purchase_department_notes',
                'manager_id', 'manager_approved_at', 'manager_notes',
                'department_head_id', 'department_head_approved_at', 'department_head_notes',
                'current_approval_step', 'approval_chain', 'workflow_type', 'workflow_status',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('purchase_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
