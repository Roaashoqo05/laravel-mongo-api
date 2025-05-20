<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class CarPart extends Model
{
    protected $collection = 'car_parts'; // اسم الكولكشن في MongoDB (مكافئ للجدول في MySQL)
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'brand',
        'car_model',
        'year',
        'stock',
        'image_url',
    ];
    
    // لا حاجة لـ $table أو timestamps إذا كنت تريد تعطيلها
    public $timestamps = true; // تفعيل created_at و updated_at (اختياري)
}