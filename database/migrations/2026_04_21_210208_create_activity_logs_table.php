<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // من قام بالفعل
            $table->string('action'); // Created, Updated, Deleted
            $table->string('model_type'); // Patient, Invoice, Appointment
            $table->unsignedBigInteger('model_id'); // معرف السجل الذي تم عليه الفعل
            $table->text('description'); // تفاصيل إضافية
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};