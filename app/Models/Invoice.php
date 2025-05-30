<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Invoice extends Model
{
    protected $collection = 'invoices';
    
    protected $fillable = [
        'invoice_number',
        'date',
        'customer',
        'items',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status'
    ];
    
    protected $casts = [
        'date' => 'datetime',
        'items' => 'array',
        'customer' => 'array',
        'subtotal' => 'float',
        'tax' => 'float',
        'discount' => 'float',
        'total' => 'float'
    ];
}