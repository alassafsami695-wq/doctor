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
            Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // الأدمن أو صاحب العيادة
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', ['active', 'expired', 'trial'])->default('active');
            $table->integer('months_duration'); // عدد الأشهر المحجوزة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
