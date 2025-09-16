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

    @if(auth()->user()->isEmployee())
        @include('dashboard.employee')
    @elseif(auth()->user()->isApprover())
        @include('dashboard.approver')
    @elseif(auth()->user()->isStockKeeper())
        @include('dashboard.stock-keeper')
    @elseif(auth()->user()->isAdministrator())
        @include('dashboard.administrator')
    @endif
</div>
@endsection
