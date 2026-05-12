<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->unique();
            $table->string('national_id')->nullable()->unique();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('blood_type')->nullable(); 
            
            // الترتيب هنا سيحدد مكان العمود في قاعدة البيانات تلقائياً
            $table->text('medical_history')->nullable(); 
            $table->text('current_medications')->nullable(); // سيظهر بعد السيرة المرضية
            
            $table->text('allergies')->nullable(); 
            $table->string('panorama_image')->nullable(); // سيظهر بعد الحساسية
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};