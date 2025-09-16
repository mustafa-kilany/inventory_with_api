@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-box"></i> Inventory Items</h1>
                @can('create items')
                <a href="{{ route('items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Item
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('items.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name, SKU, or description">
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="Computer Accessories" {{ request('category') === 'Computer Accessories' ? 'selected' : '' }}>Computer Accessories</option>
                                <option value="Office Supplies" {{ request('category') === 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Stock Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Items</option>
                                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($items && $items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Stock Level</th>
                                        <th>Unit Price</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                    <tr class="{{ $item->isOutOfStock() ? 'table-danger' : ($item->isLowStock() ? 'table-warning' : '') }}">
                                        <td>
                                            <div class="fw-bold">{{ $item->name }}</div>
                                            <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                        </td>
                                        <td><code>{{ $item->sku }}</code></td>
                                        <td>{{ $item->category }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $item->quantity_on_hand }} {{ $item->unit }}</span>
                                                @if($item->isOutOfStock())
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($item->isLowStock())
                                                    <span class="badge bg-warning">Low Stock</span>
                                                @else
                                                    <span class="badge bg-success">In Stock</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">Reorder at: {{ $item->reorder_level }}</small>
                                        </td>
                                        <td>
                                            @if($item->unit_price)
                                                ${{ number_format($item->unit_price, 2) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->location ?: '-' }}</td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('edit items')
                                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                                @can('create stock transactions')
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="showStockModal({{ $item->id }}, '{{ $item->name }}')">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($items->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Items pagination">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($items->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $items->appends(request()->query())->previousPageUrl() }}" rel="prev">Previous</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($items->appends(request()->query())->getUrlRange(1, $items->lastPage()) as $page => $url)
                                        @if ($page == $items->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($items->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $items->appends(request()->query())->nextPageUrl() }}" rel="next">Next</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No items found</h5>
                            <p class="text-muted">Try adjusting your search criteria or add a new item.</p>
                            @can('create items')
                            <a href="{{ route('items.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First Item
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stock Update Modal -->
@can('create stock transactions')
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm" method="POST" action="{{ route('stock-transactions.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="item_id" name="item_id">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" id="item_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="in">Stock In (Receiving)</option>
                            <option value="out">Stock Out (Issue)</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function showStockModal(itemId, itemName) {
    document.getElementById('item_id').value = itemId;
    document.getElementById('item_name').value = itemName;
    const modal = new bootstrap.Modal(document.getElementById('stockModal'));
    modal.show();
}
</script>
@endpush
