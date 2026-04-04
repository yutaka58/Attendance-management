<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Attendance;

use Carbon\Carbon;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $testUser = User::create([
                'name' => 'test',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);

        $this->createAttendanceForUser($testUser);

        User::factory()->count(10)->create()->each(function ($user) {
            $this->createAttendanceForUser($user);
        });
    }

private function createAttendanceForUser($user)
{
    $count = 0;
    for ($i = 1; $count < 30 && $i < 40; $i++) {
        $date = now()->subDays($i);
        if ($date->isWeekend()) continue;

        $baseDate = $date->format('Y-m-d');
        
        // 各時刻の生成
        $startTime = Carbon::parse($baseDate . ' 09:00:00')->addMinutes(rand(0, 30));
        $restStartTime = Carbon::parse($baseDate . ' 12:00:00')->addMinutes(rand(0, 15));
        $restEndTime = $restStartTime->copy()->addMinutes(60);
        $endTime = Carbon::parse($baseDate . ' 18:00:00')->addMinutes(rand(0, 60));

        // --- ★追加：合計時間の計算ロジック ---
        // 休憩時間（分）
        $restMinutes = $restStartTime->diffInMinutes($restEndTime); // 60分
        $restTimeStr = sprintf('%02d:%02d', floor($restMinutes / 60), $restMinutes % 60);

        // 勤務時間（分）＝ (出勤から退勤までの総分) - 休憩分
        $totalStayMinutes = $startTime->diffInMinutes($endTime);
        $workMinutes = max(0, $totalStayMinutes - $restMinutes);
        $workTimeStr = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);

        // 1:出勤
        Attendance::create([
            'user_id' => $user->id,
            'work_action_id' => 1,
            'start_time' => $startTime->format('H:i'),
            'created_at' => $startTime,
        ]);

        // 3:休憩入
        Attendance::create([
            'user_id' => $user->id,
            'work_action_id' => 3,
            'rest_start' => $restStartTime->format('H:i'),
            'created_at' => $restStartTime,
        ]);

        // 4:休憩戻
        Attendance::create([
            'user_id' => $user->id,
            'work_action_id' => 4,
            'rest_end' => $restEndTime->format('H:i'),
            'created_at' => $restEndTime,
        ]);

        // 2:退勤（ここに計算結果を入れる）
        Attendance::create([
            'user_id' => $user->id,
            'work_action_id' => 2,
            'end_time' => $endTime->format('H:i'),
            'total_rest_time' => $restTimeStr, // 👈 追加
            'work_time' => $workTimeStr,       // 👈 追加
            'created_at' => $endTime,
        ]);

        $count++;
    }
}
}
