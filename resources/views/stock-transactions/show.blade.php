@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-eye"></i> Stock Transaction Details</h4>
                        <div>
                            @can('edit stock transactions')
                                <a href="{{ route('stock-transactions.edit', $stockTransaction) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            @endcan
                            <a href="{{ route('stock-transactions.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Transaction Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Transaction Number:</strong></td>
                                    <td><span class="badge bg-primary">{{ $stockTransaction->transaction_number }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Item:</strong></td>
                                    <td>{{ $stockTransaction->item->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        @if($stockTransaction->type === 'in')
                                            <span class="badge bg-success">Stock In</span>
                                        @elseif($stockTransaction->type === 'out')
                                            <span class="badge bg-danger">Stock Out</span>
                                        @else
                                            <span class="badge bg-warning">Adjustment</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td>
                                        @if($stockTransaction->type === 'out')
                                            <span class="text-danger">-{{ abs($stockTransaction->quantity) }}</span>
                                        @else
                                            <span class="text-success">+{{ $stockTransaction->quantity }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Performed By:</strong></td>
                                    <td>{{ $stockTransaction->performedBy->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Transaction Date:</strong></td>
                                    <td>{{ $stockTransaction->transaction_date->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Stock Levels</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Quantity Before:</strong></td>
                                    <td><span class="badge bg-info">{{ $stockTransaction->quantity_before }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity After:</strong></td>
                                    <td><span class="badge bg-success">{{ $stockTransaction->quantity_after }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Net Change:</strong></td>
                                    <td>
                                        @php
                                            $change = $stockTransaction->quantity_after - $stockTransaction->quantity_before;
                                        @endphp
                                        @if($change > 0)
                                            <span class="text-success">+{{ $change }}</span>
                                        @elseif($change < 0)
                                            <span class="text-danger">{{ $change }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($stockTransaction->reference_type || $stockTransaction->reference_id)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Reference Information</h6>
                            <table class="table table-borderless">
                                @if($stockTransaction->reference_type)
                                <tr>
                                    <td><strong>Reference Type:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $stockTransaction->reference_type)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                @if($stockTransaction->reference_id)
                                <tr>
                                    <td><strong>Reference ID:</strong></td>
                                    <td>{{ $stockTransaction->reference_id }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($stockTransaction->notes)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Notes</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $stockTransaction->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Timestamps</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $stockTransaction->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $stockTransaction->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
