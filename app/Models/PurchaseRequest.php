<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Support\Str;

// class PurchaseRequest extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'request_number',
//         'requested_by',
//         'status',
//         'workflow_status',
//         'workflow_type',
//         'approval_chain',
//         'current_approval_step',
//         'justification',
//         'priority',
//         'needed_by',
//         'approved_by',
//         'approved_at',
//         'approval_notes',
//         'fulfilled_by',
//         'fulfilled_at',
//         'estimated_total',
//         'actual_total',
//         // Workflow approval fields
//         'department_head_id',
//         'department_head_approved_at',
//         'department_head_notes',
//         'manager_id',
//         'manager_approved_at',
//         'manager_notes',
//         'purchase_department_id',
//         'purchase_department_approved_at',
//         'purchase_department_notes',
//         'stock_keeper_id',
//         'stock_keeper_approved_at',
//         'stock_keeper_notes',
//         'owner_id',
// 'owner_approved_at',
// 'owner_notes',

//     ];

//     protected function casts(): array
//     {
//         return [
//             'needed_by' => 'date',
//             'approved_at' => 'datetime',
//             'fulfilled_at' => 'datetime',
//             'estimated_total' => 'decimal:2',
//             'actual_total' => 'decimal:2',
//             'approval_chain' => 'array',
//             'department_head_approved_at' => 'datetime',
//             'manager_approved_at' => 'datetime',
//             'purchase_department_approved_at' => 'datetime',
//             'stock_keeper_approved_at' => 'datetime',
//             'owner_approved_at' => 'datetime',

//         ];
//     }

//     // Boot method to generate request number
//     protected static function boot()
//     {
//         parent::boot();
        
//         static::creating(function ($model) {
//             if (empty($model->request_number)) {
//                 $model->request_number = 'PR-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
//             }
//         });
//     }

//     // Business logic methods
//     public function isPending(): bool
//     {
//         return $this->status === 'pending';
//     }

//     public function isApproved(): bool
//     {
//         return $this->status === 'approved';
//     }

//     public function isRejected(): bool
//     {
//         return $this->status === 'rejected';
//     }

//     public function isFulfilled(): bool
//     {
//         return $this->status === 'fulfilled';
//     }

//     public function isCancelled(): bool
//     {
//         return $this->status === 'cancelled';
//     }

//     public function canBeApproved(): bool
//     {
//         return $this->isPending();
//     }

//     public function canBeRejected(): bool
//     {
//         return $this->isPending();
//     }

//     public function canBeFulfilled(): bool
//     {
//         return $this->isApproved();
//     }

//     public function approve(User $approver, string $notes = null): void
//     {
//         $this->update([
//             'status' => 'approved',
//             'approved_by' => $approver->id,
//             'approved_at' => now(),
//             'approval_notes' => $notes,
//         ]);
//     }

//     public function reject(User $approver, string $notes = null): void
//     {
//         $this->update([
//             'status' => 'rejected',
//             'approved_by' => $approver->id,
//             'approved_at' => now(),
//             'approval_notes' => $notes,
//         ]);
//     }

//     public function fulfill(User $fulfiller): void
//     {
//         $this->update([
//             'status' => 'fulfilled',
//             'fulfilled_by' => $fulfiller->id,
//             'fulfilled_at' => now(),
//         ]);
//     }

//     public function calculateEstimatedTotal(): float
//     {
//         return $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity_requested * unit_price'));
//     }

//     public function calculateActualTotal(): float
//     {
//         return $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity_fulfilled * unit_price'));
//     }

//     // Relationships
//     public function requestedBy(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'requested_by');
//     }

//     public function approvedBy(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'approved_by');
//     }

//     public function fulfilledBy(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'fulfilled_by');
//     }

//     public function items(): HasMany
//     {
//         return $this->hasMany(PurchaseRequestItem::class);
//     }

//     // Workflow relationships
//     public function departmentHead(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'department_head_id');
//     }

//     public function manager(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'manager_id');
//     }

//     public function purchaseDepartment(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'purchase_department_id');
//     }

//     public function stockKeeper(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'stock_keeper_id');
//     }

//     // Scopes
//     public function scopePending($query)
//     {
//         return $query->where('status', 'pending');
//     }

//     public function scopeApproved($query)
//     {
//         return $query->where('status', 'approved');
//     }

//     public function scopeRejected($query)
//     {
//         return $query->where('status', 'rejected');
//     }

//     public function scopeFulfilled($query)
//     {
//         return $query->where('status', 'fulfilled');
//     }

