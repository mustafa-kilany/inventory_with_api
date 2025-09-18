@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-arrow-left-right"></i> Stock Movements</h1>
                @can('create stock transactions')
                    <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> New Transaction
                    </a>
                @endcan
            </div>

            {{-- Filters --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('stock-transactions.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="reference_type" class="form-label">Reference</label>
                            <select name="reference_type" id="reference_type" class="form-select">
                                <option value="">All References</option>
                                <option value="purchase_request" {{ request('reference_type') == 'purchase_request' ? 'selected' : '' }}>Purchase Request</option>
                                <option value="procurement" {{ request('reference_type') == 'procurement' ? 'selected' : '' }}>Procurement</option>
                                <option value="manual" {{ request('reference_type') == 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="return" {{ request('reference_type') == 'return' ? 'selected' : '' }}>Return</option>
                                <option value="damage" {{ request('reference_type') == 'damage' ? 'selected' : '' }}>Damage</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="item_id" class="form-label">Item</label>
                            <select name="item_id" id="item_id" class="form-select">
                                <option value="">All Items</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="performed_by" class="form-label">Performed By</label>
                            <select name="performed_by" id="performed_by" class="form-select">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('performed_by') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Stock Transactions Table --}}
            <div class="card">
                <div class="card-body">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction #</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Before</th>
                                        <th>After</th>
                                        <th>Performed By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $transaction->transaction_number }}</span>
                                        </td>
                                        <td>{{ $transaction->item->name }}</td>
                                        <td>
                                            @if($transaction->type === 'in')
                                                <span class="badge bg-success">Stock In</span>
                                            @elseif($transaction->type === 'out')
                                                <span class="badge bg-danger">Stock Out</span>
                                            @else
                                                <span class="badge bg-warning">Adjustment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->type === 'out')
                                                <span class="text-danger">-{{ abs($transaction->quantity) }}</span>
                                            @else
                                                <span class="text-success">+{{ $transaction->quantity }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->quantity_before }}</td>
                                        <td>{{ $transaction->quantity_after }}</td>
                                        <td>{{ $transaction->performedBy->name }}</td>
                                        <td>{{ $transaction->transaction_date->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('stock-transactions.show', $transaction) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('edit stock transactions')
                                                    <a href="{{ route('stock-transactions.edit', $transaction) }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete stock transactions')
                                                    <form method="POST" action="{{ route('stock-transactions.destroy', $transaction) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Stock transactions pagination">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($transactions->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transactions->withQueryString()->previousPageUrl() }}" rel="prev">Previous</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($transactions->withQueryString()->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                                        @if ($page == $transactions->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($transactions->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transactions->withQueryString()->nextPageUrl() }}" rel="next">Next</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No Stock Transactions Found</h4>
                            <p class="text-muted">There are no stock transactions matching your criteria.</p>
                            @can('create stock transactions')
                                <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create First Transaction
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
