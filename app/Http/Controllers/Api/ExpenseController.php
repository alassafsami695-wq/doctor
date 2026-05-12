<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // جلب قائمة المصاريف لعرضها في الجدول
    public function index()
    {
        return response()->json(Expense::orderBy('expense_date', 'desc')->get());
    }

    // حفظ مصروف جديد (رواتب، مواد، إلخ)
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense = Expense::create($request->all());

        return response()->json([
            'message' => 'تم تسجيل المصروف بنجاح',
            'data' => $expense
        ], 201);
    }
    
    // حذف مصروف
    public function destroy($id)
    {
        Expense::destroy($id);
        return response()->json(['message' => 'تم الحذف']);
    }
}