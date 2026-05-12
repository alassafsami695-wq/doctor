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
    Schema::create('partner_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('partner_id')->constrained()->onDelete('cascade');
        $table->enum('type', ['order', 'payment', 'debt']); // نوع العملية
        $table->decimal('amount', 15, 2)->default(0);
        $table->text('note')->nullable(); // الوصف (دين، طلبية، إلخ)
        $table->date('transaction_date');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_logs');
    }
};
