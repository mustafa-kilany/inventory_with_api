@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-cart-plus"></i> Purchase Requests</h1>
                <div>
                    @if(auth()->user()->hasRole('employee') || auth()->user()->hasRole('administrator'))
                        <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Request
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status and Priority Filters --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase-requests.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
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

            {{-- Purchase Requests Table --}}
            @if($purchaseRequests->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request #</th>
                                        <th>Requested By</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Items</th>
                                        <th>Estimated Total</th>
                                        <th>Needed By</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequests as $request)
                                        <tr>
                                            <td>
                                                <a href="{{ route('purchase-requests.show', $request) }}" class="fw-bold text-decoration-none">
                                                    {{ $request->request_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-circle me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold">{{ $request->requestedBy->name }}</div>
                                                        <small class="text-muted">{{ $request->requestedBy->employee_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
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
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$request->status] }}">
                                                    <i class="bi bi-{{ $statusIcons[$request->status] }}"></i>
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $priorityColors = [
                                                        'low' => 'secondary',
                                                        'medium' => 'primary',
                                                        'high' => 'warning',
                                                        'urgent' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $priorityColors[$request->priority] }}">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $request->items->count() }} {{ Str::plural('item', $request->items->count()) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->estimated_total)
                                                    ${{ number_format($request->estimated_total, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->needed_by)
                                                    {{ $request->needed_by->format('M j, Y') }}
                                                    @if($request->needed_by->isPast())
                                                        <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue</small>
                                                    @elseif($request->needed_by->diffInDays() <= 3)
                                                        <br><small class="text-warning"><i class="bi bi-clock"></i> Due soon</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No deadline</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $request->created_at->format('M j, Y') }}
                                                <br><small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-outline-primary" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($request->status === 'pending' && $request->requested_by === auth()->id())
                                                        <a href="{{ route('purchase-requests.edit', $request) }}" class="btn btn-outline-secondary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif

                                                    @if(auth()->user()->can('approve purchase requests') && $request->status === 'pending')
                                                        <button type="button" class="btn btn-outline-success" title="Approve" 
                                                                onclick="approveRequest({{ $request->id }})">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" title="Reject" 
                                                                onclick="rejectRequest({{ $request->id }})">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    @endif

                                                    @if(auth()->user()->can('fulfill purchase requests') && $request->status === 'approved')
                                                        <button type="button" class="btn btn-outline-info" title="Fulfill" 
                                                                onclick="fulfillRequest({{ $request->id }})">
                                                            <i class="bi bi-box-seam"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Purchase requests pagination">
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($purchaseRequests->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $purchaseRequests->withQueryString()->previousPageUrl() }}" rel="prev">Previous</a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($purchaseRequests->withQueryString()->getUrlRange(1, $purchaseRequests->lastPage()) as $page => $url)
                                @if ($page == $purchaseRequests->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($purchaseRequests->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $purchaseRequests->withQueryString()->nextPageUrl() }}" rel="next">Next</a>
                                </li>
                            @else
                                <li class="page-item disabled"><span class="page-link">Next</span></li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cart-x display-1 text-muted"></i>
                        <h3 class="mt-3">No Purchase Requests Found</h3>
                        <p class="text-muted">
                            @if(auth()->user()->hasRole('employee'))
                                You haven't created any purchase requests yet.
                            @else
                                No purchase requests match your current filters.
                            @endif
                        </p>
                        @if(auth()->user()->hasRole('employee') || auth()->user()->hasRole('administrator'))
                            <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create Your First Request
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Action Modals --}}
@if(auth()->user()->can('approve purchase requests'))
    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
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
                <form id="rejectForm" method="POST">
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

<script>
function approveRequest(requestId) {
    const form = document.getElementById('approveForm');
    form.action = `/purchase-requests/${requestId}/approve`;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectRequest(requestId) {
    const form = document.getElementById('rejectForm');
    form.action = `/purchase-requests/${requestId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function fulfillRequest(requestId) {
    window.location.href = `/purchase-requests/${requestId}`;
}
</script>
@endsection
