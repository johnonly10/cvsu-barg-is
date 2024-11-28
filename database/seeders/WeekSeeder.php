<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('week_names')->truncate();

        $weeks = [
            ['week_number' => 1, 'name' => 'Week 1'],
            ['week_number' => 2, 'name' => 'Week 2'],
            ['week_number' => 3, 'name' => 'Week 3'],
            ['week_number' => 4, 'name' => 'Week 4'],
            ['week_number' => 5, 'name' => 'Week 5'],
            ['week_number' => 6, 'name' => 'Week 6'],
            
        ];

        DB::table('week_names')->insert($weeks);
    }
}
