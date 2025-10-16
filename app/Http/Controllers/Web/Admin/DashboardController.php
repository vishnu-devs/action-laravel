<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_vendors' => User::role('vendor')->count(),
            'total_customers' => User::role('customer')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 0)->count(),
            'pending_vendor_requests' => VendorRequest::where('status', 0)->count(),
            'recent_orders' => Order::with(['product', 'buyer', 'vendor'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_products' => Product::with('user')
                ->latest()
                ->take(5)
                ->get(),
            'recent_vendor_requests' => VendorRequest::with('user')
                ->where('status', 0)
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}