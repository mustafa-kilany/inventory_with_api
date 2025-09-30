@extends('layouts.app')

@section('content')
<div class="container">
    {{-- GLOBAL FLASHES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Optional explicit "Sent to Owner" flash --}}
    @if(session('sent_to_owner'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            Purchase request <strong>{{ session('sent_to_owner') }}</strong> has been sent to <strong>Doc. Zuhair</strong> for approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="bi bi-eye"></i> Purchase Request Details
                    <small class="text-muted">#{{ $purchaseRequest->request_number }}</small>
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Requests
                    </a>
                    @if($purchaseRequest->status === 'pending' && $purchaseRequest->requested_by === auth()->id())
                        <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Request
                        </a>
                    @endif

                    {{-- PD primary action removed from header per request --}}
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS STRIPE / CONTEXT BANNERS --}}
    <div class="row">
        <div class="col-12">
            @php
                $statusColors = [
                    'pending'   => 'warning',
                    'approved'  => 'info',
                    'rejected'  => 'danger',
                    'fulfilled' => 'success',
                    'cancelled' => 'secondary'
                ];
                $statusIcons = [
                    'pending'   => 'clock',
                    'approved'  => 'check-circle',
                    'rejected'  => 'x-circle',
                    'fulfilled' => 'check-circle-fill',
                    'cancelled' => 'slash-circle'
                ];
                $priorityColors = [
                    'low'    => 'secondary',
                    'medium' => 'primary',
                    'high'   => 'warning',
                    'urgent' => 'danger'
                ];
            @endphp

            @if($purchaseRequest->workflow_status === 'pending_owner')
                <div class="alert alert-primary d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-person-badge"></i>
                        This request is <strong>awaiting Owner approval</strong> by <strong>Doc. Zuhair</strong>.
                    </div>
                    <span class="badge bg-primary">Pending Owner</span>
                </div>
            @elseif($purchaseRequest->workflow_status === 'pending_purchase_department')
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-building"></i>
                        <strong>Purchase Department</strong> action required: submit final price and send to Owner.
                    </div>
                    <span class="badge bg-info">PD Step</span>
                </div>
            @elseif($purchaseRequest->workflow_status === 'pending_department_head')
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-person-check"></i>
                        <strong>Department Head</strong> review is required.
                    </div>
                    <span class="badge bg-warning text-dark">Dept Head Step</span>
                </div>
            @elseif($purchaseRequest->workflow_status === 'pending_manager')
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-briefcase"></i>
                        <strong>Manager</strong> review is required.
                    </div>
                    <span class="badge bg-warning text-dark">Manager Step</span>
                </div>
            @elseif($purchaseRequest->workflow_status === 'pending_stock_keeper')
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-box-seam"></i>
                        <strong>Stock Keeper</strong> step: issue items when available.
                    </div>
                    <span class="badge bg-success">Stock Keeper</span>
                </div>
            @endif
        </div>
    </div>

    {{-- MAIN LAYOUT --}}
    <div class="row">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            {{-- REQUEST INFO CARD --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Request Information</h5>
                    <span class="badge bg-{{ $statusColors[$purchaseRequest->status] ?? 'secondary' }} fs-6">
                        <i class="bi bi-{{ $statusIcons[$purchaseRequest->status] ?? 'question-circle' }}"></i>
                        {{ ucfirst($purchaseRequest->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Request Number</label>
                                <div>{{ $purchaseRequest->request_number }}</div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Requested By</label>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <div>
                                        <div>{{ $purchaseRequest->requestedBy->name }}</div>
                                        <small class="text-muted">
                                            {{ $purchaseRequest->requestedBy->employee_id }} - {{ $purchaseRequest->requestedBy->department }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Priority</label>
                                <div>
                                    <span class="badge bg-{{ $priorityColors[$purchaseRequest->priority] ?? 'secondary' }}">
                                        {{ ucfirst($purchaseRequest->priority) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Workflow Status</label>
                                <div class="text-capitalize">{{ $purchaseRequest->workflow_status ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Created Date</label>
                                <div>{{ $purchaseRequest->created_at->format('M j, Y \a\t g:i A') }}</div>
                                <small class="text-muted">{{ $purchaseRequest->created_at->diffForHumans() }}</small>
                            </div>
                            @if($purchaseRequest->needed_by)
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-0">Needed By</label>
                                    <div>
                                        {{ $purchaseRequest->needed_by->format('M j, Y') }}
                                        @if($purchaseRequest->needed_by->isPast())
                                            <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue</small>
                                        @elseif($purchaseRequest->needed_by->diffInDays() <= 3)
                                            <br><small class="text-warning">
                                                <i class="bi bi-clock"></i>
                                                Due in {{ $purchaseRequest->needed_by->diffInDays() }} {{ \Illuminate\Support\Str::plural('day', $purchaseRequest->needed_by->diffInDays()) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Total Items</label>
                                <div>{{ $purchaseRequest->items->count() }} {{ \Illuminate\Support\Str::plural('item', $purchaseRequest->items->count()) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">Justification</label>
                        <div class="p-3 bg-light rounded">{{ $purchaseRequest->justification }}</div>
                    </div>
                </div>
            </div>

            {{-- ITEMS CARD --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Requested Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>SKU</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Qty Requested</th>
                                    @if($purchaseRequest->status !== 'pending')
                                        <th class="text-end">Qty Approved</th>
                                    @endif
                                    @if($purchaseRequest->status === 'fulfilled')
                                        <th class="text-end">Qty Fulfilled</th>
                                    @endif
                                    <th class="text-end">Line Total</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseRequest->items as $pri)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($pri->item->image_url)
                                                    <img src="{{ $pri->item->image_url }}" alt="{{ $pri->item->name }}"
                                                         class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $pri->item->name }}</div>
                                                    <small class="text-muted">{{ $pri->item->category }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code class="text-primary">{{ $pri->item->sku }}</code></td>
                                        <td class="text-end">${{ number_format($pri->unit_price ?? 0, 2) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ number_format($pri->quantity_requested) }}</span>
                                        </td>
                                        @if($purchaseRequest->status !== 'pending')
                                            <td class="text-end">
                                                @if($pri->quantity_approved !== null)
                                                    <span class="badge bg-info">{{ number_format($pri->quantity_approved) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if($purchaseRequest->status === 'fulfilled')
                                            <td class="text-end">
                                                <span class="badge bg-success">{{ number_format($pri->quantity_fulfilled) }}</span>
                                            </td>
                                        @endif
                                        <td class="text-end">
                                            <span class="fw-bold">
                                                ${{ number_format(($pri->unit_price ?? 0) * $pri->quantity_requested, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($pri->notes)
                                                <small class="text-muted">{{ $pri->notes }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    @php
                                        $colspanBase = 4;
                                        if ($purchaseRequest->status !== 'pending') { $colspanBase++; }
                                        if ($purchaseRequest->status === 'fulfilled') { $colspanBase++; }
                                    @endphp
                                    <th colspan="{{ $colspanBase }}" class="text-end">Estimated Total</th>
                                    <th class="text-end">${{ number_format($purchaseRequest->estimated_total ?? 0, 2) }}</th>
                                    <th></th>
                                </tr>
                                @if(!is_null($purchaseRequest->actual_total))
                                    <tr>
                                        <th colspan="{{ $colspanBase }}" class="text-end">Final / Quoted Total</th>
                                        <th class="text-end">${{ number_format($purchaseRequest->actual_total, 2) }}</th>
                                        <th></th>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- WORKFLOW ACTIONS (generic approve/reject buttons for current step) --}}
            @php
                $canApprove = false;
                $approveRoute = null;
                $stepLabel = null;

                switch ($purchaseRequest->workflow_status) {
                    case 'pending_department_head':
                        $canApprove = auth()->user()->canApproveAsDepartmentHead();
                        $approveRoute = route('purchase-requests.approve-department-head', $purchaseRequest);
                        $stepLabel = 'Department Head Approval';
                        break;
                    case 'pending_manager':
                        $canApprove = auth()->user()->canApproveAsManager();
                        $approveRoute = route('purchase-requests.approve-manager', $purchaseRequest);
                        $stepLabel = 'Manager Approval';
                        break;
                    case 'pending_purchase_department':
                        // PD has a dedicated card below; still keep generic buttons if you prefer
                        $canApprove = auth()->user()->canApproveAsPurchaseDepartment();
                        $approveRoute = route('purchase-requests.approve-purchase-department', $purchaseRequest);
                        $stepLabel = 'Purchase Department Approval';
                        break;
                    case 'pending_stock_keeper':
                        $canApprove = auth()->user()->canManageStock();
                        $approveRoute = route('purchase-requests.approve-stock-keeper', $purchaseRequest);
                        $stepLabel = 'Stock Keeper Approval';
                        break;
                }
            @endphp

            @if($canApprove && !$purchaseRequest->isWorkflowComplete())
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> {{ $stepLabel }}</h5>
                        <span class="badge bg-primary">Step {{ ($purchaseRequest->current_approval_step ?? 0) + 1 }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success w-100" onclick="approveRequestModal()">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger w-100" onclick="rejectRequestModal()">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- OUT OF STOCK HINT (Dept Head) --}}
            @if($purchaseRequest->workflow_type === 'out_of_stock' && $purchaseRequest->workflow_status === 'pending_department_head')
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Out-of-Stock Items</h5>
                        <span class="badge bg-warning text-dark">Requires Purchase Department</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i>
                            These items are currently out of stock. After you approve, the request goes to the Purchase Department to procure and then to the Owner.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Current Stock</th>
                                        <th>Requested Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequest->items as $pri)
                                        <tr>
                                            <td>
                                                <strong>{{ $pri->item->name }}</strong>
                                                <br><small class="text-muted">SKU: {{ $pri->item->sku }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $pri->item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                                    {{ $pri->item->quantity_on_hand }}
                                                </span>
                                            </td>
                                            <td>{{ $pri->quantity_requested }}</td>
                                            <td><span class="badge bg-warning text-dark">Out of Stock</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PURCHASE DEPT detailed form removed; single action shown in header instead --}}

            {{-- OWNER PANEL (Doc. Zuhair) --}}
            @if(auth()->user()->hasRole('owner') && $purchaseRequest->workflow_status === 'pending_owner')
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-check"></i> Owner Approval</h5>
                        <span class="badge bg-light text-primary">Doc. Zuhair</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Final Price Submitted by Purchase Department</div>
                                <div class="fs-4 mt-1">
                                    ${{ number_format($purchaseRequest->actual_total ?? $purchaseRequest->estimated_total ?? 0, 2) }}
                                </div>
                            </div>
                            <div class="text-muted">
                                PR #: <strong>{{ $purchaseRequest->request_number }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-text">
                                Please review the requested items, justification, and final amount. Your approval moves this request to the Stock Keeper to issue items (if/when available).
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <form action="{{ route('purchase-requests.approve-owner', $purchaseRequest) }}" method="POST" class="border rounded p-3">
                                    @csrf
                                    <label class="form-label">Approval Notes (optional)</label>
                                    <textarea name="approval_notes" class="form-control mb-3" rows="2" placeholder="Notes to record…"></textarea>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check2-circle"></i> Approve as Owner
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('purchase-requests.reject-workflow', $purchaseRequest) }}" method="POST" class="border rounded p-3">
                                    @csrf
                                    <label class="form-label">Rejection Reason</label>
                                    <textarea name="rejection_reason" class="form-control mb-3" rows="2" required></textarea>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FULFILLMENT (Stock Keeper when status approved) --}}
            @if(auth()->user()->can('fulfill purchase requests') && $purchaseRequest->status === 'approved')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Fulfillment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('purchase-requests.fulfill', $purchaseRequest) }}">
                            @csrf
                            <div class="table-responsive mb-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-end">Approved Qty</th>
                                            <th class="text-end">Available Stock</th>
                                            <th class="text-end">Fulfill Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseRequest->items as $pri)
                                            @php
                                                $approvedQty = $pri->quantity_approved ?? $pri->quantity_requested;
                                                $maxFulfill = min($approvedQty, $pri->item->quantity_on_hand);
                                            @endphp
                                            <tr>
                                                <td>{{ $pri->item->name }}</td>
                                                <td class="text-end">{{ $approvedQty }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-{{ $pri->item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                                        {{ $pri->item->quantity_on_hand }}
                                                    </span>
                                                </td>
                                                <td class="text-end" style="max-width:150px">
                                                    <input
                                                        type="number"
                                                        name="items[{{ $pri->item_id }}][quantity_fulfilled]"
                                                        class="form-control text-end"
                                                        min="0"
                                                        max="{{ $maxFulfill }}"
                                                        value="{{ $maxFulfill }}"
                                                    >
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Fulfill Request
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- TIMELINE --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        {{-- Created --}}
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Created</h6>
                                <p class="mb-1">By {{ $purchaseRequest->requestedBy->name }}</p>
                                <small class="text-muted">{{ $purchaseRequest->created_at->format('M j, Y \a\t g:i A') }}</small>
                            </div>
                        </div>

                        {{-- Dept Head --}}
                        @if($purchaseRequest->department_head_approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Department Head</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->departmentHead)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->department_head_approved_at?->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->department_head_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->department_head_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Manager --}}
                        @if($purchaseRequest->manager_approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Manager</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->manager)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->manager_approved_at?->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->manager_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->manager_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Purchase Department --}}
                        @if($purchaseRequest->purchase_department_approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Purchase Department</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->purchaseDepartment)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->purchase_department_approved_at?->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->purchase_department_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->purchase_department_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Owner --}}
                        @if($purchaseRequest->owner_approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Owner Approval</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->owner)->name ?? 'Doc. Zuhair' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->owner_approved_at?->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->owner_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->owner_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Approved/Rejected (legacy overall) --}}
                        @if($purchaseRequest->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $purchaseRequest->status === 'rejected' ? 'danger' : 'info' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Request {{ ucfirst($purchaseRequest->status) }}</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->approvedBy)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->approved_at->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->approval_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->approval_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Stock Keeper --}}
                        @if($purchaseRequest->stock_keeper_approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Stock Keeper</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->stockKeeper)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->stock_keeper_approved_at?->format('M j, Y \a\t g:i A') }}</small>
                                    @if($purchaseRequest->stock_keeper_notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $purchaseRequest->stock_keeper_notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Fulfilled --}}
                        @if($purchaseRequest->fulfilled_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Request Fulfilled</h6>
                                    <p class="mb-1">By {{ optional($purchaseRequest->fulfilledBy)->name ?? '—' }}</p>
                                    <small class="text-muted">{{ $purchaseRequest->fulfilled_at->format('M j, Y \a\t g:i A') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- FINANCIAL SUMMARY --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Estimated Total</span>
                        <span class="fw-bold">${{ number_format($purchaseRequest->estimated_total ?? 0, 2) }}</span>
                    </div>
                    @if(!is_null($purchaseRequest->actual_total))
                        <div class="d-flex justify-content-between mb-2">
                            <span>Final / Quoted Total</span>
                            <span class="fw-bold text-success">${{ number_format($purchaseRequest->actual_total, 2) }}</span>
                        </div>
                        <hr>
                        @php
                            $difference = ($purchaseRequest->actual_total ?? 0) - ($purchaseRequest->estimated_total ?? 0);
                        @endphp
                        <div class="d-flex justify-content-between">
                            <span>Difference</span>
                            <span class="fw-bold {{ $difference > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $difference > 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- APPROVAL TRAIL / META --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-flag"></i> Approval Trail</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-semibold">Current Step</div>
                                <div class="text-capitalize">{{ $purchaseRequest->workflow_status ?? '—' }}</div>
                            </div>
                            <span class="badge bg-secondary rounded-pill">Step {{ ($purchaseRequest->current_approval_step ?? 0) + 1 }}</span>
                        </li>
                        <li class="list-group-item">
                            <div class="fw-semibold mb-1">Approval Chain</div>
                            @php $chain = $purchaseRequest->approval_chain ?? []; @endphp
                            @if(!empty($chain))
                                <ol class="mb-0">
                                  @foreach($chain as $i => $step)
    <li class="{{ $i < ($purchaseRequest->current_approval_step ?? 0) ? 'text-decoration-line-through text-muted' : '' }}">
        {{ ucfirst($step) }}
    </li>
@endforeach
                                </ol>
                            @else
                                <span class="text-muted">No chain defined</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- APPROVE / REJECT MODALS --}}
@php
    $approveRouteForStep = null;
    switch ($purchaseRequest->workflow_status) {
        case 'pending_department_head':
            $approveRouteForStep = route('purchase-requests.approve-department-head', $purchaseRequest);
            break;
        case 'pending_manager':
            $approveRouteForStep = route('purchase-requests.approve-manager', $purchaseRequest);
            break;
        case 'pending_purchase_department':
            $approveRouteForStep = route('purchase-requests.approve-purchase-department', $purchaseRequest);
            break;
        case 'pending_stock_keeper':
            $approveRouteForStep = route('purchase-requests.approve-stock-keeper', $purchaseRequest);
            break;
        case 'pending_owner':
            $approveRouteForStep = route('purchase-requests.approve-owner', $purchaseRequest);
            break;
    }
@endphp

@if($approveRouteForStep && !$purchaseRequest->isWorkflowComplete())
    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ $approveRouteForStep }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Purchase Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="approval_notes" class="form-label">Approval Notes (optional)</label>
                        <textarea name="approval_notes" id="approval_notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('purchase-requests.reject', $purchaseRequest) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Purchase Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="reject_notes" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="approval_notes" id="reject_notes" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- STYLES + JS --}}
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0; bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item { position: relative; margin-bottom: 20px; }
.timeline-marker {
    position: absolute;
    left: -25px; top: 5px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}
.timeline-content {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid #dee2e6;
}
</style>

<script>
function approveRequestModal(){ new bootstrap.Modal(document.getElementById('approveModal')).show(); }
function rejectRequestModal(){ new bootstrap.Modal(document.getElementById('rejectModal')).show(); }
</script>
@endsection
