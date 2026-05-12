<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'total_amount' => 'required|numeric',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            // حساب المبلغ المتبقي (يمكن أن يكون سالباً إذا دفع المريض أكثر)
            $remaining = $request->total_amount - $request->paid_amount;

            $invoice = Invoice::create([
                'patient_id' => $request->patient_id,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount,
                'discount' => 0,
                'remaining_amount' => $remaining,
                // إذا كان المدفوع يساوي أو أكبر من الإجمالي تصبح الحالة "مدفوعة"
                'status' => $request->paid_amount >= $request->total_amount ? 'paid' : 'partially_paid',
            ]);

            if ($request->paid_amount > 0) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $request->paid_amount,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'payment_date' => now(),
                    'notes' => $request->notes ?? 'دفعة افتتاحية'
                ]);
            }
            return $invoice->load('patient');
        });
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate(['amount' => 'required|numeric|min:0']);

        return DB::transaction(function () use ($request, $invoice) {
            // 1. تسجيل الدفعة الجديدة
            Payment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_date'   => now(),
                'notes'          => $request->notes
            ]);

            // 2. تحديث إجمالي المبلغ المدفوع في الفاتورة
            $invoice->increment('paid_amount', $request->amount);
            
            /**
             * 3. تحديث المبلغ المتبقي بناءً على المنطق الجديد:
             * (المدفوع - الإجمالي)
             * - إذا كان الناتج موجباً (+) : المريض دفع زيادة وله رصيد.
             * - إذا كان الناتج سالباً (-) : المريض لا يزال عليه دين.
             */
            $newRemaining = (float)$invoice->paid_amount - (float)$invoice->total_amount;

            $invoice->update([
                'remaining_amount' => $newRemaining,
                // الحالة تصبح "مدفوعة" إذا كان الرصيد صفراً أو موجباً (دفع زيادة)
                'status'           => $newRemaining >= 0 ? 'paid' : 'partially_paid'
            ]);

            return $invoice;
        });
    }

    public function index(Request $request)
{
    $query = Invoice::query();

    // فلترة بناءً على الفترة الزمنية
    if ($request->has('period')) {
        switch ($request->period) {
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                      ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('created_at', Carbon::now()->subYear()->year);
                break;
        }
    }

    $invoices = $query->with('patient')->latest()->get();
    return response()->json($invoices);
}

    public function show($id) {
        return Invoice::with(['patient', 'payments'])->findOrFail($id);
    }

    public function getAllTransactions() {
    $invoices = Invoice::with('patient')->get()->map(function($item) {
        return [
            'id' => $item->id,
            'type' => 'income',
            'category' => 'كشفية/علاج',
            'amount' => $item->total_amount,
            'description' => $item->patient->name ?? 'مريض',
            'date' => $item->created_at->format('Y-m-d'),
        ];
    });

    $expenses = Expense::all()->map(function($item) {
        return [
            'id' => $item->id,
            'type' => 'expense',
            'category' => $item->category,
            'amount' => $item->amount,
            'description' => $item->description,
            'date' => $item->expense_date,
        ];
    });

    return $invoices->concat($expenses)->sortByDesc('date')->values();
}
}