<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkAction;

class WorkActionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $work_actions = [
            ['name' => '出勤'],
            ['name' => '退勤'],
            ['name' => '休憩入'],
            ['name' => '休憩戻'],
            ['name' =>  'お疲れ様でした。'],
        ];

        WorkAction::insert($work_actions);

    }
}
