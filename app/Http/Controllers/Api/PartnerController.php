<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    // جلب كل الجهات الخاصة بالطبيب الحالي
    public function index()
    {
        return Partner::where('user_id', Auth::id())->get();
    }

    // إضافة جهة جديدة
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:lab,company',
            'contact' => 'nullable|string'
        ]);

        $data['user_id'] = Auth::id();
        return Partner::create($data);
    }

    // جلب تفاصيل جهة معينة مع سجلاتها
    public function show($id)
    {
        return Partner::with('logs')->where('user_id', Auth::id())->findOrFail($id);
    }

    // إضافة سجل (وصف/دين/طلبية)
    public function storeLog(Request $request, $id)
    {
        $partner = Partner::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'type' => 'required|in:order,payment,debt',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        return $partner->logs()->create([
            'type' => $data['type'],
            'amount' => $data['amount'] ?? 0,
            'note' => $data['note'],
            'transaction_date' => now(),
        ]);
    }
}