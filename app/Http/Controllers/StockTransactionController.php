<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class StockTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view stock transactions')->only(['index', 'show']);
        $this->middleware('permission:create stock transactions')->only(['create', 'store']);
        $this->middleware('permission:edit stock transactions')->only(['edit', 'update']);
        $this->middleware('permission:delete stock transactions')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = StockTransaction::with(['item', 'performedBy'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->paginate(15)->withQueryString();

        $items = Item::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('stock-transactions.index', compact('transactions', 'items', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $items = Item::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('stock-transactions.create', compact('items', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'quantity_before' => 'required|integer|min:0',
            'quantity_after' => 'required|integer|min:0',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'performed_by' => 'required|exists:users,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:65535',
        ]);

        // Generate transaction number
        $prefix = strtoupper(substr($validated['type'], 0, 2));
        $validated['transaction_number'] = $prefix . '-' . date('Y') . '-' . str_pad(StockTransaction::count() + 1, 6, '0', STR_PAD_LEFT);

        $transaction = StockTransaction::create($validated);

        // Update item stock based on transaction type
        $item = Item::find($validated['item_id']);
        if ($validated['type'] === 'in') {
            $item->quantity_on_hand += $validated['quantity'];
        } elseif ($validated['type'] === 'out') {
            $item->quantity_on_hand = max(0, $item->quantity_on_hand - $validated['quantity']);
        } else { // adjustment
            $item->quantity_on_hand = $validated['quantity_after'];
        }
        $item->save();

        return redirect()->route('stock-transactions.index')
            ->with('success', 'Stock transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransaction $stockTransaction): View
    {
        $stockTransaction->load(['item', 'performedBy']);
        
        return view('stock-transactions.show', compact('stockTransaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockTransaction $stockTransaction): View
    {
        $items = Item::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('stock-transactions.edit', compact('stockTransaction', 'items', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransaction $stockTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'quantity_before' => 'required|integer|min:0',
            'quantity_after' => 'required|integer|min:0',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'performed_by' => 'required|exists:users,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:65535',
        ]);

        $stockTransaction->update($validated);

        return redirect()->route('stock-transactions.index')
            ->with('success', 'Stock transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransaction $stockTransaction): RedirectResponse
    {
        $stockTransaction->delete();

        return redirect()->route('stock-transactions.index')
            ->with('success', 'Stock transaction deleted successfully.');
    }

    /**
     * Get stock movements for API
     */
    public function getStockMovements(Request $request): JsonResponse
    {
        $query = StockTransaction::with(['item', 'performedBy'])
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $transactions = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }
}
