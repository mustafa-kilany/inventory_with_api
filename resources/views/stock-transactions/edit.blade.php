@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Stock Transaction</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('stock-transactions.update', $stockTransaction) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="item_id" class="form-label">Item <span class="text-danger">*</span></label>
                                <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">Select an item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id', $stockTransaction->item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->quantity_on_hand }} {{ $item->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select type</option>
                                    <option value="in" {{ old('type', $stockTransaction->type) == 'in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="out" {{ old('type', $stockTransaction->type) == 'out' ? 'selected' : '' }}>Stock Out</option>
                                    <option value="adjustment" {{ old('type', $stockTransaction->type) == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                       value="{{ old('quantity', $stockTransaction->quantity) }}" min="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="quantity_before" class="form-label">Quantity Before <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_before" id="quantity_before" class="form-control @error('quantity_before') is-invalid @enderror" 
                                       value="{{ old('quantity_before', $stockTransaction->quantity_before) }}" min="0" required>
                                @error('quantity_before')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="quantity_after" class="form-label">Quantity After <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_after" id="quantity_after" class="form-control @error('quantity_after') is-invalid @enderror" 
                                       value="{{ old('quantity_after', $stockTransaction->quantity_after) }}" min="0" required>
                                @error('quantity_after')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reference_type" class="form-label">Reference Type</label>
                                <select name="reference_type" id="reference_type" class="form-select @error('reference_type') is-invalid @enderror">
                                    <option value="">Select reference type</option>
                                    <option value="purchase_request" {{ old('reference_type', $stockTransaction->reference_type) == 'purchase_request' ? 'selected' : '' }}>Purchase Request</option>
                                    <option value="procurement" {{ old('reference_type', $stockTransaction->reference_type) == 'procurement' ? 'selected' : '' }}>Procurement</option>
                                    <option value="manual" {{ old('reference_type', $stockTransaction->reference_type) == 'manual' ? 'selected' : '' }}>Manual Adjustment</option>
                                    <option value="return" {{ old('reference_type', $stockTransaction->reference_type) == 'return' ? 'selected' : '' }}>Return</option>
                                    <option value="damage" {{ old('reference_type', $stockTransaction->reference_type) == 'damage' ? 'selected' : '' }}>Damage</option>
                                    <option value="other" {{ old('reference_type', $stockTransaction->reference_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('reference_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="reference_id" class="form-label">Reference ID</label>
                                <input type="number" name="reference_id" id="reference_id" class="form-control @error('reference_id') is-invalid @enderror" 
                                       value="{{ old('reference_id', $stockTransaction->reference_id) }}" min="1">
                                @error('reference_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="performed_by" class="form-label">Performed By <span class="text-danger">*</span></label>
                                <select name="performed_by" id="performed_by" class="form-select @error('performed_by') is-invalid @enderror" required>
                                    <option value="">Select user</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('performed_by', $stockTransaction->performed_by) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('performed_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="transaction_date" id="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                       value="{{ old('transaction_date', $stockTransaction->transaction_date->format('Y-m-d\TH:i')) }}" required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $stockTransaction->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stock-transactions.show', $stockTransaction) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Details
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item_id');
    const quantityBeforeInput = document.getElementById('quantity_before');
    const typeSelect = document.getElementById('type');
    const quantityInput = document.getElementById('quantity');
    const quantityAfterInput = document.getElementById('quantity_after');

    // Update quantity before when item is selected
    itemSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const currentStock = selectedOption.textContent.match(/\((\d+)/);
            if (currentStock) {
                quantityBeforeInput.value = currentStock[1];
            }
        }
    });

    // Calculate quantity after based on type and quantity
    function calculateQuantityAfter() {
        const quantityBefore = parseInt(quantityBeforeInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const type = typeSelect.value;

        if (type === 'in') {
            quantityAfterInput.value = quantityBefore + quantity;
        } else if (type === 'out') {
            quantityAfterInput.value = Math.max(0, quantityBefore - quantity);
        } else if (type === 'adjustment') {
            quantityAfterInput.value = quantity;
        }
    }

    typeSelect.addEventListener('change', calculateQuantityAfter);
    quantityInput.addEventListener('input', calculateQuantityAfter);
    quantityBeforeInput.addEventListener('input', calculateQuantityAfter);
});
</script>
@endsection
