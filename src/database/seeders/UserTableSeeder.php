<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Attendance;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(10)->create()->each(function ($user) {
            $count = 0;

            for ($i = 0; $count < 20 && $i < 30; $i++) {

            $date = now()->subDays($i);

                if ($date->isWeekend()) continue;

                // 1:出勤 (9:00)
                \App\Models\Attendance::create([
                    'user_id' => $user->id,
                    'work_action_id' => 1,
                    'created_at' => $date->copy()->hour(9)->minute(rand(0, 30)),
                ]);

                // 3:休憩入 (12:00)
                \App\Models\Attendance::create([
                    'user_id' => $user->id,
                    'work_action_id' => 3,
                    'created_at' => $date->copy()->hour(12)->minute(rand(0, 60)),
                ]);

                // 4:休憩戻 (13:00)
                \App\Models\Attendance::create([
                    'user_id' => $user->id,
                    'work_action_id' => 4,
                    'created_at' => $date->copy()->hour(13)->minute(rand(0, 60)),
                ]);

                // 2:退勤 (18:00)
                \App\Models\Attendance::create([
                    'user_id' => $user->id,
                    'work_action_id' => 2,
                    'created_at' => $date->copy()->hour(18)->minute(rand(0, 30)),
                ]);

                $count++;
            }
        });
    }
}
