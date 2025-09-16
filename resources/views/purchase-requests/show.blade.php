@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-eye"></i> Purchase Request Details
                    <small class="text-muted">#{{ $purchaseRequest->request_number }}</small>
                </h1>
                <div>
                    <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Requests
                    </a>
                    @if($purchaseRequest->status === 'pending' && $purchaseRequest->requested_by === auth()->id())
                        <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Request
                        </a>
                    @endif
                </div>
            </div>

            {{-- Alerts --}}
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

            <div class="row">
                {{-- Left Column - Request Details --}}
                <div class="col-lg-8">
                    {{-- Request Information --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Request Information</h5>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'approved' => 'info',
                                    'rejected' => 'danger',
                                    'fulfilled' => 'success',
                                    'cancelled' => 'secondary'
                                ];
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'approved' => 'check-circle',
                                    'rejected' => 'x-circle',
                                    'fulfilled' => 'check-circle-fill',
                                    'cancelled' => 'ban'
                                ];
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'primary',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$purchaseRequest->status] }} fs-6">
                                <i class="bi bi-{{ $statusIcons[$purchaseRequest->status] }}"></i>
                                {{ ucfirst($purchaseRequest->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Request Number</label>
                                        <div>{{ $purchaseRequest->request_number }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Requested By</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2"></i>
                                            <div>
                                                <div>{{ $purchaseRequest->requestedBy->name }}</div>
                                                <small class="text-muted">{{ $purchaseRequest->requestedBy->employee_id }} - {{ $purchaseRequest->requestedBy->department }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Priority</label>
                                        <div>
                                            <span class="badge bg-{{ $priorityColors[$purchaseRequest->priority] }}">
                                                {{ ucfirst($purchaseRequest->priority) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created Date</label>
                                        <div>{{ $purchaseRequest->created_at->format('M j, Y \a\t g:i A') }}</div>
                                        <small class="text-muted">{{ $purchaseRequest->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($purchaseRequest->needed_by)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Needed By</label>
                                            <div>
                                                {{ $purchaseRequest->needed_by->format('M j, Y') }}
                                                @if($purchaseRequest->needed_by->isPast())
                                                    <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue</small>
                                                @elseif($purchaseRequest->needed_by->diffInDays() <= 3)
                                                    <br><small class="text-warning"><i class="bi bi-clock"></i> Due in {{ $purchaseRequest->needed_by->diffInDays() }} {{ Str::plural('day', $purchaseRequest->needed_by->diffInDays()) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Total Items</label>
                                        <div>{{ $purchaseRequest->items->count() }} {{ Str::plural('item', $purchaseRequest->items->count()) }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Justification</label>
                                <div class="p-3 bg-light rounded">{{ $purchaseRequest->justification }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Requested Items --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-box"></i> Requested Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>SKU</th>
                                            <th>Unit Price</th>
                                            <th>Qty Requested</th>
                                            @if($purchaseRequest->status !== 'pending')
                                                <th>Qty Approved</th>
                                            @endif
                                            @if($purchaseRequest->status === 'fulfilled')
                                                <th>Qty Fulfilled</th>
                                            @endif
                                            <th>Total</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseRequest->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($item->item->image_url)
                                                            <img src="{{ $item->item->image_url }}" alt="{{ $item->item->name }}" 
                                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $item->item->name }}</div>
                                                            <small class="text-muted">{{ $item->item->category }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-primary">{{ $item->item->sku }}</code>
                                                </td>
                                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ number_format($item->quantity_requested) }}</span>
                                                </td>
                                                @if($purchaseRequest->status !== 'pending')
                                                    <td>
                                                        @if($item->quantity_approved !== null)
                                                            <span class="badge bg-info">{{ number_format($item->quantity_approved) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                @if($purchaseRequest->status === 'fulfilled')
                                                    <td>
                                                        <span class="badge bg-success">{{ number_format($item->quantity_fulfilled) }}</span>
                                                    </td>
                                                @endif
                                                <td>
                                                    <span class="fw-bold">${{ number_format($item->total_price, 2) }}</span>
                                                </td>
                                                <td>
                                                    @if($item->notes)
                                                        <small class="text-muted">{{ $item->notes }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="@if($purchaseRequest->status === 'fulfilled') 6 @elseif($purchaseRequest->status !== 'pending') 5 @else 4 @endif">Total</th>
                                            <th>${{ number_format($purchaseRequest->estimated_total, 2) }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons based on Workflow --}}
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
                                        <button type="button" class="btn btn-success w-100" onclick="approveRequest()">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-danger w-100" onclick="rejectRequest()">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                                    <th>Approved Qty</th>
                                                    <th>Available Stock</th>
                                                    <th>Fulfill Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchaseRequest->items as $item)
                                                    <tr>
                                                        <td>{{ $item->item->name }}</td>
                                                        <td>{{ $item->quantity_approved ?? $item->quantity_requested }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $item->item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                                                {{ $item->item->quantity_on_hand }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $item->item_id }}][quantity_fulfilled]" 
                                                                   class="form-control" min="0" 
                                                                   max="{{ min($item->quantity_approved ?? $item->quantity_requested, $item->item->quantity_on_hand) }}"
                                                                   value="{{ min($item->quantity_approved ?? $item->quantity_requested, $item->item->quantity_on_hand) }}">
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

                {{-- Right Column - Timeline & Notes --}}
                <div class="col-lg-4">
                    {{-- Request Timeline --}}
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

                                {{-- Approved/Rejected --}}
                                @if($purchaseRequest->approved_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $purchaseRequest->status === 'approved' ? 'success' : 'danger' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Request {{ ucfirst($purchaseRequest->status) }}</h6>
                                            <p class="mb-1">By {{ $purchaseRequest->approvedBy->name }}</p>
                                            <small class="text-muted">{{ $purchaseRequest->approved_at->format('M j, Y \a\t g:i A') }}</small>
                                            @if($purchaseRequest->approval_notes)
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small>{{ $purchaseRequest->approval_notes }}</small>
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
                                            <p class="mb-1">By {{ $purchaseRequest->fulfilledBy->name }}</p>
                                            <small class="text-muted">{{ $purchaseRequest->fulfilled_at->format('M j, Y \a\t g:i A') }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calculator"></i> Financial Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Estimated Total:</span>
                                        <span class="fw-bold">${{ number_format($purchaseRequest->estimated_total, 2) }}</span>
                                    </div>
                                    @if($purchaseRequest->actual_total)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Actual Total:</span>
                                            <span class="fw-bold text-success">${{ number_format($purchaseRequest->actual_total, 2) }}</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span>Difference:</span>
                                            @php
                                                $difference = $purchaseRequest->actual_total - $purchaseRequest->estimated_total;
                                            @endphp
                                            <span class="fw-bold {{ $difference > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $difference > 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Action Modals --}}
@php
    // Determine approve route for the current workflow step
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
                        <div class="mb-3">
                            <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                            <textarea name="approval_notes" id="approval_notes" class="form-control" rows="3"
                                    placeholder="Add any notes about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Approve Request
                        </button>
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
                        <div class="mb-3">
                            <label for="reject_notes" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="approval_notes" id="reject_notes" class="form-control" rows="3" required
                                    placeholder="Please provide a reason for rejecting this request..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
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
function approveRequest() {
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectRequest() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endsection
