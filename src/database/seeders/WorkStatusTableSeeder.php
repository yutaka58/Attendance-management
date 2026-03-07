<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkStatus;

class WorkStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $work_statuses = [
            '勤務外', '出勤中', '休憩中', '退勤済'
        ];

        foreach ($work_statuses as $work_status) {
            WorkStatus::create([
                'name' => $work_status
            ]);
        }
    }
}
