<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('lab_name');
            $table->string('work_type'); // مثال: تاج زيركون، جسر
            $table->integer('tooth_number')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->date('sent_date');
            $table->date('expected_date')->nullable();
            $table->enum('status', ['ordered', 'received', 'fitted'])->default('ordered');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};