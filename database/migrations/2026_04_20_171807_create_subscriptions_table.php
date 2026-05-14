<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // ✅ بدون constrained() - مجرد رقم عادي
            $table->foreignId('clinic_id')
                  ->nullable()
                  ->comment('سنربطه لاحقاً بـ clinics');
            
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'trial', 'cancelled'])
                  ->default('active');
            $table->unsignedInteger('months_duration');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};