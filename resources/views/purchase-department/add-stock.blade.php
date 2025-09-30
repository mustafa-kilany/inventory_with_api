@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Success alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Special banner when request was sent to Owner --}}
            @if(session('sent_to_owner'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-send-check"></i>
                    {{ session('sent_to_owner') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

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
                    {{-- Optional: PR context (pass $purchaseRequest from controller when coming from a PR) --}}
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
                                    Current stage: {{ str_replace('_',' ', $purchaseRequest->workflow_status) }}
                                </small>
                            </div>
                            <a class="btn btn-sm btn-outline-primary"
                               href="{{ route('purchase-requests.show', $purchaseRequest) }}">
                                <i class="bi bi-eye"></i> View PR
                            </a>
                        </div>
                    @endisset

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

                        @isset($purchaseRequest)
                            {{-- Keep PR context with the submission (controller can use if desired) --}}
                            <input type="hidden" name="purchase_request_id" value="{{ $purchaseRequest->id }}">
                        @endisset

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

                        {{-- Live Cost Preview --}}
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6>Stock & Cost Preview</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Current Stock:</strong> {{ $item->quantity_on_hand }} {{ $item->unit }}</p>
                                    <p class="mb-1"><strong>Adding:</strong> <span id="preview-quantity">0</span> {{ $item->unit }}</p>
                                    <p class="mb-1"><strong>New Stock:</strong> <span id="preview-total">{{ $item->quantity_on_hand }}</span> {{ $item->unit }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Unit Price:</strong> $<span id="preview-unit">{{ number_format($item->unit_price ?? 0, 2) }}</span></p>
                                    <p class="mb-1"><strong>Line Total (qty × unit):</strong>
                                        $<span id="preview-line">0.00</span>
                                    </p>
                                    <p class="mb-1"><strong>Status After:</strong> <span id="preview-status" class="badge"></span></p>
                                </div>
                            </div>
                        </div>

                        {{-- Owner step is automatic when tied to a request; no toggle UI --}}

                        <div class="mt-4 d-flex gap-2 align-items-center">
                            @php
                                $isPr = isset($purchaseRequest);
                                $wf = $isPr ? $purchaseRequest->workflow_status : null;
                            @endphp
                            @if($isPr && $wf === 'pending_purchase_execution')
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Add Stock
                                </button>
                            @elseif($isPr && $wf === 'pending_owner')
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="bi bi-hourglass-split"></i> Waiting for Owner Approval
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Send to Owner
                                </button>
                                <small class="text-muted">Owner approval required before stock can be added.</small>
                            @endif
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
    const quantityInput   = document.getElementById('quantity');
    const unitPriceInput  = document.getElementById('unit_price');
    const previewQuantity = document.getElementById('preview-quantity');
    const previewTotal    = document.getElementById('preview-total');
    const previewStatus   = document.getElementById('preview-status');
    const previewUnit     = document.getElementById('preview-unit');
    const previewLine     = document.getElementById('preview-line');

    const currentStock = {{ $item->quantity_on_hand }};
    const reorderLevel = {{ $item->reorder_level }};

    // Owner extras removed; flow is automatic

    function formatMoney(n) {
        return (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
    }

    function updatePreview() {
        const qty  = parseInt(quantityInput.value) || 0;
        const unit = parseFloat(unitPriceInput.value) || 0;
        const newTotal = currentStock + qty;

        previewQuantity.textContent = qty;
        previewTotal.textContent    = newTotal;
        previewUnit.textContent     = formatMoney(unit);
        previewLine.textContent     = formatMoney(qty * unit);

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
    unitPriceInput.addEventListener('input', updatePreview);
    updatePreview(); // Initial

    // No owner toggle handler
});
</script>
@endsection
