<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procedure;

class ProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $procedures = [
            ['id' => 1, 'name' => 'خلع', 'slug' => 'extracted', 'default_price' => 0],
            ['id' => 2, 'name' => 'زراعة', 'slug' => 'implant', 'default_price' => 0],
            ['id' => 3, 'name' => 'حشوة', 'slug' => 'filling', 'default_price' => 0],
            ['id' => 4, 'name' => 'سحب عصب', 'slug' => 'root-canal', 'default_price' => 0],
            ['id' => 5, 'name' => 'تاج (تلبيسة)', 'slug' => 'crown', 'default_price' => 0],
            ['id' => 8, 'name' => 'تنظيف', 'slug' => 'cleaning', 'default_price' => 0],
            ['id' => 9, 'name' => 'تقويم', 'slug' => 'ortho', 'default_price' => 0],
        ];

        foreach ($procedures as $proc) {
            Procedure::updateOrCreate(['id' => $proc['id']], $proc);
        }
    }
}