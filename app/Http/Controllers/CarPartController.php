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
                'image_urls' => $part->image_urls ?? [],
            ];
        });

        return response()->json($carParts);
    }

    // حفظ منتج جديد (POST)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'car_model' => 'nullable|string',
            'year' => 'nullable|integer',
            'stock' => 'nullable|integer',
            'image_urls' => 'nullable|array',
        ]);

        $carPart = CarPart::create($validated);

        return response()->json($carPart, 201);
    }

    // دالة البحث (GET)
    public function search(Request $request)
    {
        // ✅ استلام الكلمة المفتاحية من param اسمه term
        $query = $request->query('term');

        if (!$query) {
            return response()->json(['message' => 'يرجى إدخال كلمة البحث'], 400);
        }

        $regex = new Regex($query, 'i');

        $results = CarPart::where('name', 'regex', $regex)
            ->orWhere('description', 'regex', $regex)
            ->orWhere('brand', 'regex', $regex)
            ->get();

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
                'image_urls' => $part->image_urls ?? [],
            ];
        });

        return response()->json($results);
    }

    // دالة رفع الصور (POST)
    public function uploadImages(Request $request)
    {
        if (!$request->hasFile('images')) {
            return response()->json(['error' => 'لم يتم رفع أي صور'], 400);
        }

        $uploadedImageUrls = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('car_parts_images', 'public');
            $uploadedImageUrls[] = asset('storage/' . $path);
        }

        return response()->json([
            'image_urls' => $uploadedImageUrls
        ]);
    }
}
