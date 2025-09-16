<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Dashboard data based on user role
        $data = [
            'user' => $user,
        ];

        if ($user->isEmployee()) {
            $data = array_merge($data, $this->getEmployeeDashboardData($user));
        } elseif ($user->isApprover()) {
            $data = array_merge($data, $this->getApproverDashboardData($user));
        } elseif ($user->isStockKeeper()) {
            $data = array_merge($data, $this->getStockKeeperDashboardData($user));
        } elseif ($user->isAdministrator()) {
            $data = array_merge($data, $this->getAdministratorDashboardData($user));
        }

        return view('dashboard', $data);
    }

    private function getEmployeeDashboardData($user)
    {
        return [
            'my_requests' => PurchaseRequest::where('requested_by', $user->id)
                ->with(['items.item'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'pending_requests_count' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_requests_count' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'approved')
                ->count(),
            'recent_items' => Item::active()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    private function getApproverDashboardData($user)
    {
        return [
            'pending_requests' => PurchaseRequest::pending()
                ->with(['requestedBy', 'items.item'])
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get(),
            'pending_requests_count' => PurchaseRequest::pending()->count(),
            'urgent_requests' => PurchaseRequest::pending()
                ->urgent()
                ->with(['requestedBy', 'items.item'])
                ->orderBy('created_at', 'asc')
                ->get(),
            'overdue_requests' => PurchaseRequest::overdue()
                ->with(['requestedBy', 'items.item'])
                ->orderBy('needed_by', 'asc')
                ->get(),
            'recent_approvals' => PurchaseRequest::where('approved_by', $user->id)
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    private function getStockKeeperDashboardData($user)
    {
        return [
            'low_stock_items' => Item::active()
                ->lowStock()
                ->orderBy('quantity_on_hand', 'asc')
                ->get(),
            'out_of_stock_items' => Item::active()
                ->outOfStock()
                ->get(),
            'approved_requests' => PurchaseRequest::approved()
                ->with(['requestedBy', 'items.item'])
                ->orderBy('approved_at', 'asc')
                ->limit(10)
                ->get(),
            'recent_transactions' => StockTransaction::with(['item', 'performedBy'])
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get(),
            'total_items' => Item::active()->count(),
            'low_stock_count' => Item::active()->lowStock()->count(),
            'out_of_stock_count' => Item::active()->outOfStock()->count(),
        ];
    }

    private function getAdministratorDashboardData($user)
    {
        $totalRequests = PurchaseRequest::count();
        $pendingRequests = PurchaseRequest::pending()->count();
        $approvedRequests = PurchaseRequest::approved()->count();
        $fulfilledRequests = PurchaseRequest::fulfilled()->count();

        return [
            'stats' => [
                'total_items' => Item::count(),
                'active_items' => Item::active()->count(),
                'low_stock_items' => Item::active()->lowStock()->count(),
                'out_of_stock_items' => Item::active()->outOfStock()->count(),
                'total_requests' => $totalRequests,
                'pending_requests' => $pendingRequests,
                'approved_requests' => $approvedRequests,
                'fulfilled_requests' => $fulfilledRequests,
                'total_users' => \App\Models\User::where('is_active', true)->count(),
            ],
            'recent_requests' => PurchaseRequest::with(['requestedBy', 'approvedBy'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'urgent_requests' => PurchaseRequest::pending()
                ->urgent()
                ->with(['requestedBy'])
                ->orderBy('created_at', 'asc')
                ->get(),
            'overdue_requests' => PurchaseRequest::overdue()
                ->with(['requestedBy'])
                ->orderBy('needed_by', 'asc')
                ->get(),
            'low_stock_items' => Item::active()
                ->lowStock()
                ->orderBy('quantity_on_hand', 'asc')
                ->limit(10)
                ->get(),
            'recent_transactions' => StockTransaction::with(['item', 'performedBy'])
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get(),
        ];
    }
}
