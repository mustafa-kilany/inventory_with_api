<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the purchase requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseRequest::with(['requestedBy', 'approvedBy', 'fulfilledBy', 'items.item']);

        // Filter based on user role
        if ($user->hasRole('employee')) {
            // Employees can only see their own requests
            $query->where('requested_by', $user->id);
        } elseif ($user->hasRole('approver')) {
            // Approvers can see pending requests and those they've approved/rejected
            $query->where(function($q) use ($user) {
                $q->where('status', 'pending')
                  ->orWhere('approved_by', $user->id);
            });
        } elseif ($user->hasRole('stock_keeper')) {
            // Stock keepers can see approved requests and those they've fulfilled
            $query->where(function($q) use ($user) {
                $q->where('status', 'approved')
                  ->orWhere('fulfilled_by', $user->id);
            });
        }
        // Administrators can see all requests (no additional filtering)

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $purchaseRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('purchase-requests.index', compact('purchaseRequests'));
    }

    /**
     * Show the form for creating a new purchase request
     */
    public function create()
    {
        $items = Item::active()->orderBy('name')->get();
        return view('purchase-requests.create', compact('items'));
    }

    /**
     * Store a newly created purchase request
     */
    public function store(Request $request)
    {
        $request->validate([
            'justification' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,urgent',
            'needed_by' => 'nullable|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create the purchase request
            $purchaseRequest = PurchaseRequest::create([
                'requested_by' => Auth::id(),
                'justification' => $request->justification,
                'priority' => $request->priority,
                'needed_by' => $request->needed_by,
                'status' => 'pending',
            ]);

            $estimatedTotal = 0;

            // Add items to the request
            foreach ($request->items as $itemData) {
                $item = Item::find($itemData['item_id']);
                
                $purchaseRequestItem = PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity'],
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->unit_price * $itemData['quantity'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $estimatedTotal += $purchaseRequestItem->total_price;
            }

            // Update estimated total
            $purchaseRequest->update(['estimated_total' => $estimatedTotal]);
            
            // Initialize the workflow
            $purchaseRequest->initializeWorkflow();

            DB::commit();

            return redirect()->route('purchase-requests.index')
                ->with('success', 'Purchase request created successfully! Request number: ' . $purchaseRequest->request_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while creating the purchase request.');
        }
    }

    /**
     * Display the specified purchase request
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        // Check if user can view this request
        $user = Auth::user();
        if ($user->hasRole('employee') && $purchaseRequest->requested_by !== $user->id) {
            abort(403, 'You can only view your own purchase requests.');
        }

        $purchaseRequest->load(['requestedBy', 'approvedBy', 'fulfilledBy', 'items.item']);
        
        return view('purchase-requests.show', compact('purchaseRequest'));
    }

    /**
     * Show the form for editing the specified purchase request
     */
    public function edit(PurchaseRequest $purchaseRequest)
    {
        // Only allow editing pending requests by the requester
        if ($purchaseRequest->status !== 'pending' || $purchaseRequest->requested_by !== Auth::id()) {
            abort(403, 'You can only edit your own pending purchase requests.');
        }

        $items = Item::active()->orderBy('name')->get();
        $purchaseRequest->load('items.item');
        
        return view('purchase-requests.edit', compact('purchaseRequest', 'items'));
    }

    /**
     * Update the specified purchase request
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Only allow updating pending requests by the requester
        if ($purchaseRequest->status !== 'pending' || $purchaseRequest->requested_by !== Auth::id()) {
            abort(403, 'You can only update your own pending purchase requests.');
        }

        $request->validate([
            'justification' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,urgent',
            'needed_by' => 'nullable|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update the purchase request
            $purchaseRequest->update([
                'justification' => $request->justification,
                'priority' => $request->priority,
                'needed_by' => $request->needed_by,
            ]);

            // Remove existing items
            $purchaseRequest->items()->delete();

            $estimatedTotal = 0;

            // Add updated items
            foreach ($request->items as $itemData) {
                $item = Item::find($itemData['item_id']);
                
                $purchaseRequestItem = PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity'],
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->unit_price * $itemData['quantity'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $estimatedTotal += $purchaseRequestItem->total_price;
            }

            // Update estimated total
            $purchaseRequest->update(['estimated_total' => $estimatedTotal]);

            DB::commit();

            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('success', 'Purchase request updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while updating the purchase request.');
        }
    }

    /**
     * Remove the specified purchase request
     */
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        // Only allow deleting pending requests by the requester
        if ($purchaseRequest->status !== 'pending' || $purchaseRequest->requested_by !== Auth::id()) {
            abort(403, 'You can only delete your own pending purchase requests.');
        }

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase request deleted successfully!');
    }

    /**
     * Approve a purchase request (for approvers)
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->can('approve purchase requests')) {
            abort(403, 'You do not have permission to approve purchase requests.');
        }

        if (!$purchaseRequest->canBeApproved()) {
            return back()->with('error', 'This purchase request cannot be approved.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $purchaseRequest->approve(Auth::user(), $request->approval_notes);

        return back()->with('success', 'Purchase request approved successfully!');
    }

    /**
     * Reject a purchase request (for approvers)
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->can('approve purchase requests')) {
            abort(403, 'You do not have permission to reject purchase requests.');
        }

        if (!$purchaseRequest->canBeRejected()) {
            return back()->with('error', 'This purchase request cannot be rejected.');
        }

        $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $purchaseRequest->reject(Auth::user(), $request->approval_notes);

        return back()->with('success', 'Purchase request rejected.');
    }

    /**
     * Fulfill a purchase request (for stock keepers)
     */
    public function fulfill(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->can('fulfill purchase requests')) {
            abort(403, 'You do not have permission to fulfill purchase requests.');
        }

        if (!$purchaseRequest->canBeFulfilled()) {
            return back()->with('error', 'This purchase request cannot be fulfilled.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.quantity_fulfilled' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $actualTotal = 0;

            foreach ($request->items as $itemId => $itemData) {
                $purchaseRequestItem = PurchaseRequestItem::where('purchase_request_id', $purchaseRequest->id)
                    ->where('item_id', $itemId)
                    ->first();

                if ($purchaseRequestItem) {
                    $quantityFulfilled = $itemData['quantity_fulfilled'];
                    
                    $purchaseRequestItem->update([
                        'quantity_fulfilled' => $quantityFulfilled
                    ]);

                    if ($quantityFulfilled > 0) {
                        // Update item stock
                        $item = $purchaseRequestItem->item;
                        $item->updateStock($quantityFulfilled, 'subtract');

                        // Create stock transaction
                        StockTransaction::createStockOut(
                            $item,
                            $quantityFulfilled,
                            Auth::user(),
                            'purchase_request',
                            $purchaseRequest->id,
                            "Fulfilled purchase request: {$purchaseRequest->request_number}"
                        );

                        $actualTotal += $purchaseRequestItem->unit_price * $quantityFulfilled;
                    }
                }
            }

            // Update purchase request
            $purchaseRequest->update([
                'actual_total' => $actualTotal
            ]);

            $purchaseRequest->fulfill(Auth::user());

            DB::commit();

            return back()->with('success', 'Purchase request fulfilled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while fulfilling the purchase request.');
        }
    }

    // Enhanced Workflow Approval Methods

    /**
     * Approve by Department Head
     */
    public function approveDepartmentHead(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->canApproveAsDepartmentHead()) {
            abort(403, 'You do not have permission to approve as department head.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($purchaseRequest->approveByDepartmentHead(Auth::user(), $request->approval_notes)) {
            return back()->with('success', 'Request approved as Department Head! Moving to next approval step.');
        }

        return back()->with('error', 'Unable to approve at this stage.');
    }

    /**
     * Approve by Manager
     */
    public function approveManager(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->canApproveAsManager()) {
            abort(403, 'You do not have permission to approve as manager.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($purchaseRequest->approveByManager(Auth::user(), $request->approval_notes)) {
            return back()->with('success', 'Request approved as Manager! Moving to Purchase Department.');
        }

        return back()->with('error', 'Unable to approve at this stage.');
    }

    /**
     * Approve by Purchase Department
     */
    public function approvePurchaseDepartment(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->canApproveAsPurchaseDepartment()) {
            abort(403, 'You do not have permission to approve as purchase department.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($purchaseRequest->approveByPurchaseDepartment(Auth::user(), $request->approval_notes)) {
            return back()->with('success', 'Request approved by Purchase Department! Moving to next approval step.');
        }

        return back()->with('error', 'Unable to approve at this stage.');
    }

    /**
     * Approve by Stock Keeper (for in-stock items)
     */
    public function approveStockKeeper(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->canManageStock()) {
            abort(403, 'You do not have permission to approve as stock keeper.');
        }

        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($purchaseRequest->approveByStockKeeper(Auth::user(), $request->approval_notes)) {
            return back()->with('success', 'Request approved by Stock Keeper! Ready for fulfillment.');
        }

        return back()->with('error', 'Unable to approve at this stage.');
    }

    /**
     * Add stock to items by Purchase Department
     */
    public function addStockToItems(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (!Auth::user()->canApproveAsPurchaseDepartment()) {
            abort(403, 'You do not have permission to add stock as purchase department.');
        }

        $request->validate([
            'stock_additions' => 'required|array',
            'stock_additions.*.item_id' => 'required|exists:items,id',
            'stock_additions.*.quantity' => 'required|integer|min:1',
            'stock_additions.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->stock_additions as $addition) {
                $item = Item::findOrFail($addition['item_id']);
                $item->addStockByPurchaseDepartment(
                    $addition['quantity'],
                    Auth::user(),
                    $addition['notes'] ?? "Stock added for purchase request: {$purchaseRequest->request_number}"
                );
            }

            DB::commit();

            return back()->with('success', 'Stock successfully added to items! You can now approve the request.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while adding stock: ' . $e->getMessage());
        }
    }

    /**
     * Reject at any workflow stage
     */
    public function rejectWorkflow(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($purchaseRequest->rejectWorkflow(Auth::user(), $request->rejection_reason)) {
            return back()->with('success', 'Purchase request has been rejected.');
        }

        return back()->with('error', 'Unable to reject at this stage.');
    }
}