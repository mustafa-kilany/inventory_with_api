@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-plus-circle"></i> Create Purchase Request</h1>
                <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Requests
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-requests.store') }}" id="purchaseRequestForm">
                @csrf
                
                {{-- Request Details --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="justification" class="form-label">Justification <span class="text-danger">*</span></label>
                                    <textarea name="justification" id="justification" class="form-control @error('justification') is-invalid @enderror" 
                                            rows="4" required placeholder="Please explain why these items are needed...">{{ old('justification') }}</textarea>
                                    @error('justification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="needed_by" class="form-label">Needed By (Optional)</label>
                                    <input type="date" name="needed_by" id="needed_by" 
                                           class="form-control @error('needed_by') is-invalid @enderror"
                                           value="{{ old('needed_by') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('needed_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">When do you need these items?</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items Selection --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box"></i> Requested Items</h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                            <i class="bi bi-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            {{-- Item rows will be added here dynamically --}}
                        </div>
                        
                        @error('items')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                        
                        <div class="text-muted mt-3">
                            <small><i class="bi bi-info-circle"></i> Click "Add Item" to start selecting items for your request.</small>
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Request Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Total Items:</span>
                                    <span id="totalItems" class="fw-bold">0</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Estimated Total:</span>
                                    <span id="estimatedTotal" class="fw-bold">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Item Row Template --}}
<template id="itemRowTemplate">
    <div class="item-row border rounded p-3 mb-3" data-row-index="">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="mb-2">
                    <label class="form-label">Item <span class="text-danger">*</span></label>
                    <select name="items[INDEX][item_id]" class="form-select item-select" required onchange="updateItemInfo(this)">
                        <option value="">Select an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" 
                                    data-name="{{ $item->name }}" 
                                    data-sku="{{ $item->sku }}"
                                    data-price="{{ $item->unit_price }}"
                                    data-unit="{{ $item->unit }}"
                                    data-stock="{{ $item->quantity_on_hand }}"
                                    data-low-stock="{{ $item->isLowStock() ? 'true' : 'false' }}">
                                {{ $item->name }} ({{ $item->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="item-info" style="display: none;">
                    <small class="text-muted">
                        <div>SKU: <span class="item-sku"></span></div>
                        <div>Price: $<span class="item-price"></span> per <span class="item-unit"></span></div>
                        <div class="stock-info">Stock: <span class="item-stock"></span></div>
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-2">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="items[INDEX][quantity]" class="form-control quantity-input" 
                           min="1" required onchange="updateRowTotal(this)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-2">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="items[INDEX][notes]" class="form-control" 
                           placeholder="Any specific requirements...">
                </div>
            </div>
            <div class="col-md-1">
                <div class="mb-2">
                    <label class="form-label">Total</label>
                    <div class="fw-bold text-primary row-total">$0.00</div>
                </div>
            </div>
            <div class="col-md-1">
                <div class="mb-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger d-block" onclick="removeItemRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let itemRowIndex = 0;

function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const container = document.getElementById('itemsContainer');
    
    const clone = template.content.cloneNode(true);
    
    // Update the row index
    const row = clone.querySelector('.item-row');
    row.setAttribute('data-row-index', itemRowIndex);
    
    // Update all name attributes to use the current index
    const inputs = clone.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.name) {
            input.name = input.name.replace('INDEX', itemRowIndex);
        }
    });
    
    container.appendChild(clone);
    itemRowIndex++;
    
    updateSummary();
}

function removeItemRow(button) {
    const row = button.closest('.item-row');
    row.remove();
    updateSummary();
}

function updateItemInfo(select) {
    const row = select.closest('.item-row');
    const option = select.selectedOptions[0];
    const infoDiv = row.querySelector('.item-info');
    
    if (option.value) {
        const sku = option.dataset.sku;
        const price = option.dataset.price;
        const unit = option.dataset.unit;
        const stock = option.dataset.stock;
        const lowStock = option.dataset.lowStock === 'true';
        
        row.querySelector('.item-sku').textContent = sku;
        row.querySelector('.item-price').textContent = price;
        row.querySelector('.item-unit').textContent = unit;
        row.querySelector('.item-stock').textContent = stock;
        
        const stockInfo = row.querySelector('.stock-info');
        if (lowStock) {
            stockInfo.className = 'text-warning';
            stockInfo.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Stock: ${stock} (Low Stock)`;
        } else if (stock == 0) {
            stockInfo.className = 'text-danger';
            stockInfo.innerHTML = `<i class="bi bi-x-circle"></i> Stock: ${stock} (Out of Stock)`;
        } else {
            stockInfo.className = 'text-success';
            stockInfo.innerHTML = `<i class="bi bi-check-circle"></i> Stock: ${stock}`;
        }
        
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
    
    updateRowTotal(select);
}

function updateRowTotal(element) {
    const row = element.closest('.item-row');
    const select = row.querySelector('.item-select');
    const quantityInput = row.querySelector('.quantity-input');
    const totalDiv = row.querySelector('.row-total');
    
    const option = select.selectedOptions[0];
    if (option && option.value && quantityInput.value) {
        const price = parseFloat(option.dataset.price) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const total = price * quantity;
        
        totalDiv.textContent = `$${total.toFixed(2)}`;
    } else {
        totalDiv.textContent = '$0.00';
    }
    
    updateSummary();
}

function updateSummary() {
    const rows = document.querySelectorAll('.item-row');
    let totalItems = 0;
    let estimatedTotal = 0;
    
    rows.forEach(row => {
        const select = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        
        if (select.value && quantityInput.value) {
            totalItems++;
            const option = select.selectedOptions[0];
            const price = parseFloat(option.dataset.price) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            estimatedTotal += price * quantity;
        }
    });
    
    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('estimatedTotal').textContent = `$${estimatedTotal.toFixed(2)}`;
}

// Add first item row on page load
document.addEventListener('DOMContentLoaded', function() {
    addItemRow();
});

// Form validation
document.getElementById('purchaseRequestForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.item-row');
    let hasItems = false;
    
    rows.forEach(row => {
        const select = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        
        if (select.value && quantityInput.value) {
            hasItems = true;
        }
    });
    
    if (!hasItems) {
        e.preventDefault();
        alert('Please add at least one item to your request.');
        return false;
    }
});
</script>
@endsection
