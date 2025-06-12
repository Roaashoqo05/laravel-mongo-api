<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Invoice extends Model
{
    protected $connection = 'mongodb';
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
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'datetime',
        'items' => 'array',
        'customer' => 'array',
        'subtotal' => 'float',
        'tax' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    /**
     * العلاقة مع اليوزر
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', '_id');
    }
}
