<!-- Employee Dashboard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Pending Requests</h6>
                        <h3 class="mb-0">{{ $pending_requests_count }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Approved Requests</h6>
                        <h3 class="mb-0">{{ $approved_requests_count }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Quick Actions</h6>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> New Request
                    </a>
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i> Browse Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> My Recent Requests</h5>
            </div>
            <div class="card-body">
                @if($my_requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($my_requests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif($request->status === 'fulfilled')
                                            <span class="badge bg-primary">Fulfilled</span>
                                        @endif
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
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('purchase-requests.index') }}" class="btn btn-sm btn-link">View All →</a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No requests yet. <a href="{{ route('purchase-requests.create') }}">Create your first request</a>.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-box"></i> Recently Updated Items</h6>
            </div>
            <div class="card-body">
                @if($recent_items->count() > 0)
                    @foreach($recent_items->take(5) as $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->sku }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ $item->quantity_on_hand }}</div>
                            <small class="text-muted">{{ $item->unit }}</small>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-end mt-2">
                        <a href="{{ route('items.index') }}" class="btn btn-sm btn-link">View All →</a>
                    </div>
                @else
                    <p class="text-muted">No items available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
