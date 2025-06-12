<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class CarPart extends Model
{
    protected $collection = 'car_parts';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'brand',
        'car_model',
        'year',
        'stock',
        'image_urls',  // هنا مصفوفة روابط
    ];

    public $timestamps = true;
}
