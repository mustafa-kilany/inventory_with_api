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

            {{-- Shown when you redirected with "sent_to_owner" after saving --}}
            @if(session('sent_to_owner'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-send-check"></i> {{ session('sent_to_owner') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Optional: show context if this bulk page is opened from a specific PR --}}
            @isset($purchaseRequest)
                <div class="alert alert-info d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">
                            Working on Purchase Request
                            <a class="text-decoration-none" href="{{ route('purchase-requests.show', $purchaseRequest) }}">
                                #{{ $purchaseRequest->request_number }}
                            </a>
                        </div>
                        <small class="text-muted">
                            Current stage: {{ str_replace('_', ' ', $purchaseRequest->workflow_status) }}
                        </small>
                    </div>
                    <a class="btn btn-sm btn-outline-primary"
                       href="{{ route('purchase-requests.show', $purchaseRequest) }}">
                        <i class="bi bi-eye"></i> View PR
                    </a>
                </div>
            @endisset

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Add Stock to Multiple Items
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchase-department.bulk-add-stock') }}">
                        @csrf
                        @isset($purchaseRequest)
                            <input type="hidden" name="purchase_request_id" value="{{ $purchaseRequest->id }}">
                        @endisset

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
                                        <th width="23%">Item</th>
                                        <th width="12%">Category</th>
                                        <th width="12%">Current Stock</th>
                                        <th width="12%">Quantity to Add</th>
                                        <th width="12%">Unit Price</th>
                                        <th width="12%">Line Total</th>
                                        <th width="12%">Supplier</th>
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
                                                       class="form-control form-control-sm unit-input" 
                                                       min="0" max="999999.99" step="0.01" 
                                                       value="{{ $item->unit_price }}" 
                                                       placeholder="Unit price">
                                            </td>
                                            <td>
                                                <span class="fw-semibold">$<span class="line-total">0.00</span></span>
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
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="6" class="text-end">Grand Total:</th>
                                        <th colspan="2" class="fw-bold">$<span id="grand-total">0.00</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h6>Bulk Notes</h6>
                            <textarea name="bulk_notes" class="form-control" rows="3" 
                                      placeholder="Add notes that will apply to all selected items (e.g., Purchase order #12345, Delivery from ABC Supplier)"></textarea>
                        </div>

                        {{-- Owner step is automatic when tied to a request; no toggle UI --}}

                        <div class="mt-4 d-flex gap-2 align-items-center">
                            @php
                                $isPr = isset($purchaseRequest);
                                $wf = $isPr ? $purchaseRequest->workflow_status : null;
                            @endphp
                            @if($isPr && $wf === 'pending_purchase_department')
                                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                    <i class="bi bi-send"></i> Send to Owner
                                </button>
                                <small class="text-muted">Owner approval required before stock can be added.</small>
                            @elseif($isPr && $wf === 'pending_owner')
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="bi bi-hourglass-split"></i> Waiting for Owner Approval
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                    <i class="bi bi-plus-circle"></i> Add Stock to Selected Items
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-secondary" onclick="clearAll()">
                                <i class="bi bi-x-circle"></i> Clear All
                            </button>
                            <a href="{{ route('purchase-department.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                        {{-- No owner toggle; controller auto-sends when appropriate --}}
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
    const unitInputs = document.querySelectorAll('.unit-input');
    const submitBtn = document.getElementById('submit-btn');
    const searchInput = document.getElementById('search_items');
    const categoryFilter = document.getElementById('filter_category');
    const stockFilter = document.getElementById('filter_stock');
    const table = document.getElementById('items-table');
    const rows = table.querySelectorAll('tbody tr');
    const grandTotalEl = document.getElementById('grand-total');

    // Owner extras removed; flow is automatic

    function formatMoney(n) {
        return (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
    }

    function rowLineTotal(row) {
        const qty = parseFloat(row.querySelector('.quantity-input')?.value || '0');
        const unit = parseFloat(row.querySelector('.unit-input')?.value || '0');
        return qty * unit;
    }

    function updateRowLineTotal(row) {
        const lineTotalSpan = row.querySelector('.line-total');
        if (!lineTotalSpan) return;
        const val = rowLineTotal(row);
        lineTotalSpan.textContent = formatMoney(val);
    }

    function updateGrandTotal() {
        let sum = 0;
        rows.forEach(row => {
            const visible = row.style.display !== 'none';
            const checked = row.querySelector('.item-checkbox')?.checked;
            if (visible && checked) {
                sum += rowLineTotal(row);
            }
        });
        grandTotalEl.textContent = formatMoney(sum);
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

    function filterItems() {
        const searchTerm = (searchInput.value || '').toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedStock = stockFilter.value;

        rows.forEach(row => {
            const searchText = row.dataset.searchText || '';
            const category = row.dataset.category || '';
            const stockStatus = row.dataset.stockStatus || '';

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
        updateGrandTotal();
    }

    function clearAll() {
        itemCheckboxes.forEach(checkbox => checkbox.checked = false);
        quantityInputs.forEach(input => input.value = 0);
        unitInputs.forEach(input => input.value = input.getAttribute('value') || input.value);
        selectAllCheckbox.checked = false;
        updateSubmitButton();
        updateGrandTotal();
        rows.forEach(updateRowLineTotal);
    }

    // Event wiring
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = this.checked;
            }
        });
        updateSubmitButton();
        updateGrandTotal();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateSubmitButton();
            updateSelectAllCheckbox();
            updateGrandTotal();
        });
    });

    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const checkbox = row.querySelector('.item-checkbox');
            if ((parseFloat(this.value) || 0) > 0) {
                checkbox.checked = true;
            }
            updateRowLineTotal(row);
            updateSubmitButton();
            updateGrandTotal();
        });
    });

    unitInputs.forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            updateRowLineTotal(row);
            updateGrandTotal();
        });
    });

    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);
    stockFilter.addEventListener('change', filterItems);

    // No owner toggle handler

    // Initial calculations
    rows.forEach(updateRowLineTotal);
    updateGrandTotal();
    updateSubmitButton();

    // Expose clearAll for the button
    window.clearAll = clearAll;
});
</script>
@endsection
