<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الإجراءات
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->decimal('default_price', 10, 2)->default(0); 
            $table->timestamps();
        });

        // 2. جدول مخطط الأسنان (تم تصحيح الأخطاء هنا)
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('procedure_id')
                ->nullable() 
                ->constrained('procedures')
                ->onDelete('set null');

            $table->integer('tooth_number'); 
            $table->text('doctor_notes')->nullable();
            $table->enum('state', ['planned', 'completed'])->default('completed');
            $table->decimal('final_price', 10, 2)->default(0); 
            $table->decimal('paid_now', 10, 2)->default(0); // تم وضعه بشكل طبيعي بدون after
            $table->string('currency', 10)->default('USD'); // تم تعريفه مباشرة بدون change
            $table->decimal('exchange_rate', 15, 2)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_charts');
        Schema::dropIfExists('procedures');
    }
};