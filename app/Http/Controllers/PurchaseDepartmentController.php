<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\StockTransaction;

class PurchaseDepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:approve as purchase department');
    }

    /**
     * Display stock management dashboard
     */
    public function index()
    {
        $items = Item::active()
            ->with(['stockTransactions' => function($query) {
                $query->where('type', 'in')
                      ->where('performed_by', Auth::id())
                      ->latest()
                      ->limit(5);
            }])
            ->paginate(20);

        $statistics = [
            'total_items' => Item::active()->count(),
            'low_stock_items' => Item::active()->lowStock()->count(),
            'out_of_stock_items' => Item::active()->outOfStock()->count(),
            'total_value' => Item::active()->sum(DB::raw('quantity_on_hand * unit_price')) ?? 0,
        ];

        // Chart data for Purchase Department
        $chartData = $this->getPurchaseDepartmentChartData();

        return view('purchase-department.index', compact('items', 'statistics', 'chartData'));
    }

    /**
     * Show stock addition form for specific item
     */
    public function addStockForm(Item $item)
    {
        return view('purchase-department.add-stock', compact('item'));
    }

    /**
     * Add stock to a specific item
     */
    public function addStock(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
            'notes' => 'nullable|string|max:500',
            'supplier' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        try {
            DB::beginTransaction();

            // Add stock to item
            $item->addStockByPurchaseDepartment(
                $request->quantity,
                Auth::user(),
                $request->notes ?? "Stock added by Purchase Department"
            );

            // Update supplier and price if provided
            if ($request->supplier) {
                $item->update(['supplier' => $request->supplier]);
            }

            if ($request->unit_price) {
                $item->update(['unit_price' => $request->unit_price]);
            }

            DB::commit();

            return redirect()->route('purchase-department.index')
                ->with('success', "Successfully added {$request->quantity} units of {$item->name} to stock!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while adding stock: ' . $e->getMessage());
        }
    }

    /**
     * Bulk stock addition form
     */
    public function bulkAddStockForm()
    {
        $items = Item::active()->orderBy('name')->get();
        return view('purchase-department.bulk-add-stock', compact('items'));
    }

    /**
     * Process bulk stock addition
     */
    public function bulkAddStock(Request $request)
    {
        $request->validate([
            'stock_additions' => 'required|array|min:1',
            'stock_additions.*.item_id' => 'required|exists:items,id',
            'stock_additions.*.quantity' => 'required|integer|min:1|max:10000',
            'stock_additions.*.notes' => 'nullable|string|max:500',
            'stock_additions.*.supplier' => 'nullable|string|max:255',
            'stock_additions.*.unit_price' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        try {
            DB::beginTransaction();

            $addedItems = [];
            $totalQuantity = 0;

            foreach ($request->stock_additions as $addition) {
                if (empty($addition['quantity']) || $addition['quantity'] <= 0) {
                    continue;
                }

                $item = Item::findOrFail($addition['item_id']);
                
                // Add stock
                $item->addStockByPurchaseDepartment(
                    $addition['quantity'],
                    Auth::user(),
                    $addition['notes'] ?? "Bulk stock addition by Purchase Department"
                );

                // Update supplier and price if provided
                $updates = [];
                if (!empty($addition['supplier'])) {
                    $updates['supplier'] = $addition['supplier'];
                }
                if (!empty($addition['unit_price'])) {
                    $updates['unit_price'] = $addition['unit_price'];
                }
                
                if (!empty($updates)) {
                    $item->update($updates);
                }

                $addedItems[] = $item->name;
                $totalQuantity += $addition['quantity'];
            }

            DB::commit();

            return redirect()->route('purchase-department.index')
                ->with('success', "Successfully added {$totalQuantity} units across " . count($addedItems) . " items!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while adding stock: ' . $e->getMessage());
        }
    }

    /**
     * View stock history for an item
     */
    public function stockHistory(Item $item)
    {
        $transactions = $item->stockTransactions()
            ->where('type', 'in')
            ->where('performed_by', Auth::id())
            ->latest()
            ->paginate(20);

        return view('purchase-department.stock-history', compact('item', 'transactions'));
    }

    /**
     * Get chart data for Purchase Department dashboard
     */
    private function getPurchaseDepartmentChartData()
    {
        // Stock trends over last 6 months (stock additions by Purchase Department)
        $monthlyStockTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStockTrends[] = [
                'month' => $date->format('M Y'),
                'stock_added' => StockTransaction::where('type', 'in')
                    ->where('reference_type', 'purchase_department')
                    ->whereYear('transaction_date', $date->year)
                    ->whereMonth('transaction_date', $date->month)
                    ->sum('quantity'),
            ];
        }

        // Top categories by stock value
        $categoryData = Item::active()
            ->selectRaw('category, SUM(quantity_on_hand * unit_price) as total_value, COUNT(*) as item_count')
            ->groupBy('category')
            ->orderBy('total_value', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category,
                    'value' => $item->total_value,
                    'count' => $item->item_count
                ];
            });

        // Stock status distribution
        $stockStatusData = [
            'in_stock' => Item::active()->where('quantity_on_hand', '>', 0)->count(),
            'low_stock' => Item::active()->lowStock()->count(),
            'out_of_stock' => Item::active()->outOfStock()->count(),
        ];

        // Recent stock additions by Purchase Department (last 30 days)
        $recentStockAdditions = StockTransaction::where('type', 'in')
            ->where('reference_type', 'purchase_department')
            ->where('transaction_date', '>=', now()->subDays(30))
            ->with(['item'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        return [
            'monthly_stock_trends' => $monthlyStockTrends,
            'categories' => $categoryData,
            'stock_status' => $stockStatusData,
            'recent_additions' => $recentStockAdditions,
        ];
    }

    /**
     * Search items for stock management
     */
    public function search(Request $request)
    {
        $query = Item::active();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'in_stock':
                    $query->where('quantity_on_hand', '>', 0);
                    break;
            }
        }

        $items = $query->paginate(20)->withQueryString();

        $statistics = [
            'total_items' => Item::active()->count(),
            'low_stock_items' => Item::active()->lowStock()->count(),
            'out_of_stock_items' => Item::active()->outOfStock()->count(),
            'total_value' => Item::active()->sum(DB::raw('quantity_on_hand * unit_price')) ?? 0,
        ];

        return view('purchase-department.index', compact('items', 'statistics'));
    }
}