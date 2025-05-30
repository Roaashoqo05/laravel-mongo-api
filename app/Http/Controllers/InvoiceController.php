<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CarPart;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\InvoiceController;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        // التحقق من البيانات مع رسائل أخطاء عربية
        $validatedData = $request->validate([
            'customer.name' => 'required|string|max:255',
            'customer.phone' => 'required|string|max:20|regex:/^[0-9]+$/',
            'customer.address' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.part_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!ObjectId::isValid($value)) {
                        $fail('معرف القطعة غير صالح (يجب أن يكون 24 حرفاً)');
                    }
                }
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'tax' => 'numeric|min:0|max:10000',
            'discount' => 'numeric|min:0|max:10000',
            'notes' => 'nullable|string|max:1000'
        ], [
            'customer.name.required' => 'اسم العميل مطلوب',
            'items.*.part_id.required' => 'معرف القطعة مطلوب',
            'items.*.quantity.min' => 'الكمية يجب أن تكون على الأقل 1'
        ]);

        // بدء معاملة قاعدة البيانات
        DB::beginTransaction();

        try {
            $items = [];
            $subtotal = 0;
            $errors = [];

            // معالجة كل عنصر في الفاتورة
            foreach ($validatedData['items'] as $index => $item) {
                try {
                    $objectId = new ObjectId($item['part_id']);
                    $carPart = CarPart::find($objectId);

                    if (!$carPart) {
                        $errors[] = "المنتج غير موجود للمعرف: {$item['part_id']}";
                        continue;
                    }

                    if ($carPart->stock < $item['quantity']) {
                        $errors[] = "الكمية المطلوبة غير متوفرة للمنتج: {$carPart->name} (المتوفر: {$carPart->stock})";
                        continue;
                    }

                    $itemTotal = $carPart->price * $item['quantity'];

                    $items[] = [
                        'part_id' => $carPart->_id,
                        'part_name' => $carPart->name,
                        'brand' => $carPart->brand,
                        'car_model' => $carPart->car_model,
                        'year' => $carPart->year,
                        'quantity' => $item['quantity'],
                        'unit_price' => $carPart->price,
                        'total' => $itemTotal,
                        'original_stock' => $carPart->stock // لحفظ السجل الأصلي
                    ];

                    $subtotal += $itemTotal;

                } catch (\Exception $e) {
                    Log::error("Invoice item error: " . $e->getMessage());
                    $errors[] = "خطأ في معالجة المنتج رقم " . ($index + 1);
                }
            }

            // إذا كان هناك أخطاء
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'أخطاء في العناصر',
                    'errors' => $errors
                ], 422);
            }

            // حساب المبالغ النهائية
            $taxAmount = $validatedData['tax'] ?? 0;
            $discountAmount = $validatedData['discount'] ?? 0;
            $total = $subtotal + $taxAmount - $discountAmount;

            // إنشاء الفاتورة
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'date' => now(),
                'customer' => [
                    'name' => $validatedData['customer']['name'],
                    'phone' => $validatedData['customer']['phone'],
                    'address' => $validatedData['customer']['address'] ?? null
                ],
                'items' => $items,
                'subtotal' => $subtotal,
                'tax' => $taxAmount,
                'discount' => $discountAmount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validatedData['notes'] ?? null,
                'created_by' => auth()->id() ?? null // إذا كان لديك نظام مصادقة
            ]);

            // تحديث المخزون
            foreach ($items as $item) {
                CarPart::where('_id', $item['part_id'])
                      ->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->_id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'message' => 'تم إنشاء الفاتورة بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Invoice creation failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الفاتورة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('invoice_number', 'desc')->first();
        $lastNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, 4) : 0;
        return 'INV-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        try {
            $invoice = Invoice::with(['creator:id,name'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'invoice' => $invoice
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'الفاتورة غير موجودة'
            ], 404);
        }
    }

    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->when($request->date_from, fn($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('date', '<=', $request->date_to))
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'invoices' => $invoices
        ]);
    }
}