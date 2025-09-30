@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- DOC. ZUHAIR OWNER DASHBOARD --}}
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="bi bi-person-badge"></i> Owner Dashboard
                <small class="text-muted">- Doc. Zuhair</small>
            </h2>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_requests_for_owner'] }}</h4>
                            <p class="mb-0">Pending Approval</p>
                        </div>
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['urgent_requests'] }}</h4>
                            <p class="mb-0">Urgent Requests</p>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['overdue_requests'] }}</h4>
                            <p class="mb-0">Overdue</p>
                        </div>
                        <i class="bi bi-calendar-x fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">${{ number_format($total_pending_value ?? 0, 0) }}</h4>
                            <p class="mb-0">Total Value</p>
                        </div>
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PENDING REQUESTS TABLE --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> 
                        Purchase Requests Awaiting Your Approval
                    </h5>
                    <span class="badge bg-primary">{{ $pending_count }} Pending</span>
                </div>
                <div class="card-body">
                    @if($pending_owner_requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request #</th>
                                        <th>Requested By</th>
                                        <th>Priority</th>
                                        <th>Items to Purchase</th>
                                        <th>Final Price</th>
                                        <th>Needed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pending_owner_requests as $request)
                                        <tr>
                                            <td>
                                                <a href="{{ route('purchase-requests.show', $request) }}" 
                                                   class="text-decoration-none fw-bold">
                                                    {{ $request->request_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div>{{ $request->requestedBy->name }}</div>
                                                <small class="text-muted">{{ $request->requestedBy->department }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->priority === 'urgent' ? 'danger' : ($request->priority === 'high' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    @foreach($request->items as $item)
                                                        <div>• {{ $item->item->name }} (Qty: {{ $item->quantity_requested }})</div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    ${{ number_format($request->actual_total ?? $request->estimated_total, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->needed_by)
                                                    <div>{{ $request->needed_by->format('M j, Y') }}</div>
                                                    @if($request->needed_by->isPast())
                                                        <small class="text-danger">Overdue</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('purchase-requests.show', $request) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Review & Approve
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No Pending Requests</h5>
                            <p class="text-muted">All purchase requests have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection