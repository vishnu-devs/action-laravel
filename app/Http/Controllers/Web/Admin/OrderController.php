<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // Eager load valid relationships: user and products with their company
        $orders = Order::with(['user', 'products.company'])->get();
        return view('dashboard.admin-orders', compact('orders'));
    }
}