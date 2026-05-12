<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('invoices', function (Blueprint $table) {
            // نضيف فقط العمود المفقود لتجنب Duplicate column error
            if (!Schema::hasColumn('invoices', 'remaining_amount')) {
                $table->decimal('remaining_amount', 10, 2)->default(0)->after('paid_amount');
            }
        });
    }

    public function down() {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('remaining_amount');
        });
    }
};