<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['trial', 'active', 'suspended'])->default('trial');
            $table->decimal('total_paid', 12, 2)->default(0.00); // رصيد ما دفعه صاحب العيادة للآدمن
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clinics');
    }
};