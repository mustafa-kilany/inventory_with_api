<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Instant login experience: root redirects to login or to appropriate page if authenticated
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('employee')) {
            return redirect()->route('purchase-requests.create');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes with email verification
Auth::routes(['verify' => true]);

// Email verification routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Protected routes - Email verification available but not required
// Note: 'verified' middleware commented out - users can access without email verification
Route::middleware(['auth' /* , 'verified' */])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Item management routes (with permission checks - employees can't see stock levels)
    Route::resource('items', \App\Http\Controllers\ItemController::class)->middleware('permission:view stock levels');
    
    // Purchase request routes
    Route::resource('purchase-requests', \App\Http\Controllers\PurchaseRequestController::class);
    
    // Stock transaction routes
    Route::resource('stock-transactions', \App\Http\Controllers\StockTransactionController::class)
        ->middleware('permission:view stock transactions');
    
    // Additional routes for actions
    Route::post('/purchase-requests/{purchaseRequest}/approve', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approve'
    ])->name('purchase-requests.approve')->middleware('permission:approve purchase requests');
    
    Route::post('/purchase-requests/{purchaseRequest}/reject', [
        \App\Http\Controllers\PurchaseRequestController::class, 'reject'
    ])->name('purchase-requests.reject')->middleware('permission:approve purchase requests');
    
    Route::post('/purchase-requests/{purchaseRequest}/fulfill', [
        \App\Http\Controllers\PurchaseRequestController::class, 'fulfill'
    ])->name('purchase-requests.fulfill')->middleware('permission:fulfill purchase requests');
    
    // New workflow approval routes
    Route::post('/purchase-requests/{purchaseRequest}/approve-department-head', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveDepartmentHead'
    ])->name('purchase-requests.approve-department-head')->middleware('permission:approve as department head');
    
    Route::post('/purchase-requests/{purchaseRequest}/approve-manager', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveManager'
    ])->name('purchase-requests.approve-manager')->middleware('permission:approve as manager');
    
    Route::post('/purchase-requests/{purchaseRequest}/approve-purchase-department', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approvePurchaseDepartment'
    ])->name('purchase-requests.approve-purchase-department')->middleware('permission:approve as purchase department');
    
    Route::post('/purchase-requests/{purchaseRequest}/approve-stock-keeper', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveStockKeeper'
    ])->name('purchase-requests.approve-stock-keeper')->middleware('permission:fulfill purchase requests');
    
    Route::post('/purchase-requests/{purchaseRequest}/reject-workflow', [
        \App\Http\Controllers\PurchaseRequestController::class, 'rejectWorkflow'
    ])->name('purchase-requests.reject-workflow');
});
// Remove duplicate Auth::routes and legacy /home
