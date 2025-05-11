<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return view('product-form');
    }

    public function store(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);
    
        // Store the product
        return "Product added to MongoDB!";
    }

    public function getProducts() {
        return Product::all();
    }
        // Redirect back with success message
}
