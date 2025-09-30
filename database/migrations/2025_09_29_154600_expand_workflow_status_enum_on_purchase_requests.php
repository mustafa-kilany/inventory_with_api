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
        // SQL Server: enum is implemented as a CHECK constraint; extend it to include owner stages
        DB::unprepared(<<<'SQL'
DECLARE @ConstraintName NVARCHAR(128);
SELECT @ConstraintName = dc.name
FROM sys.check_constraints dc
JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
WHERE dc.parent_object_id = OBJECT_ID('purchase_requests') AND c.name = 'workflow_status';

IF @ConstraintName IS NOT NULL
BEGIN
    EXEC('ALTER TABLE [purchase_requests] DROP CONSTRAINT [' + @ConstraintName + ']');
END

ALTER TABLE [purchase_requests]
ADD CONSTRAINT [CK_purchase_requests_workflow_status]
CHECK ([workflow_status] IN (
    'pending_department_head',
    'pending_manager',
    'pending_purchase_department',
    'pending_owner',
    'pending_purchase_execution',
    'pending_stock_keeper',
    'approved',
    'rejected',
    'fulfilled',
    'cancelled'
));
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous constraint (without owner stages)
        DB::unprepared(<<<'SQL'
DECLARE @ConstraintName NVARCHAR(128);
SELECT @ConstraintName = dc.name
FROM sys.check_constraints dc
JOIN sys.columns c ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
WHERE dc.parent_object_id = OBJECT_ID('purchase_requests') AND c.name = 'workflow_status';

IF @ConstraintName IS NOT NULL
BEGIN
    EXEC('ALTER TABLE [purchase_requests] DROP CONSTRAINT [' + @ConstraintName + ']');
END

ALTER TABLE [purchase_requests]
ADD CONSTRAINT [CK_purchase_requests_workflow_status]
CHECK ([workflow_status] IN (
    'pending_department_head',
    'pending_manager',
    'pending_purchase_department',
    'pending_stock_keeper',
    'approved',
    'rejected',
    'fulfilled',
    'cancelled'
));
SQL);
    }
};


