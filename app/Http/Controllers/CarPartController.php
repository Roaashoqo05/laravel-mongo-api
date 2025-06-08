<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarPart;
use MongoDB\BSON\Regex;

class CarPartController extends Controller
{
    // جلب كل المنتجات (GET) مع إرجاع رابط الصورة كامل
    public function index()
    {
        $carParts = CarPart::all()->map(function ($part) {
            return [
                '_id' => $part->_id,
                'name' => $part->name,
                'price' => $part->price,
                'description' => $part->description,
                'category' => $part->category,
                'brand' => $part->brand,
                'car_model' => $part->car_model,
                'year' => $part->year,
                'stock' => $part->stock,
                'image_url' => $part->image ? asset('storage/images/' . $part->image) : null,
            ];
        });

        return response()->json($carParts);
    }

    // حفظ منتج جديد (POST) مع رفع صورة
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // معالجة رفع الصورة إذا موجودة
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/images', $imageName);
            $validated['image'] = $imageName;  // خزن اسم الصورة وليس رابط كامل
        }

        // إنشاء المنتج مع بياناته كاملة
        $carPart = CarPart::create($validated);

        return response()->json($carPart, 201);
    }

    // دالة البحث (GET)
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'يرجى إدخال كلمة البحث'], 400);
        }

        $regex = new Regex($query, 'i');

        $results = CarPart::where('name', 'regex', $regex)
                   ->orWhere('description', 'regex', $regex)
                   ->orWhere('brand', 'regex', $regex)
                   ->get();

        // لو حابب ترجع رابط الصورة كامل في نتائج البحث برضو
        $results = $results->map(function ($part) {
            return [
                '_id' => $part->_id,
                'name' => $part->name,
                'price' => $part->price,
                'description' => $part->description,
                'category' => $part->category,
                'brand' => $part->brand,
                'car_model' => $part->car_model,
                'year' => $part->year,
                'stock' => $part->stock,
                'image_url' => $part->image ? asset('storage/images/' . $part->image) : null,
            ];
        });

        return response()->json($results);
    }
}
