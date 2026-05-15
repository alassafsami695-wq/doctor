<?php

use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DentalChartController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\Api\LabOrderController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - Smart Clinic System V2.0
|--------------------------------------------------------------------------
*/

Route::get('/debug-db', function () {
    return [
        'database' => DB::connection()->getDatabaseName(),
        'host' => DB::connection()->getConfig('host'),
        'tables' => DB::select('SHOW TABLES'),
    ];
});
// مسارات عامة (Public Routes)
 
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/resend-code', [AuthController::class, 'resendCode']);

// 2. مسارات محمية بالتوثيق فقط
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});


/**
 * 3. مسارات "لوحة تحكم العيادة" (الطبيب والموظفين)
 * تم تحويل كافة مسارات التحديث إلى POST بناءً على طلبك
 */
Route::middleware(['auth:sanctum', 'check.subscription'])->group(function () {

Route::get('/staff/permissions', [StaffController::class, 'permissions']);
Route::get('/staff', [StaffController::class, 'index']);
Route::post('/staff/create', [StaffController::class, 'store']);
Route::post('/staff/{id}', [StaffController::class, 'update']);
Route::delete('/staff/{id}', [StaffController::class, 'destroy']);



Route::prefix('doctor/partners')->group(function () {
        Route::get('/', [PartnerController::class, 'index']);      // جلب الكل
        Route::post('/', [PartnerController::class, 'store']);     // إضافة جهة
        Route::get('/{id}', [PartnerController::class, 'show']);   // تفاصيل جهة مع سجلاتها
        Route::post('/{id}/logs', [PartnerController::class, 'storeLog']); // إضافة حركة مادية
    });
    
    // --- الإحصائيات (Dashboard) ---
    Route::get('/stats/clinic', [StatsController::class, 'getClinicStats']);

    // --- إدارة المرضى (Patients) ---

  // 1. مسار الرفع العام (يستخدم patient_id من الـ Body)
Route::post('/patients/upload-panorama', [PatientController::class, 'updateInfo']);

// 2. المسارات الأخرى
Route::get('/patients', [PatientController::class, 'index']);
Route::post('/patients', [PatientController::class, 'store']);
Route::get('/patients/{id}', [PatientController::class, 'show']);
Route::post('/patients/{id}', [PatientController::class, 'update']);

// 3. مسار التحديث الخاص (يستخدم id من الرابط)
Route::post('/patients/{id}/medical-info', [PatientController::class, 'updateInfo']);

Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
    // --- إدارة المواعيد (Appointments) ---
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::post('/appointments/{id}/update', [AppointmentController::class, 'update']); // تحديث باستخدام POST
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    // --- العمليات الطبية (Procedures & Dental Chart) ---
    Route::get('/procedures', [ProcedureController::class, 'index']);
    Route::post('/procedures', [ProcedureController::class, 'store']);
    Route::get('/procedures/{id}', [ProcedureController::class, 'show']);
    Route::post('/procedures/{id}', [ProcedureController::class, 'update']); // تحديث باستخدام POST
    Route::delete('/procedures/{id}', [ProcedureController::class, 'destroy']);
    
    Route::post('/dental-charts', [DentalChartController::class, 'store']);
    Route::get('/patients/{patient}/history', [DentalChartController::class, 'getPatientHistory']);
    
    // --- إدارة المختبرات (Lab Orders) ---
    Route::get('/lab-orders', [LabOrderController::class, 'index']);
    Route::post('/lab-orders', [LabOrderController::class, 'store']);
    Route::get('/lab-orders/{id}', [LabOrderController::class, 'show']);
    Route::post('/lab-orders/{id}', [LabOrderController::class, 'update']); // تحديث باستخدام POST
    Route::delete('/lab-orders/{id}', [LabOrderController::class, 'destroy']);
    Route::post('/lab-orders/{order}/status', [LabOrderController::class, 'updateStatus']);
    
    // --- المصاريف (Expenses) ---
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
    Route::post('/expenses/{id}', [ExpenseController::class, 'update']); // تحديث باستخدام POST
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);

    // --- الفواتير والمدفوعات (Finance) ---
    Route::get('/invoices', [InvoiceController::class, 'index']); // جلب كل الفواتير
    // ✅ المسار الصحيح لإضافة فاتورة
Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']); // جلب فاتورة مريض محددة
    Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'addPayment']);});

    Route::get('/statistics', [StatisticsController::class, 'getFinancialStats']);




/**
 * 4. مسارات "لوحة تحكم السوبر أدمن" (Super Admin Dashboard)
 */
Route::middleware(['auth:sanctum', 'is.superadmin'])->group(function () {
    
    Route::get('/stats/superadmin', [StatsController::class, 'getSuperAdminStats']);
    Route::post('/admin/activate-subscription', [SubscriptionController::class, 'activate']);
    //Route::get('/admin/stats', [AdminDashboardController::class, 'getStats']);
    Route::get('/admin/stats', [StatsController::class, 'getSuperAdminStats']);
    Route::get('/admin/users', function() {
        return \App\Models\User::where('role', 'dentist')->with('subscription')->get();
    });
    
    // Route::get('/admin/users', function() {
    //     return \App\Models\User::where('role', 'dentist')->get();
    // });
});