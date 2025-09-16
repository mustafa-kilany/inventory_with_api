<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Inventory endpoints for employees and external systems
    Route::get('/items', [InventoryApiController::class, 'getItems']);
    Route::get('/items/{identifier}', [InventoryApiController::class, 'getItem']);
    Route::get('/categories', [InventoryApiController::class, 'getCategories']);
    Route::get('/statistics', [InventoryApiController::class, 'getStatistics']);
    
    // Availability check
    Route::post('/check-availability', [InventoryApiController::class, 'checkAvailability']);
    
    // Purchase requests
    Route::get('/purchase-requests', [InventoryApiController::class, 'getPurchaseRequests']);
    Route::post('/purchase-requests', [InventoryApiController::class, 'createItemRequest']);
    
    // Stock movements (read-only)
    Route::get('/stock-movements', [InventoryApiController::class, 'getStockMovements']);
    
});

/*
Example API Endpoints:

GET /api/v1/items - Get all items with filtering
    ?category=Office Supplies
    &search=mouse
    &low_stock=true
    &per_page=20

GET /api/v1/items/MSE001 - Get item by SKU or ID
    ?include_transactions=true

GET /api/v1/categories - Get all categories

GET /api/v1/statistics - Get inventory statistics

POST /api/v1/check-availability - Check item availability
{
    "items": [
        {"item_id": 1, "quantity": 5},
        {"item_id": 2, "quantity": 10}
    ]
}

GET /api/v1/purchase-requests - Get purchase requests
    ?status=pending
    &priority=urgent

POST /api/v1/purchase-requests - Create new purchase request
{
    "requester_email": "employee@inventory.com",
    "items": [
        {"item_id": 1, "quantity": 5}
    ],
    "justification": "Need for project X",
    "priority": "medium",
    "needed_by": "2025-10-01"
}

GET /api/v1/stock-movements - Get stock movement history
    ?item_id=1
    &type=in
    &from_date=2025-01-01
    &to_date=2025-12-31

*/
