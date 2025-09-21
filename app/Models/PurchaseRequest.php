<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'requested_by',
        'status',
        'workflow_status',
        'workflow_type',
        'approval_chain',
        'current_approval_step',
        'justification',
        'priority',
        'needed_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'fulfilled_by',
        'fulfilled_at',
        'estimated_total',
        'actual_total',
        // Workflow approval fields
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
        'stock_keeper_notes',
    ];

    protected function casts(): array
    {
        return [
            'needed_by' => 'date',
            'approved_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'estimated_total' => 'decimal:2',
            'actual_total' => 'decimal:2',
            'approval_chain' => 'array',
            'department_head_approved_at' => 'datetime',
            'manager_approved_at' => 'datetime',
            'purchase_department_approved_at' => 'datetime',
            'stock_keeper_approved_at' => 'datetime',
        ];
    }

    // Boot method to generate request number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = 'PR-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // Business logic methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    public function canBeFulfilled(): bool
    {
        return $this->isApproved();
    }

    public function approve(User $approver, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $approver, string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function fulfill(User $fulfiller): void
    {
        $this->update([
            'status' => 'fulfilled',
            'fulfilled_by' => $fulfiller->id,
            'fulfilled_at' => now(),
        ]);
    }

    public function calculateEstimatedTotal(): float
    {
        return $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity_requested * unit_price'));
    }

    public function calculateActualTotal(): float
    {
        return $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity_fulfilled * unit_price'));
    }

    // Relationships
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    // Workflow relationships
    public function departmentHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function purchaseDepartment(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchase_department_id');
    }

    public function stockKeeper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stock_keeper_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOverdue($query)
    {
        return $query->where('needed_by', '<', now())->whereIn('status', ['pending', 'approved']);
    }

    // Enhanced Workflow Methods
    public function initializeWorkflow(): void
    {
        // Check if all items are in stock
        $allInStock = true;
        foreach ($this->items as $requestItem) {
            if (!$requestItem->item->canFulfill($requestItem->quantity_requested)) {
                $allInStock = false;
                break;
            }
        }
        
        // Set workflow type and status
        $workflowType = $allInStock ? 'in_stock' : 'out_of_stock';
        $this->workflow_type = $workflowType;
        
        // Build approval chain based on stock availability
        if ($allInStock) {
            // In stock: Department Head → Stock Keeper
            $this->approval_chain = ['department_head', 'stock_keeper'];
            $this->workflow_status = 'pending_department_head';
        } else {
            // Out of stock: Department Head → Purchase Department → Stock Keeper
            $this->approval_chain = ['department_head', 'purchase_department', 'stock_keeper'];
            $this->workflow_status = 'pending_department_head';
        }
        
        $this->current_approval_step = 0;
        $this->save();
    }

    public function approveByDepartmentHead(User $departmentHead, string $notes = null): bool
    {
        if ($this->workflow_status !== 'pending_department_head') {
            return false;
        }

        $this->department_head_id = $departmentHead->id;
        $this->department_head_approved_at = now();
        $this->department_head_notes = $notes;
        $this->current_approval_step = 1;

        // Move to next step based on workflow type
        if ($this->workflow_type === 'in_stock') {
            $this->workflow_status = 'pending_stock_keeper';
        } else {
            // For out of stock items, go to Purchase Department
            $this->workflow_status = 'pending_purchase_department';
        }

        $this->save();
        return true;
    }

    public function approveByManager(User $manager, string $notes = null): bool
    {
        if ($this->workflow_status !== 'pending_manager') {
            return false;
        }

        $this->manager_id = $manager->id;
        $this->manager_approved_at = now();
        $this->manager_notes = $notes;
        $this->current_approval_step = 2;
        $this->workflow_status = 'pending_purchase_department';
        
        $this->save();
        return true;
    }

    public function approveByPurchaseDepartment(User $purchaseDept, string $notes = null): bool
    {
        if ($this->workflow_status !== 'pending_purchase_department') {
            return false;
        }

        $this->purchase_department_id = $purchaseDept->id;
        $this->purchase_department_approved_at = now();
        $this->purchase_department_notes = $notes;
        $this->current_approval_step = 2;

        // Automatically add stock for out-of-stock items
        $this->autoStockItems($purchaseDept);
        
        // Move to Stock Keeper for fulfillment
        $this->workflow_status = 'pending_stock_keeper';
        
        $this->save();
        return true;
    }

    public function approveByStockKeeper(User $stockKeeper, string $notes = null): bool
    {
        if ($this->workflow_status !== 'pending_stock_keeper') {
            return false;
        }

        $this->stock_keeper_id = $stockKeeper->id;
        $this->stock_keeper_approved_at = now();
        $this->stock_keeper_notes = $notes;
        $this->workflow_status = 'approved';
        $this->status = 'approved';
        
        $this->save();
        return true;
    }

    /**
     * Automatically add stock to items when Department Head approves out-of-stock requests
     */
    public function autoStockItems(User $departmentHead): void
    {
        foreach ($this->items as $requestItem) {
            $item = $requestItem->item;
            $quantityNeeded = $requestItem->quantity_requested;
            
            // Add the exact quantity needed to fulfill the request
            $item->addStockByPurchaseDepartment(
                $quantityNeeded,
                $departmentHead,
                "Auto-stocked upon Department Head approval for request: {$this->request_number}"
            );
        }
    }

    public function rejectWorkflow(User $rejector, string $reason): bool
    {
        $currentStatus = $this->workflow_status;
        $this->workflow_status = 'rejected';
        $this->status = 'rejected';
        
        // Store rejection details based on current step
        switch ($currentStatus) {
            case 'pending_department_head':
                $this->department_head_id = $rejector->id;
                $this->department_head_approved_at = now();
                $this->department_head_notes = $reason;
                break;
            case 'pending_manager':
                $this->manager_id = $rejector->id;
                $this->manager_approved_at = now();
                $this->manager_notes = $reason;
                break;
            case 'pending_purchase_department':
                $this->purchase_department_id = $rejector->id;
                $this->purchase_department_approved_at = now();
                $this->purchase_department_notes = $reason;
                break;
            case 'pending_stock_keeper':
                $this->stock_keeper_id = $rejector->id;
                $this->stock_keeper_approved_at = now();
                $this->stock_keeper_notes = $reason;
                break;
        }
        
        $this->save();
        return true;
    }

    public function getNextApprover(): ?string
    {
        if ($this->current_approval_step >= count($this->approval_chain ?? [])) {
            return null;
        }
        
        return $this->approval_chain[$this->current_approval_step] ?? null;
    }

    public function getCurrentApprovalStepName(): ?string
    {
        $step = $this->getNextApprover();
        
        return match($step) {
            'department_head' => 'Department Head Approval',
            'manager' => 'Manager Approval',
            'purchase_department' => 'Purchase Department Approval',
            'stock_keeper' => 'Stock Keeper Approval',
            default => 'Complete'
        };
    }

    public function isWorkflowComplete(): bool
    {
        return in_array($this->workflow_status, ['approved', 'rejected', 'fulfilled', 'cancelled']);
    }

    public function canUserApprove(User $user): bool
    {
        $nextApprover = $this->getNextApprover();
        
        return match($nextApprover) {
            'department_head' => $user->hasRole('department_head') || $user->hasRole('administrator'),
            'manager' => $user->hasRole('manager') || $user->hasRole('administrator'),
            'purchase_department' => $user->hasRole('purchase_department') || $user->hasRole('administrator'),
            'stock_keeper' => $user->hasRole('stock_keeper') || $user->hasRole('administrator'),
            default => false
        };
    }
}
