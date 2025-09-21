@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-building"></i> Purchase Department - Stock Management
                </h1>
                <div>
                    <a href="{{ route('purchase-department.bulk-add-stock-form') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Bulk Add Stock
                    </a>
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

            {{-- Statistics Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['total_items'] }}</h4>
                                    <p class="mb-0">Total Items</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-box-seam fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['low_stock_items'] }}</h4>
                                    <p class="mb-0">Low Stock</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-exclamation-triangle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['out_of_stock_items'] }}</h4>
                                    <p class="mb-0">Out of Stock</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-x-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">${{ number_format($statistics['total_value'], 2) }}</h4>
                                    <p class="mb-0">Total Value</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-currency-dollar fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            @if(isset($chartData))
            <script>
                window.purchaseChartData = @json($chartData);
            </script>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Stock Trends</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyStockTrendsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-tags"></i> Top Categories by Value</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoriesValueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Stock Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="stockStatusChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-activity"></i> Recent Stock Additions</h5>
                        </div>
                        <div class="card-body">
                            @if($chartData['recent_additions']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chartData['recent_additions'] as $addition)
                                                <tr>
                                                    <td>{{ $addition->item->name }}</td>
                                                    <td><span class="badge bg-success">+{{ $addition->quantity }}</span></td>
                                                    <td>{{ $addition->transaction_date->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No recent stock additions</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <script src="{{ asset('js/purchase-charts.js') }}"></script>

            {{-- Search and Filter --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase-department.search') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Items</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search by name, SKU, or category">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach(['Office Supplies', 'Computer Accessories', 'Furniture', 'Electronics', 'Stationery'] as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="">All Items</option>
                                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Inventory Items</h5>
                </div>
                <div class="card-body">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Unit Price</th>
                                        <th>Supplier</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->name }}</strong>
                                                    <br><small class="text-muted">SKU: {{ $item->sku }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $item->category }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                                    {{ $item->quantity_on_hand }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->unit_price)
                                                    ${{ number_format($item->unit_price, 2) }}
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->supplier)
                                                    {{ $item->supplier }}
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->isOutOfStock())
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($item->isLowStock())
                                                    <span class="badge bg-warning">Low Stock</span>
                                                @else
                                                    <span class="badge bg-success">In Stock</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('purchase-department.add-stock-form', $item) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-plus-circle"></i> Add Stock
                                                    </a>
                                                    <a href="{{ route('purchase-department.stock-history', $item) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-clock-history"></i> History
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            <nav aria-label="Items pagination">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($items->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">« Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $items->previousPageUrl() }}" rel="prev">« Previous</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                                        @if ($page == $items->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($items->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $items->nextPageUrl() }}" rel="next">Next »</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Next »</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <h5 class="text-muted mt-3">No items found</h5>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
