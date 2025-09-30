@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
                <div class="text-muted">
                    Welcome back, <strong>{{ auth()->user()->name }}</strong>
                    <span class="badge bg-primary">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>
            
            {{-- Email Verification Reminder (Non-blocking) --}}
            @if(!auth()->user()->hasVerifiedEmail())
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-envelope me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Email Verification</strong> - Please verify your email address to ensure you receive important notifications.
                        </div>
                        <div class="ms-3">
                            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-send"></i> Send Verification Email
                                </button>
                            </form>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    {{-- General Charts Section for All Users --}}
    @if(isset($chart_data))
    <script>
        window.chartData = @json($chart_data);
    </script>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Stock Status Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="generalStockStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-tags"></i> Top Categories</h5>
                </div>
                <div class="card-body">
                    <canvas id="generalCategoriesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/general-dashboard-charts.js') }}"></script>
    <script src="{{ asset('js/role-dashboard-charts.js') }}"></script>
    @endif

    @if($user->hasRole('owner'))
    @include('dashboard.owner')
@elseif($user->isAdministrator())
    @include('dashboard.administrator')
@elseif($user->isStockKeeper())
    @include('dashboard.stock-keeper')
@elseif($user->isApprover())
    @include('dashboard.approver')
@elseif($user->isEmployee())
    @include('dashboard.employee')
@endif
</div>
@endsection
