@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Add Stock - {{ $item->name }}
                    </h5>
                    <a href="{{ route('purchase-department.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Inventory
                    </a>
                </div>
                <div class="card-body">
                    {{-- Item Information --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Item Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td>{{ $item->sku }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $item->category }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Stock:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                            {{ $item->quantity_on_hand }} {{ $item->unit }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Current Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Unit Price:</strong></td>
                                    <td>
                                        @if($item->unit_price)
                                            ${{ number_format($item->unit_price, 2) }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>
                                        @if($item->supplier)
                                            {{ $item->supplier }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Reorder Level:</strong></td>
                                    <td>{{ $item->reorder_level }} {{ $item->unit }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Stock Addition Form --}}
                    <form method="POST" action="{{ route('purchase-department.add-stock', $item) }}">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">
                                    Quantity to Add <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" min="1" max="10000" 
                                           value="{{ old('quantity') }}" required>
                                    <span class="input-group-text">{{ $item->unit }}</span>
                                </div>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Unit Price (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                                           id="unit_price" name="unit_price" min="0" max="999999.99" 
                                           step="0.01" value="{{ old('unit_price', $item->unit_price) }}">
                                </div>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="supplier" class="form-label">Supplier (Optional)</label>
                                <input type="text" class="form-control @error('supplier') is-invalid @enderror" 
                                       id="supplier" name="supplier" maxlength="255" 
                                       value="{{ old('supplier', $item->supplier) }}">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <input type="text" class="form-control @error('notes') is-invalid @enderror" 
                                       id="notes" name="notes" maxlength="500" 
                                       value="{{ old('notes') }}" 
                                       placeholder="e.g., Purchase order #12345">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6>Stock Addition Preview</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Current Stock:</strong> {{ $item->quantity_on_hand }} {{ $item->unit }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Adding:</strong> <span id="preview-quantity">0</span> {{ $item->unit }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>New Stock:</strong> <span id="preview-total">{{ $item->quantity_on_hand }}</span> {{ $item->unit }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status After:</strong> <span id="preview-status" class="badge"></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Stock
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
    const quantityInput = document.getElementById('quantity');
    const previewQuantity = document.getElementById('preview-quantity');
    const previewTotal = document.getElementById('preview-total');
    const previewStatus = document.getElementById('preview-status');
    const currentStock = {{ $item->quantity_on_hand }};
    const reorderLevel = {{ $item->reorder_level }};

    function updatePreview() {
        const quantity = parseInt(quantityInput.value) || 0;
        const newTotal = currentStock + quantity;
        
        previewQuantity.textContent = quantity;
        previewTotal.textContent = newTotal;
        
        // Update status badge
        if (newTotal === 0) {
            previewStatus.textContent = 'Out of Stock';
            previewStatus.className = 'badge bg-danger';
        } else if (newTotal <= reorderLevel) {
            previewStatus.textContent = 'Low Stock';
            previewStatus.className = 'badge bg-warning';
        } else {
            previewStatus.textContent = 'In Stock';
            previewStatus.className = 'badge bg-success';
        }
    }

    quantityInput.addEventListener('input', updatePreview);
    updatePreview(); // Initial update
});
</script>
@endsection
