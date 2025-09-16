<!-- Approver Dashboard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-stat warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Pending Approvals</h6>
                        <h3 class="mb-0">{{ $pending_requests_count }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Urgent Requests</h6>
                        <h3 class="mb-0">{{ $urgent_requests->count() }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-lightning text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Overdue Requests</h6>
                        <h3 class="mb-0">{{ $overdue_requests->count() }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock-history text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('purchase-requests.index') }}?status=pending" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-square"></i> Review Pending
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($urgent_requests->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Urgent Requests Requiring Attention</h6>
            <div class="row">
                @foreach($urgent_requests->take(3) as $request)
                <div class="col-md-4">
                    <div class="card border-warning mb-2">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $request->request_number }}</strong><br>
                                    <small>{{ $request->requestedBy->name }}</small>
                                </div>
                                <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-warning">Review</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Pending Requests</h5>
            </div>
            <div class="card-body">
                @if($pending_requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Requester</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pending_requests as $request)
                                <tr class="{{ $request->priority === 'urgent' ? 'table-warning' : '' }}">
                                    <td>{{ $request->request_number }}</td>
                                    <td>
                                        <div>{{ $request->requestedBy->name }}</div>
                                        <small class="text-muted">{{ $request->requestedBy->department }}</small>
                                    </td>
                                    <td>{{ $request->items->count() }} item(s)</td>
                                    <td>
                                        @if($request->priority === 'urgent')
                                            <span class="badge bg-danger">Urgent</span>
                                        @elseif($request->priority === 'high')
                                            <span class="badge bg-warning">High</span>
                                        @elseif($request->priority === 'medium')
                                            <span class="badge bg-info">Medium</span>
                                        @else
                                            <span class="badge bg-secondary">Low</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" onclick="approveRequest({{ $request->id }})">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectRequest({{ $request->id }})">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No pending requests to review.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Approvals</h6>
            </div>
            <div class="card-body">
                @if($recent_approvals->count() > 0)
                    @foreach($recent_approvals as $request)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $request->request_number }}</div>
                            <small class="text-muted">{{ $request->requestedBy->name }}</small>
                        </div>
                        <div class="text-end">
                            @if($request->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                            <br><small class="text-muted">{{ $request->approved_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">No recent approvals.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this request?')) {
        fetch(`/purchase-requests/${requestId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        }).then(() => {
            location.reload();
        });
    }
}

function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        fetch(`/purchase-requests/${requestId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason })
        }).then(() => {
            location.reload();
        });
    }
}
</script>
@endpush
