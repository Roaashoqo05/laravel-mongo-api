<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarPart;
use MongoDB\BSON\Regex;

class CarPartController extends Controller
{
    // جلب كل المنتجات (GET)
    public function index()
    {
        return response()->json(CarPart::all());
    }

    // حفظ منتج جديد (POST)
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category',
        'brand',
        'car_model',
        'year',
        'stock',
        'image_url',
        ]);

        // إنشاء المنتج
        $carPart = CarPart::create($validated);

        // إرجاع المنتج الجديد
        return response()->json($carPart, 201);
    }
    // دالة البحث (GET)
public function search(Request $request)
{
    $query = $request->input('query');

    if (!$query) {
        return response()->json(['message' => 'يرجى إدخال كلمة البحث'], 400);
    }

    $results = CarPart::where('name', new Regex($query, 'i'))
               ->orWhere('description', new Regex($query, 'i'))
               ->orWhere('brand', new Regex($query, 'i'))
               ->get();

    return response()->json($results);
}
}