//     public function scopeByPriority($query, $priority)
//     {
//         return $query->where('priority', $priority);
//     }

//     public function scopeUrgent($query)
//     {
//         return $query->where('priority', 'urgent');
//     }

//     public function scopeOverdue($query)
//     {
//         return $query->where('needed_by', '<', now())->whereIn('status', ['pending', 'approved']);
//     }

//     // Enhanced Workflow Methods
//     public function initializeWorkflow(): void
//     {
//         // Check if all items are in stock
//         $allInStock = true;
//         foreach ($this->items as $requestItem) {
//             if (!$requestItem->item->canFulfill($requestItem->quantity_requested)) {
//                 $allInStock = false;
//                 break;
//             }
//         }
        
//         // Set workflow type and status
//         $workflowType = $allInStock ? 'in_stock' : 'out_of_stock';
//         $this->workflow_type = $workflowType;
        
//         // Build approval chain based on stock availability
//         if ($allInStock) {
//             // In stock: Department Head → Stock Keeper
//             $this->approval_chain = ['department_head', 'stock_keeper'];
//             $this->workflow_status = 'pending_department_head';
//         } else {
//             // Out of stock: Department Head → Purchase Department → Stock Keeper
//             $this->approval_chain = ['department_head', 'purchase_department', 'stock_keeper'];
//             $this->workflow_status = 'pending_department_head';
//         }
        
//         $this->current_approval_step = 0;
//         $this->save();
//     }

//     public function approveByDepartmentHead(User $departmentHead, string $notes = null): bool
//     {
//         if ($this->workflow_status !== 'pending_department_head') {
//             return false;
//         }

//         $this->department_head_id = $departmentHead->id;
//         $this->department_head_approved_at = now();
//         $this->department_head_notes = $notes;
//         $this->current_approval_step = 1;

//         // Move to next step based on workflow type
//         if ($this->workflow_type === 'in_stock') {
//             $this->workflow_status = 'pending_stock_keeper';
//         } else {
//             // For out of stock items, go to Purchase Department
//             $this->workflow_status = 'pending_purchase_department';
//         }

//         $this->save();
//         return true;
//     }

//     public function approveByManager(User $manager, string $notes = null): bool
//     {
//         if ($this->workflow_status !== 'pending_manager') {
//             return false;
//         }

//         $this->manager_id = $manager->id;
//         $this->manager_approved_at = now();
//         $this->manager_notes = $notes;
//         $this->current_approval_step = 2;
//         $this->workflow_status = 'pending_purchase_department';
        
//         $this->save();
//         return true;
//     }

//     public function approveByPurchaseDepartment(User $purchaseDept, string $notes = null): bool
//     {
//         if ($this->workflow_status !== 'pending_purchase_department') {
//             return false;
//         }

//         $this->purchase_department_id = $purchaseDept->id;
//         $this->purchase_department_approved_at = now();
//         $this->purchase_department_notes = $notes;
//         $this->current_approval_step = 2;

//         // Automatically add stock for out-of-stock items
//         $this->autoStockItems($purchaseDept);
        
//         // Move to Stock Keeper for fulfillment
//         $this->workflow_status = 'pending_stock_keeper';
        
//         $this->save();
//         return true;
//     }

//     public function approveByStockKeeper(User $stockKeeper, string $notes = null): bool
//     {
//         if ($this->workflow_status !== 'pending_stock_keeper') {
//             return false;
//         }

//         $this->stock_keeper_id = $stockKeeper->id;
//         $this->stock_keeper_approved_at = now();
//         $this->stock_keeper_notes = $notes;
//         $this->workflow_status = 'approved';
//         $this->status = 'approved';
        
//         $this->save();
//         return true;
//     }

//     /**
//      * Automatically add stock to items when Department Head approves out-of-stock requests
//      */
//     public function autoStockItems(User $departmentHead): void
//     {
//         foreach ($this->items as $requestItem) {
//             $item = $requestItem->item;
//             $quantityNeeded = $requestItem->quantity_requested;
            
//             // Add the exact quantity needed to fulfill the request
//             $item->addStockByPurchaseDepartment(
//                 $quantityNeeded,
//                 $departmentHead,
//                 "Auto-stocked upon Department Head approval for request: {$this->request_number}"
//             );
//         }
//     }

//     public function rejectWorkflow(User $rejector, string $reason): bool
//     {
//         $currentStatus = $this->workflow_status;
//         $this->workflow_status = 'rejected';
//         $this->status = 'rejected';
        
