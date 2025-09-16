<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    /**
     * Get all active inventory items
     */
    public function getItems(Request $request): JsonResponse
    {
        $query = Item::active();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search by name or SKU
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter low stock items
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Filter out of stock items
        if ($request->boolean('out_of_stock')) {
            $query->outOfStock();
        }

        $items = $query->orderBy('name')->paginate(
            $request->get('per_page', 20)
        );

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'total_items' => Item::active()->count(),
                'low_stock_count' => Item::active()->lowStock()->count(),
                'out_of_stock_count' => Item::active()->outOfStock()->count(),
            ]
        ]);
    }

    /**
     * Get a specific item by ID or SKU
     */
    public function getItem(Request $request, $identifier): JsonResponse
    {
        $item = Item::active()
            ->where('id', $identifier)
            ->orWhere('sku', $identifier)
            ->first();

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found'
            ], 404);
        }

        // Include recent transactions if requested
        $data = $item->toArray();
        if ($request->boolean('include_transactions')) {
            $data['recent_transactions'] = $item->stockTransactions()
                ->with('performedBy:id,name')
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'total_items' => Item::active()->count(),
                'total_value' => Item::active()->sum(DB::raw('quantity_on_hand * unit_price')) ?? 0,
                'low_stock_items' => Item::active()->lowStock()->count(),
                'out_of_stock_items' => Item::active()->outOfStock()->count(),
                'categories' => Item::active()
                    ->select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->get(),
                'top_requested_items' => Item::select('items.*', DB::raw('COALESCE(SUM(purchase_request_items.quantity_requested), 0) as total_requested'))
                    ->leftJoin('purchase_request_items', 'items.id', '=', 'purchase_request_items.item_id')
                    ->leftJoin('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.id')
                    ->where(function($query) {
                        $query->where('purchase_requests.created_at', '>=', now()->subMonths(3))
                              ->orWhereNull('purchase_requests.created_at');
                    })
                    ->groupBy('items.id', 'items.name', 'items.sku', 'items.category', 'items.quantity_on_hand', 'items.unit_price', 'items.created_at', 'items.updated_at', 'items.description', 'items.unit', 'items.reorder_level', 'items.supplier', 'items.location', 'items.is_active', 'items.wikidata_qid', 'items.image_url')
                    ->orderBy('total_requested', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check item availability for purchase requests
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $availability = [];
        foreach ($request->items as $requestItem) {
            $item = Item::find($requestItem['item_id']);
            $availability[] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'sku' => $item->sku,
                'requested_quantity' => $requestItem['quantity'],
                'available_quantity' => $item->quantity_on_hand,
                'can_fulfill' => $item->canFulfill($requestItem['quantity']),
                'is_low_stock' => $item->isLowStock(),
                'is_out_of_stock' => $item->isOutOfStock(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $availability
        ]);
    }

    /**
     * Get stock movement history
     */
    public function getStockMovements(Request $request): JsonResponse
    {
        $query = StockTransaction::with(['item:id,name,sku', 'performedBy:id,name']);

        // Filter by item
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by transaction type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Get categories list
     */
    public function getCategories(): JsonResponse
    {
        $categories = Item::active()
                ->select('category', DB::raw('count(*) as item_count'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get recent purchase requests for API consumers
     */
    public function getPurchaseRequests(Request $request): JsonResponse
    {
        $query = PurchaseRequest::with(['requestedBy:id,name,employee_id', 'items.item:id,name,sku']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by requester
        if ($request->has('requested_by')) {
            $query->where('requested_by', $request->requested_by);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Create a simple item request (for external systems)
     */
    public function createItemRequest(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'requester_email' => 'required|email',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'justification' => 'required|string|max:1000',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'needed_by' => 'sometimes|date|after:today',
            ]);

            // Find user by email
            $user = \App\Models\User::where('email', $request->requester_email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requester not found in system'
                ], 404);
            }

            // Create purchase request
            $purchaseRequest = PurchaseRequest::create([
                'requested_by' => $user->id,
                'justification' => $request->justification,
                'priority' => $request->get('priority', 'medium'),
                'needed_by' => $request->needed_by,
            ]);

            // Add items to request
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['item_id']);
                $purchaseRequest->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity_requested' => $item['quantity'],
                    'unit_price' => $itemModel->unit_price,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase request created successfully',
                'data' => [
                    'request_id' => $purchaseRequest->id,
                    'request_number' => $purchaseRequest->request_number,
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the request'
            ], 500);
        }
    }
}
