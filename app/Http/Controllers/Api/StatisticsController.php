<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Expense;


class StatisticsController extends Controller
{
    public function getFinancialStats()
{
    // مجموع ما دفعه المرضى فعلياً
    $totalIncome = Invoice::sum('paid_amount');

    // مجموع المصاريف التي خرجت من العيادة
    $totalExpenses = Expense::sum('amount');

    // صافي الربح (رصيد الدكتور)
    $doctorBalance = $totalIncome - $totalExpenses;

    return response()->json([
        'total_income' => $totalIncome,
        'total_expenses' => $totalExpenses,
        'doctor_balance' => $doctorBalance,
    ]);
}
}
