<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CarPart;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'items.*.part_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        try {
                            new ObjectId($value);
                        } catch (\Exception $e) {
                            $fail("المعرف $attribute غير صالح.");
                        }
                    }
                ],
                'items.*.quantity' => 'required|integer|min:1',
                'tax' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'customer.name' => 'required|string',
                'customer.phone' => 'required|string',
                'customer.address' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $items = [];
            $subtotal = 0;
            $errors = [];

            foreach ($validatedData['items'] as $index => $item) {
                try {
                    $objectId = new ObjectId($item['part_id']);
                    $carPart = CarPart::where('_id', $objectId)->first();

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
                        'original_stock' => $carPart->stock,
                        'image_urls' => $carPart->image_urls ?? [],
                    ];

                    $subtotal += $itemTotal;

                } catch (\Exception $e) {
                    Log::error("Invoice item error: " . $e->getMessage());
                    $errors[] = "خطأ في معالجة المنتج رقم " . ($index + 1);
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'أخطاء في العناصر',
                    'errors' => $errors
                ], 422);
            }

            $taxAmount = $validatedData['tax'] ?? 0;
            $discountAmount = $validatedData['discount'] ?? 0;
            $total = $subtotal + $taxAmount - $discountAmount;

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
                'created_by' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name ?? null,
                ],
            ]);

            foreach ($items as $item) {
                CarPart::where('_id', $item['part_id'])->decrement('stock', $item['quantity']);
            }

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->_id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'message' => 'تم إنشاء الفاتورة بنجاح'
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Invoice creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الفاتورة. يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }

    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('invoice_number', 'desc')->first();
        $lastNumber = 0;

        if ($lastInvoice && preg_match('/INV-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return 'INV-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

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