//         // Store rejection details based on current step
//         switch ($currentStatus) {
//             case 'pending_department_head':
//                 $this->department_head_id = $rejector->id;
//                 $this->department_head_approved_at = now();
//                 $this->department_head_notes = $reason;
//                 break;
//             case 'pending_manager':
//                 $this->manager_id = $rejector->id;
//                 $this->manager_approved_at = now();
//                 $this->manager_notes = $reason;
//                 break;
//             case 'pending_purchase_department':
//                 $this->purchase_department_id = $rejector->id;
//                 $this->purchase_department_approved_at = now();
//                 $this->purchase_department_notes = $reason;
//                 break;
//             case 'pending_stock_keeper':
//                 $this->stock_keeper_id = $rejector->id;
//                 $this->stock_keeper_approved_at = now();
//                 $this->stock_keeper_notes = $reason;
//                 break;
//         }
        
//         $this->save();
//         return true;
//     }

//     public function getNextApprover(): ?string
//     {
//         if ($this->current_approval_step >= count($this->approval_chain ?? [])) {
//             return null;
//         }
        
//         return $this->approval_chain[$this->current_approval_step] ?? null;
//     }

//     public function getCurrentApprovalStepName(): ?string
//     {
//         $step = $this->getNextApprover();
        
//         return match($step) {
//             'department_head' => 'Department Head Approval',
//             'manager' => 'Manager Approval',
//             'purchase_department' => 'Purchase Department Approval',
//             'stock_keeper' => 'Stock Keeper Approval',
//             default => 'Complete'
//         };
//     }

//     public function isWorkflowComplete(): bool
//     {
//         return in_array($this->workflow_status, ['approved', 'rejected', 'fulfilled', 'cancelled']);
//     }

//     public function canUserApprove(User $user): bool
//     {
//         $nextApprover = $this->getNextApprover();
        
