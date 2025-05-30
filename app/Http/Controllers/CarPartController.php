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
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'car_model' => 'nullable|string',
            'year' => 'nullable|integer',
            'stock' => 'nullable|integer',
            'image_url' => 'nullable|string',

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

        // إنشاء Regex لحساسية غير كبيرة للحروف
        $regex = new Regex($query, 'i');

        $results = CarPart::where('name', 'regex', $regex)
                   ->orWhere('description', 'regex', $regex)
                   ->orWhere('brand', 'regex', $regex)
                   ->get();

        return response()->json($results);
    }
}
