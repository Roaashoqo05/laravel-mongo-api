<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarPart;

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
}