//         return match($nextApprover) {
//             'department_head' => $user->hasRole('department_head') || $user->hasRole('administrator'),
//             'manager' => $user->hasRole('manager') || $user->hasRole('administrator'),
//             'purchase_department' => $user->hasRole('purchase_department') || $user->hasRole('administrator'),
//             'stock_keeper' => $user->hasRole('stock_keeper') || $user->hasRole('administrator'),
//             default => false
//         };
//     }
// } 


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseRequest extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Workflow status constants
    const WORKFLOW_PENDING_DEPARTMENT_HEAD = 'pending_department_head';
    const WORKFLOW_PENDING_MANAGER = 'pending_manager';
    const WORKFLOW_PENDING_PURCHASE_DEPARTMENT = 'pending_purchase_department';
    const WORKFLOW_PENDING_OWNER = 'pending_owner';
    const WORKFLOW_PENDING_PURCHASE_EXECUTION = 'pending_purchase_execution';
    const WORKFLOW_PENDING_STOCK_KEEPER = 'pending_stock_keeper';
    const WORKFLOW_APPROVED = 'approved';
    const WORKFLOW_REJECTED = 'rejected';

    // Workflow type constants
    const WORKFLOW_TYPE_IN_STOCK = 'in_stock';
    const WORKFLOW_TYPE_OUT_OF_STOCK = 'out_of_stock';

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
        'owner_id',
        'owner_approved_at',
        'owner_notes',
        'purchase_executed_by',
        'purchase_executed_at',
        'purchase_execution_notes',
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
            'owner_approved_at' => 'datetime',
            'purchase_executed_at' => 'datetime',
        ];
    }

    protected $appends = [
        'status_display',
        'priority_display',
        'workflow_status_display',
        'days_until_needed',
        'is_overdue',
        'completion_percentage',
        'approval_history'
    ];

    // Boot method to generate request number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = 'PR-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
            
            // Set default values
            $model->status = $model->status ?: self::STATUS_PENDING;
            $model->priority = $model->priority ?: self::PRIORITY_MEDIUM;
        });

        static::updating(function ($model) {
            // Auto-calculate totals when items change
            if ($model->isDirty('estimated_total') || $model->isDirty('actual_total')) {
                $model->calculateTotals();
            }
        });
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_FULFILLED => 'Fulfilled',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            default => 'Unknown'
        };
    }

    public function getWorkflowStatusDisplayAttribute(): string
    {
        return match($this->workflow_status) {
            self::WORKFLOW_PENDING_DEPARTMENT_HEAD => 'Pending Department Head',
            self::WORKFLOW_PENDING_MANAGER => 'Pending Manager',
            self::WORKFLOW_PENDING_PURCHASE_DEPARTMENT => 'Pending Purchase Department',
            self::WORKFLOW_PENDING_OWNER => 'Pending Owner',
            self::WORKFLOW_PENDING_PURCHASE_EXECUTION => 'Pending Purchase Execution',
            self::WORKFLOW_PENDING_STOCK_KEEPER => 'Pending Stock Keeper',
            self::WORKFLOW_APPROVED => 'Approved',
            self::WORKFLOW_REJECTED => 'Rejected',
            default => 'Unknown'
        };
    }

    public function getDaysUntilNeededAttribute(): ?int
    {
        if (!$this->needed_by) {
            return null;
        }
        
        return now()->diffInDays($this->needed_by, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->needed_by) {
            return false;
        }
        
        return now()->gt($this->needed_by) && !in_array($this->status, [self::STATUS_FULFILLED, self::STATUS_CANCELLED]);
    }

    public function getCompletionPercentageAttribute(): float
    {
        if (!$this->approval_chain || empty($this->approval_chain)) {
            return 0;
        }
        
        return ($this->current_approval_step / count($this->approval_chain)) * 100;
    }

    public function getApprovalHistoryAttribute(): array
    {
        $history = [];
        
        if ($this->department_head_approved_at) {
            $history[] = [
                'step' => 'Department Head',
                'approved_at' => $this->department_head_approved_at,
                'approved_by' => $this->departmentHead?->name,
                'notes' => $this->department_head_notes
            ];
        }
        
        if ($this->manager_approved_at) {
            $history[] = [
                'step' => 'Manager',
                'approved_at' => $this->manager_approved_at,
                'approved_by' => $this->manager?->name,
                'notes' => $this->manager_notes
            ];
        }
        
        if ($this->purchase_department_approved_at) {
            $history[] = [
                'step' => 'Purchase Department',
                'approved_at' => $this->purchase_department_approved_at,
                'approved_by' => $this->purchaseDepartment?->name,
                'notes' => $this->purchase_department_notes
            ];
        }
        
        if ($this->owner_approved_at) {
            $history[] = [
                'step' => 'Owner',
                'approved_at' => $this->owner_approved_at,
                'approved_by' => $this->owner?->name,
                'notes' => $this->owner_notes
            ];
        }
        
        if ($this->purchase_executed_at) {
            $history[] = [
                'step' => 'Purchase Execution',
                'approved_at' => $this->purchase_executed_at,
                'approved_by' => $this->purchaseExecutedBy?->name,
                'notes' => $this->purchase_execution_notes
            ];
        }
        
        if ($this->stock_keeper_approved_at) {
            $history[] = [
                'step' => 'Stock Keeper',
                'approved_at' => $this->stock_keeper_approved_at,
                'approved_by' => $this->stockKeeper?->name,
                'notes' => $this->stock_keeper_notes
            ];
        }
        
        return $history;
    }

    // Business logic methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
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

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->workflow_status === self::WORKFLOW_PENDING_DEPARTMENT_HEAD;
    }

    // Legacy approval methods
    public function approve(User $approver, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $approver, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function fulfill(User $fulfiller): void
    {
        $this->update([
            'status' => self::STATUS_FULFILLED,
            'fulfilled_by' => $fulfiller->id,
            'fulfilled_at' => now(),
        ]);
    }

    public function cancel(User $canceller, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'workflow_status' => 'cancelled',
            'approval_notes' => $reason,
        ]);
    }

    // Calculation methods
    public function calculateEstimatedTotal(): float
    {
        return $this->items()->sum(DB::raw('quantity_requested * unit_price'));
    }

    public function calculateActualTotal(): float
    {
        return $this->items()->sum(DB::raw('quantity_fulfilled * unit_price'));
    }

    public function calculateTotals(): void
    {
        $this->estimated_total = $this->calculateEstimatedTotal();
        $this->actual_total = $this->calculateActualTotal();
    }

    public function updateTotals(): void
    {
        $this->calculateTotals();
        $this->save();
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function purchaseExecutedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchase_executed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', self::STATUS_FULFILLED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('needed_by', '<', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereBetween('needed_by', [now(), now()->addDays($days)])
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function scopeInWorkflowStatus($query, string $status)
    {
        return $query->where('workflow_status', $status);
    }

    public function scopeAwaitingApprovalFrom($query, string $role)
    {
        return $query->where('workflow_status', "pending_{$role}");
    }

    public function scopeRequestedBy($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeWithTotalAbove($query, float $amount)
    {
        return $query->where('estimated_total', '>', $amount);
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
        $workflowType = $allInStock ? self::WORKFLOW_TYPE_IN_STOCK : self::WORKFLOW_TYPE_OUT_OF_STOCK;
        $this->workflow_type = $workflowType;
        
        // Build approval chain based on stock availability
        if ($allInStock) {
            // In stock: Department Head → Stock Keeper (PD cannot act)
            $this->approval_chain = ['department_head', 'stock_keeper'];
            $this->workflow_status = self::WORKFLOW_PENDING_DEPARTMENT_HEAD;
        } else {
            // Out of stock: Department Head → Purchase Department → Owner → Purchase Execution → Stock Keeper
            $this->approval_chain = ['department_head', 'purchase_department', 'owner', 'purchase_execution', 'stock_keeper'];
            $this->workflow_status = self::WORKFLOW_PENDING_DEPARTMENT_HEAD;
        }
        
        $this->current_approval_step = 0;
        $this->save();
    }

    public function approveByDepartmentHead(User $departmentHead, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_DEPARTMENT_HEAD) {
            return false;
        }

        $this->department_head_id = $departmentHead->id;
        $this->department_head_approved_at = now();
        $this->department_head_notes = $notes;
        $this->current_approval_step = 1;

        // Move to next stage depending on workflow type
        if ($this->workflow_type === self::WORKFLOW_TYPE_IN_STOCK) {
            // Skip Purchase Department entirely
            $this->workflow_status = self::WORKFLOW_PENDING_STOCK_KEEPER;
        } else {
            $this->workflow_status = self::WORKFLOW_PENDING_PURCHASE_DEPARTMENT;
        }

        $this->save();
        return true;
    }

    public function approveByManager(User $manager, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_MANAGER) {
            return false;
        }

        $this->manager_id = $manager->id;
        $this->manager_approved_at = now();
        $this->manager_notes = $notes;
        $this->current_approval_step = 2;
        $this->workflow_status = self::WORKFLOW_PENDING_PURCHASE_DEPARTMENT;
        
        $this->save();
        return true;
    }

    public function approveByPurchaseDepartment(User $purchaseDept, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_PURCHASE_DEPARTMENT) {
            return false;
        }
        // Guard: Purchase Department must not act on in-stock workflows
        if ($this->workflow_type === self::WORKFLOW_TYPE_IN_STOCK) {
            return false;
        }

        $this->purchase_department_id = $purchaseDept->id;
        $this->purchase_department_approved_at = now();
        $this->purchase_department_notes = $notes;
        $this->current_approval_step = 2;

        // For out-of-stock items, send to owner for approval
        if ($this->workflow_type === self::WORKFLOW_TYPE_OUT_OF_STOCK) {
            $this->workflow_status = self::WORKFLOW_PENDING_OWNER;
        } else {
            // For in-stock items, auto-stock and move to stock keeper
            $this->autoStockItems($purchaseDept);
            $this->workflow_status = self::WORKFLOW_PENDING_STOCK_KEEPER;
        }
        
        $this->save();
        return true;
    }

    public function approveByOwner(User $owner, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_OWNER) {
            return false;
        }

        $this->owner_id = $owner->id;
        $this->owner_approved_at = now();
        $this->owner_notes = $notes;
        $this->workflow_status = self::WORKFLOW_PENDING_PURCHASE_EXECUTION;
        
        $this->save();
        return true;
    }

    public function executePurchaseByPurchaseDepartment(User $purchaseDept, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_PURCHASE_EXECUTION) {
            return false;
        }

        // Auto-stock items after owner approval
        $this->autoStockItems($purchaseDept);
        
        $this->workflow_status = self::WORKFLOW_PENDING_STOCK_KEEPER;
        $this->purchase_executed_by = $purchaseDept->id;
        $this->purchase_executed_at = now();
        $this->purchase_execution_notes = $notes;
        
        $this->save();
        return true;
    }

    public function ownerForceApprove(User $owner, string $notes = null): bool
    {
        $this->owner_id = $owner->id;
        $this->owner_approved_at = now();
        $this->owner_notes = $notes;
        $this->workflow_status = self::WORKFLOW_PENDING_PURCHASE_EXECUTION;
        
        $this->save();
        return true;
    }

    public function approveByStockKeeper(User $stockKeeper, string $notes = null): bool
    {
        if ($this->workflow_status !== self::WORKFLOW_PENDING_STOCK_KEEPER) {
            return false;
        }

        $this->stock_keeper_id = $stockKeeper->id;
        $this->stock_keeper_approved_at = now();
        $this->stock_keeper_notes = $notes;
        $this->workflow_status = self::WORKFLOW_APPROVED;
        $this->status = self::STATUS_APPROVED;
        
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
        $this->workflow_status = self::WORKFLOW_REJECTED;
        $this->status = self::STATUS_REJECTED;
        
        // Store rejection details based on current step
        switch ($currentStatus) {
            case self::WORKFLOW_PENDING_DEPARTMENT_HEAD:
                $this->department_head_id = $rejector->id;
                $this->department_head_approved_at = now();
                $this->department_head_notes = $reason;
                break;
            case self::WORKFLOW_PENDING_MANAGER:
                $this->manager_id = $rejector->id;
                $this->manager_approved_at = now();
                $this->manager_notes = $reason;
                break;
            case self::WORKFLOW_PENDING_PURCHASE_DEPARTMENT:
                $this->purchase_department_id = $rejector->id;
                $this->purchase_department_approved_at = now();
                $this->purchase_department_notes = $reason;
                break;
            case self::WORKFLOW_PENDING_STOCK_KEEPER:
                $this->stock_keeper_id = $rejector->id;
                $this->stock_keeper_approved_at = now();
                $this->stock_keeper_notes = $reason;
                break;
            case self::WORKFLOW_PENDING_OWNER:
                $this->owner_id = $rejector->id;
                $this->owner_approved_at = now();
                $this->owner_notes = $reason;
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
            'owner' => 'Owner Approval',
            'purchase_execution' => 'Purchase Execution',
            'stock_keeper' => 'Stock Keeper Approval',
            default => 'Complete'
        };
    }

    public function isWorkflowComplete(): bool
    {
        return in_array($this->workflow_status, [self::WORKFLOW_APPROVED, self::WORKFLOW_REJECTED, 'fulfilled', 'cancelled']);
    }

    public function canUserApprove(User $user): bool
    {
        $nextApprover = $this->getNextApprover();
        
        return match($nextApprover) {
            'department_head' => $user->hasRole('department_head') || $user->hasRole('administrator'),
            'manager' => $user->hasRole('manager') || $user->hasRole('administrator'),
            'purchase_department' => $user->hasRole('purchase_department') || $user->hasRole('administrator'),
            'owner' => $user->hasRole('owner') || $user->hasRole('administrator'),
            'stock_keeper' => $user->hasRole('stock_keeper') || $user->hasRole('administrator'),
            default => false
        };
    }

    // Utility methods
    public function getWorkflowProgress(): array
    {
        $steps = $this->approval_chain ?? [];
        $progress = [];
        
        foreach ($steps as $index => $step) {
            $progress[] = [
                'step' => $step,
                'name' => $this->getStepDisplayName($step),
                'completed' => $index < $this->current_approval_step,
                'current' => $index === $this->current_approval_step,
                'pending' => $index > $this->current_approval_step
            ];
        }
        
        return $progress;
    }

    private function getStepDisplayName(string $step): string
    {
        return match($step) {
            'department_head' => 'Department Head',
            'manager' => 'Manager',
            'purchase_department' => 'Purchase Department',
            'owner' => 'Owner',
            'purchase_execution' => 'Purchase Execution',
            'stock_keeper' => 'Stock Keeper',
            default => ucwords(str_replace('_', ' ', $step))
        };
    }

    public function getTotalItemsCount(): int
    {
        return $this->items()->count();
    }

    public function getTotalRequestedQuantity(): int
    {
        return $this->items()->sum('quantity_requested');
    }

    public function getTotalFulfilledQuantity(): int
    {
        return $this->items()->sum('quantity_fulfilled');
    }

    public function getFulfillmentPercentage(): float
    {
        $requested = $this->getTotalRequestedQuantity();
        $fulfilled = $this->getTotalFulfilledQuantity();
        
        if ($requested === 0) {
            return 0;
        }
        
        return ($fulfilled / $requested) * 100;
    }

    public function hasOverdueItems(): bool
    {
        return $this->needed_by && now()->gt($this->needed_by) && !$this->isFulfilled();
    }

    public function getWorkflowDuration(): ?int
    {
        if (!$this->department_head_approved_at) {
            return null;
        }
        
        $endDate = $this->stock_keeper_approved_at ?? now();
        return $this->department_head_approved_at->diffInDays($endDate);
    }
}