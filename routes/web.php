<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root → login or role landing
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

// Auth (with email verification available)
Auth::routes(['verify' => true]);

// Email verification views
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

// ------------------------------------------------------------------
// Protected routes (email verification optional)
// ------------------------------------------------------------------
Route::middleware(['auth' /* , 'verified' */])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Items (lock behind permission so employees can't see stock levels)
    Route::resource('items', \App\Http\Controllers\ItemController::class)
        ->middleware('permission:view stock levels');

    // Purchase Requests
    Route::resource('purchase-requests', \App\Http\Controllers\PurchaseRequestController::class);

    // Stock Transactions (read access permissioned)
    Route::resource('stock-transactions', \App\Http\Controllers\StockTransactionController::class)
        ->middleware('permission:view stock transactions');

    // ---------------------------
    // Purchase Request actions
    // ---------------------------
    Route::post('/purchase-requests/{purchaseRequest}/approve', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approve'
    ])->name('purchase-requests.approve')
      ->middleware('permission:approve purchase requests');

    Route::post('/purchase-requests/{purchaseRequest}/reject', [
        \App\Http\Controllers\PurchaseRequestController::class, 'reject'
    ])->name('purchase-requests.reject')
      ->middleware('permission:approve purchase requests');

    Route::post('/purchase-requests/{purchaseRequest}/fulfill', [
        \App\Http\Controllers\PurchaseRequestController::class, 'fulfill'
    ])->name('purchase-requests.fulfill')
      ->middleware('permission:fulfill purchase requests');

    // Workflow approvals
    Route::post('/purchase-requests/{purchaseRequest}/approve-department-head', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveDepartmentHead'
    ])->name('purchase-requests.approve-department-head')
      ->middleware('permission:approve as department head');

    Route::post('/purchase-requests/{purchaseRequest}/approve-manager', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveManager'
    ])->name('purchase-requests.approve-manager')
      ->middleware('permission:approve as manager');

    Route::post('/purchase-requests/{purchaseRequest}/approve-purchase-department', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approvePurchaseDepartment'
    ])->name('purchase-requests.approve-purchase-department')
      ->middleware('permission:approve as purchase department');

    // NEW: send to owner (explicit endpoint if you use a separate button)
    Route::post('/purchase-requests/{purchaseRequest}/send-to-owner', [
        \App\Http\Controllers\PurchaseRequestController::class, 'sendToOwner'
    ])->name('purchase-requests.send-to-owner')
      ->middleware('permission:approve as purchase department');

    // NEW: owner approves (normal owner step)
    Route::post('/purchase-requests/{purchaseRequest}/approve-owner', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveOwner'
    ])->name('purchase-requests.approve-owner')
      ->middleware('permission:approve as owner');

    // NEW: owner-only override approval (unique power)
    Route::post('/purchase-requests/{purchaseRequest}/approve-owner-override', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveOwnerOverride'
    ])->name('purchase-requests.approve-owner-override')
      ->middleware('permission:owner force approve');

    Route::post('/purchase-requests/{purchaseRequest}/approve-stock-keeper', [
        \App\Http\Controllers\PurchaseRequestController::class, 'approveStockKeeper'
    ])->name('purchase-requests.approve-stock-keeper')
      ->middleware('permission:fulfill purchase requests');

    Route::post('/purchase-requests/{purchaseRequest}/reject-workflow', [
        \App\Http\Controllers\PurchaseRequestController::class, 'rejectWorkflow'
    ])->name('purchase-requests.reject-workflow');

    Route::post('/purchase-requests/{purchaseRequest}/add-stock', [
        \App\Http\Controllers\PurchaseRequestController::class, 'addStockToItems'
    ])->name('purchase-requests.add-stock')
      ->middleware('permission:approve as purchase department');

    // ---------------------------
    // Purchase Department tools
    // ---------------------------
    Route::prefix('purchase-department')
        ->name('purchase-department.')
        ->middleware('permission:approve as purchase department')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\PurchaseDepartmentController::class, 'index'])->name('index');
            Route::get('/search', [\App\Http\Controllers\PurchaseDepartmentController::class, 'search'])->name('search');
            Route::get('/items/{item}/add-stock', [\App\Http\Controllers\PurchaseDepartmentController::class, 'addStockForm'])->name('add-stock-form');
            Route::post('/items/{item}/add-stock', [\App\Http\Controllers\PurchaseDepartmentController::class, 'addStock'])->name('add-stock');
            Route::get('/bulk-add-stock', [\App\Http\Controllers\PurchaseDepartmentController::class, 'bulkAddStockForm'])->name('bulk-add-stock-form');
            Route::post('/bulk-add-stock', [\App\Http\Controllers\PurchaseDepartmentController::class, 'bulkAddStock'])->name('bulk-add-stock');
            Route::get('/items/{item}/stock-history', [\App\Http\Controllers\PurchaseDepartmentController::class, 'stockHistory'])->name('stock-history');
        });
});
