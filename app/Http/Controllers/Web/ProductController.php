<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::with('user');
        
        // If user is vendor, only show their products
        if (auth()->user()->hasRole('vendor')) {
            $query->where('user_id', auth()->id());
        }
        
        $products = $query->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->user_id = auth()->id();
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        // Check if user can edit this product
        if (auth()->user()->hasRole('vendor') && $product->user_id !== auth()->id()) {
            return redirect()->route('products.index')
                ->with('error', 'You are not authorized to edit this product');
        }

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        // Check if user can edit this product
        if (auth()->user()->hasRole('vendor') && $product->user_id !== auth()->id()) {
            return redirect()->route('products.index')
                ->with('error', 'You are not authorized to edit this product');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);

        $product->update($request->only(['name', 'price']));

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        // Check if user can delete this product
        if (auth()->user()->hasRole('vendor') && $product->user_id !== auth()->id()) {
            return redirect()->route('products.index')
                ->with('error', 'You are not authorized to delete this product');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }
}