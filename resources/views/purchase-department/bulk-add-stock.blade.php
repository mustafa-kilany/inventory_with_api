@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-plus-circle"></i> Bulk Add Stock
                </h1>
                <a href="{{ route('purchase-department.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
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

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Add Stock to Multiple Items
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchase-department.bulk-add-stock') }}">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="search_items" class="form-label">Search Items</label>
                                <input type="text" class="form-control" id="search_items" 
                                       placeholder="Search by name, SKU, or category">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_category" class="form-label">Filter by Category</label>
                                <select class="form-select" id="filter_category">
                                    <option value="">All Categories</option>
                                    @foreach(['Office Supplies', 'Computer Accessories', 'Furniture', 'Electronics', 'Stationery'] as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_stock" class="form-label">Filter by Stock Status</label>
                                <select class="form-select" id="filter_stock">
                                    <option value="">All Items</option>
                                    <option value="low">Low Stock</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="items-table">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th width="25%">Item</th>
                                        <th width="15%">Category</th>
                                        <th width="15%">Current Stock</th>
                                        <th width="15%">Quantity to Add</th>
                                        <th width="15%">Unit Price</th>
                                        <th width="10%">Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr data-category="{{ $item->category }}" 
                                            data-stock-status="{{ $item->isOutOfStock() ? 'out' : ($item->isLowStock() ? 'low' : 'in_stock') }}"
                                            data-search-text="{{ strtolower($item->name . ' ' . $item->sku . ' ' . $item->category) }}">
                                            <td>
                                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                                       class="form-check-input item-checkbox">
                                            </td>
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
                                                <input type="number" name="stock_additions[{{ $item->id }}][quantity]" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       min="0" max="10000" value="0">
                                            </td>
                                            <td>
                                                <input type="number" name="stock_additions[{{ $item->id }}][unit_price]" 
                                                       class="form-control form-control-sm" 
                                                       min="0" max="999999.99" step="0.01" 
                                                       value="{{ $item->unit_price }}" 
                                                       placeholder="Unit price">
                                            </td>
                                            <td>
                                                <input type="text" name="stock_additions[{{ $item->id }}][supplier]" 
                                                       class="form-control form-control-sm" 
                                                       maxlength="255" value="{{ $item->supplier }}" 
                                                       placeholder="Supplier">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h6>Bulk Notes</h6>
                            <textarea name="bulk_notes" class="form-control" rows="3" 
                                      placeholder="Add notes that will apply to all selected items (e.g., Purchase order #12345, Delivery from ABC Supplier)"></textarea>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="bi bi-plus-circle"></i> Add Stock to Selected Items
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearAll()">
                                <i class="bi bi-x-circle"></i> Clear All
                            </button>
                            <a href="{{ route('purchase-department.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const submitBtn = document.getElementById('submit-btn');
    const searchInput = document.getElementById('search_items');
    const categoryFilter = document.getElementById('filter_category');
    const stockFilter = document.getElementById('filter_stock');
    const table = document.getElementById('items-table');
    const rows = table.querySelectorAll('tbody tr');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = this.checked;
            }
        });
        updateSubmitButton();
    });

    // Individual checkbox change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubmitButton);
    });

    // Quantity input change
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const checkbox = this.closest('tr').querySelector('.item-checkbox');
            if (this.value > 0) {
                checkbox.checked = true;
            }
            updateSubmitButton();
        });
    });

    // Search functionality
    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);
    stockFilter.addEventListener('change', filterItems);

    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedStock = stockFilter.value;

        rows.forEach(row => {
            const searchText = row.dataset.searchText;
            const category = row.dataset.category;
            const stockStatus = row.dataset.stockStatus;

            const matchesSearch = searchText.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;
            const matchesStock = !selectedStock || stockStatus === selectedStock;

            if (matchesSearch && matchesCategory && matchesStock) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                row.querySelector('.item-checkbox').checked = false;
            }
        });

        updateSelectAllCheckbox();
        updateSubmitButton();
    }

    function updateSelectAllCheckbox() {
        const visibleCheckboxes = Array.from(itemCheckboxes).filter(checkbox => 
            checkbox.closest('tr').style.display !== 'none'
        );
        const checkedVisibleCheckboxes = visibleCheckboxes.filter(checkbox => checkbox.checked);
        
        selectAllCheckbox.checked = visibleCheckboxes.length > 0 && 
                                  checkedVisibleCheckboxes.length === visibleCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedVisibleCheckboxes.length > 0 && 
                                        checkedVisibleCheckboxes.length < visibleCheckboxes.length;
    }

    function updateSubmitButton() {
        const checkedBoxes = Array.from(itemCheckboxes).filter(checkbox => 
            checkbox.checked && checkbox.closest('tr').style.display !== 'none'
        );
        const hasQuantities = checkedBoxes.some(checkbox => {
            const quantityInput = checkbox.closest('tr').querySelector('.quantity-input');
            return quantityInput && parseInt(quantityInput.value) > 0;
        });

        submitBtn.disabled = checkedBoxes.length === 0 || !hasQuantities;
    }

    function clearAll() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        quantityInputs.forEach(input => {
            input.value = 0;
        });
        selectAllCheckbox.checked = false;
        updateSubmitButton();
    }

    // Initial update
    updateSubmitButton();
});
</script>
@endsection
