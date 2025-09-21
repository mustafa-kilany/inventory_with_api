@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-clock-history"></i> Stock History - {{ $item->name }}
                </h1>
                <div>
                    <a href="{{ route('purchase-department.add-stock-form', $item) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Stock
                    </a>
                    <a href="{{ route('purchase-department.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Inventory
                    </a>
                </div>
            </div>

            {{-- Item Summary --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Item Information</h6>
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
                            </table>
                        </div>
                        <div class="col-md-3">
                            <h6>Current Stock</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $item->quantity_on_hand > 0 ? 'success' : 'danger' }}">
                                            {{ $item->quantity_on_hand }} {{ $item->unit }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Reorder Level:</strong></td>
                                    <td>{{ $item->reorder_level }} {{ $item->unit }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($item->isOutOfStock())
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($item->isLowStock())
                                            <span class="badge bg-warning">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <h6>Pricing</h6>
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
                                    <td><strong>Total Value:</strong></td>
                                    <td>
                                        @if($item->unit_price)
                                            ${{ number_format($item->quantity_on_hand * $item->unit_price, 2) }}
                                        @else
                                            <span class="text-muted">Not calculated</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <h6>Supplier</h6>
                            <table class="table table-sm">
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
                                    <td><strong>Location:</strong></td>
                                    <td>
                                        @if($item->location)
                                            {{ $item->location }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Transactions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Stock Addition History
                    </h5>
                </div>
                <div class="card-body">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction #</th>
                                        <th>Quantity Added</th>
                                        <th>Stock Before</th>
                                        <th>Stock After</th>
                                        <th>Notes</th>
                                        <th>Added By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <div>
                                                    {{ $transaction->transaction_date->format('M j, Y') }}
                                                    <br><small class="text-muted">{{ $transaction->transaction_date->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $transaction->transaction_number }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    +{{ $transaction->quantity }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $transaction->quantity_before }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $transaction->quantity_after }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($transaction->notes)
                                                    {{ $transaction->notes }}
                                                @else
                                                    <span class="text-muted">No notes</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $transaction->performedBy->name }}</strong>
                                                    <br><small class="text-muted">{{ $transaction->performedBy->employee_id }}</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            <nav aria-label="Transactions pagination">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($transactions->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">« Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transactions->previousPageUrl() }}" rel="prev">« Previous</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                                        @if ($page == $transactions->currentPage())
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
                                    @if ($transactions->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transactions->nextPageUrl() }}" rel="next">Next »</a>
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
                            <h5 class="text-muted mt-3">No stock additions found</h5>
                            <p class="text-muted">This item hasn't had any stock added by the Purchase Department yet.</p>
                            <a href="{{ route('purchase-department.add-stock-form', $item) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Stock Now
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
