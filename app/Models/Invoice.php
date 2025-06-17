<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Invoice extends Model
{
    // استخدام اتصال MongoDB
    protected $connection = 'mongodb';

    // اسم الكوليكشن في MongoDB
    protected $collection = 'invoices';

    // الحقول المسموح تعبئتها عبر Mass Assignment
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

    // تحويل نوع الحقول تلقائياً
    protected $casts = [
        'date' => 'datetime',
        'items' => 'array',
        'customer' => 'array',
        'subtotal' => 'float',
        'tax' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'created_by' => 'array',  // مهم لأن created_by تخزن بيانات مثل id وname
    ];

    /**
     * علاقة الفاتورة بالمستخدم الذي أنشأها.
     * 
     * ملاحظة: لأن created_by حقل array (مثل ['id' => '...', 'name' => '...'])
     * لا يمكن استخدام belongsTo مباشرة بدون تعديل، 
     * لذلك يمكننا استدعاء المستخدم عبر id يدوياً أو إنشاء علاقة مخصصة.
     */
    public function user()
    {
        // يفترض أن created_by يحتوي ['id' => ObjectId أو string]
        return $this->belongsTo(User::class, 'created_by.id', '_id');
    }
}
