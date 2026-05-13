<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function index()
    {
        return response()->json(Procedure::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:procedures',
            'default_price' => 'required|numeric|min:0',
        ]);

        $procedure = Procedure::create($validated);
        return response()->json(['message' => 'تم إضافة الخدمة بنجاح', 'procedure' => $procedure]);
    }

    public function update(Request $request, Procedure $procedure)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:procedures,name,' . $procedure->id,
            'default_price' => 'required|numeric|min:0',
        ]);

        $procedure->update($validated);
        return response()->json(['message' => 'تم تحديث الخدمة']);
    }

    public function destroy(Procedure $procedure)
    {
        $procedure->delete();
        return response()->json(['message' => 'تم حذف الخدمة']);
    }
}