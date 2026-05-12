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
        Schema::table('dental_charts', function (Blueprint $table) {
            // جعل الحقل وحقل السعر يقبلان قيم فارغة أو صفرية
            $table->foreignId('procedure_id')->nullable()->change();
            $table->decimal('final_price', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dental_charts', function (Blueprint $table) {
            //
        });
    }
};
