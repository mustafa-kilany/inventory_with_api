<!-- Stock Keeper Dashboard -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Total Items</h6>
                        <h3 class="mb-0">{{ $total_items }}</h3>
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
                        <h3 class="mb-0">{{ $low_stock_count }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted">Out of Stock</h6>
                        <h3 class="mb-0">{{ $out_of_stock_count }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
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
                    <a href="{{ route('items.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </a>
                    <a href="{{ route('stock-transactions.create') }}" class="btn btn-success">
                        <i class="bi bi-arrow-up-circle"></i> Stock In
                    </a>
                    <a href="{{ route('purchase-requests.index') }}?status=approved" class="btn btn-info">
                        <i class="bi bi-check-square"></i> Fulfill Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section for Stock Keeper --}}
@if(isset($chart_data))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Stock Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="stockKeeperStockStatusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Request Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="stockKeeperMonthlyTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@if($out_of_stock_items->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger">
            <h6><i class="bi bi-exclamation-triangle"></i> Out of Stock Items</h6>
            <div class="row">
                @foreach($out_of_stock_items->take(6) as $item)
                <div class="col-md-2">
                    <div class="card border-danger mb-2">
                        <div class="card-body py-2">
                            <strong>{{ $item->name }}</strong><br>
                            <small class="text-muted">{{ $item->sku }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@if($low_stock_items->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Low Stock Items</h6>
            <div class="row">
                @foreach($low_stock_items->take(6) as $item)
                <div class="col-md-2">
                    <div class="card border-warning mb-2">
                        <div class="card-body py-2">
                            <strong>{{ $item->name }}</strong><br>
                            <small class="text-muted">{{ $item->quantity_on_hand }}/{{ $item->reorder_level }} {{ $item->unit }}</small>
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
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cart-check"></i> Approved Requests to Fulfill</h5>
            </div>
            <div class="card-body">
                @if($approved_requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Requester</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approved_requests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>{{ $request->requestedBy->name }}</td>
                                    <td>{{ $request->items->count() }}</td>
                                    <td>
                                        <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-box-arrow-down"></i> Fulfill
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('purchase-requests.index') }}?status=approved" class="btn btn-sm btn-link">View All →</a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No approved requests to fulfill.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-arrow-left-right"></i> Recent Stock Movements</h6>
            </div>
            <div class="card-body">
                @if($recent_transactions->count() > 0)
                    @foreach($recent_transactions as $transaction)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $transaction->item->name }}</div>
                            <small class="text-muted">
                                @if($transaction->type === 'in')
                                    <i class="bi bi-arrow-up text-success"></i> Stock In
                                @elseif($transaction->type === 'out')
                                    <i class="bi bi-arrow-down text-danger"></i> Stock Out
                                @else
                                    <i class="bi bi-pencil text-info"></i> Adjustment
                                @endif
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ abs($transaction->quantity) }}</div>
                            <small class="text-muted">{{ $transaction->transaction_date->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                    <div class="text-end mt-2">
                        <a href="{{ route('stock-transactions.index') }}" class="btn btn-sm btn-link">View All →</a>
                    </div>
                @else
                    <p class="text-muted">No recent transactions.</p>
                @endif
            </div>
        </div>
    </div>
</div>
