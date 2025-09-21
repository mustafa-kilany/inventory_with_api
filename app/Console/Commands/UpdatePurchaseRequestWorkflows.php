<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;

class UpdatePurchaseRequestWorkflows extends Command
{
    protected $signature = 'purchase-requests:update-workflows';
    protected $description = 'Update existing purchase requests with workflow fields';

    public function handle()
    {
        $this->info('Updating purchase request workflows...');
        
        $purchaseRequests = PurchaseRequest::whereNull('workflow_status')->get();
        
        $this->info("Found {$purchaseRequests->count()} purchase requests to update");
        
        $bar = $this->output->createProgressBar($purchaseRequests->count());
        $bar->start();
        
        foreach ($purchaseRequests as $purchaseRequest) {
            $purchaseRequest->initializeWorkflow();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info('✅ All purchase requests have been updated with workflow fields!');
        
        // Show summary
        $this->newLine();
        $this->info('Workflow Summary:');
        $this->table(
            ['Workflow Type', 'Count'],
            [
                ['In-Stock', PurchaseRequest::where('workflow_type', 'in_stock')->count()],
                ['Out-of-Stock', PurchaseRequest::where('workflow_type', 'out_of_stock')->count()],
            ]
        );
        
        $this->newLine();
        $this->info('Status Summary:');
        $this->table(
            ['Workflow Status', 'Count'],
            [
                ['Pending Department Head', PurchaseRequest::where('workflow_status', 'pending_department_head')->count()],
                ['Pending Purchase Department', PurchaseRequest::where('workflow_status', 'pending_purchase_department')->count()],
                ['Pending Stock Keeper', PurchaseRequest::where('workflow_status', 'pending_stock_keeper')->count()],
                ['Approved', PurchaseRequest::where('workflow_status', 'approved')->count()],
                ['Fulfilled', PurchaseRequest::where('workflow_status', 'fulfilled')->count()],
            ]
        );
    }
}