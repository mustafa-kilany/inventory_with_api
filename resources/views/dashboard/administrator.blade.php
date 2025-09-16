<!-- Administrator Dashboard -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Total Items</h6>
                        <h3 class="mb-0">{{ $stats['total_items'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-boxes text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Low Stock</h6>
                        <h3 class="mb-0">{{ $stats['low_stock_items'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Requests</h6>
                        <h3 class="mb-0">{{ $stats['total_requests'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-plus text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Users</h6>
                        <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Quick Actions</h6>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="/admin" target="_blank" class="btn btn-primary">
                        <i class="bi bi-gear"></i> Admin Panel
                    </a>
                    <a href="{{ route('items.index') }}" class="btn btn-success">
                        <i class="bi bi-box"></i> Manage Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Status Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">{{ $stats['pending_requests'] }}</h5>
                <p class="card-text">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">{{ $stats['approved_requests'] }}</h5>
                <p class="card-text">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-primary">{{ $stats['fulfilled_requests'] }}</h5>
                <p class="card-text">Fulfilled</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">{{ number_format(($stats['fulfilled_requests'] / max($stats['total_requests'], 1)) * 100, 1) }}%</h5>
                <p class="card-text">Fulfillment Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
@if($urgent_requests->count() > 0 || $overdue_requests->count() > 0)
<div class="row mb-4">
    @if($urgent_requests->count() > 0)
    <div class="col-md-6">
        <div class="alert alert-warning">
            <h6><i class="bi bi-lightning"></i> Urgent Requests ({{ $urgent_requests->count() }})</h6>
            @foreach($urgent_requests->take(3) as $request)
            <div class="d-flex justify-content-between align-items-center py-1">
                <span>{{ $request->request_number }} - {{ $request->requestedBy->name }}</span>
                <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-warning">Review</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    @if($overdue_requests->count() > 0)
    <div class="col-md-6">
        <div class="alert alert-danger">
            <h6><i class="bi bi-clock-history"></i> Overdue Requests ({{ $overdue_requests->count() }})</h6>
            @foreach($overdue_requests->take(3) as $request)
            <div class="d-flex justify-content-between align-items-center py-1">
                <span>{{ $request->request_number }} - Due: {{ $request->needed_by->format('M d') }}</span>
                <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-danger">Review</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @if($recent_requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Requester</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_requests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>{{ $request->requestedBy->name }}</td>
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
                                    <td>{{ $request->created_at->format('M d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No recent activity.</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Items Requiring Attention</h6>
            </div>
            <div class="card-body">
                @if($low_stock_items->count() > 0)
                    @foreach($low_stock_items as $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->sku }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning">{{ $item->quantity_on_hand }}/{{ $item->reorder_level }}</span><br>
                            <small class="text-muted">{{ $item->unit }}</small>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-end mt-2">
                        <a href="{{ route('items.index') }}?filter=low_stock" class="btn btn-sm btn-link">View All →</a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">All items are adequately stocked.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-activity"></i> Recent Stock Movements</h6>
            </div>
            <div class="card-body">
                @if($recent_transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Performed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->item->name }}</td>
                                    <td>
                                        @if($transaction->type === 'in')
                                            <span class="badge bg-success">Stock In</span>
                                        @elseif($transaction->type === 'out')
                                            <span class="badge bg-danger">Stock Out</span>
                                        @else
                                            <span class="badge bg-info">Adjustment</span>
                                        @endif
                                    </td>
                                    <td>{{ abs($transaction->quantity) }}</td>
                                    <td>{{ $transaction->performedBy->name }}</td>
                                    <td>{{ $transaction->transaction_date->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No recent stock movements.</p>
                @endif
            </div>
        </div>
    </div>
</div>
