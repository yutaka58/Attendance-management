<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WorkStatus;
use App\Models\WorkAction;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceWorkEndTest extends TestCase
{
    use RefreshDatabase;

    /** ID 8:1. 退勤処理の実行とステータス更新のテスト */
    public function test_clock_out_process_and_status_update()
    {
        // マスタ準備
        $workingStatus = WorkStatus::firstOrCreate(['name' => '出勤中']);
        $retiredStatus = WorkStatus::firstOrCreate(['name' => '退勤済']);
        $outStatus = WorkStatus::firstOrCreate(['name' => '勤務外']);
        
        $inAction = WorkAction::firstOrCreate(['name' => '出勤']);
        $outAction = WorkAction::firstOrCreate(['name' => '退勤']);
        WorkAction::firstOrCreate(['name' => '休憩入']);

        $user = User::factory()->create([
            'work_status_id' => $workingStatus->id,
        ]);

        // 今日の出勤打刻を作成
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => $inAction->id,
            'created_at' => now(),
        ]);

        // 1. 退勤ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        // 2. 退勤処理を実行
        $response = $this->actingAs($user)->post('/attendance', [
            'action_id' => $outAction->id,
        ]);

        $response->assertRedirect();

        // 3. 画面表示の検証
        $response = $this->actingAs($user->fresh())->get('/attendance');

        // HTMLに「退勤済」が含まれているか
        $response->assertSee('退勤済');
    }

    /** 2. 勤怠一覧画面に退勤時刻が正確に記録されているかのテスト */
    public function test_clock_out_time_appears_in_list()
    {
        $user = User::factory()->create();
        $targetDate = Carbon::create(2026, 4, 15);

        // 出勤データを作成
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1, // 出勤
            'start_time' => '09:00',
            'created_at' => $targetDate->copy()->setTime(9, 0, 0),
        ]);

        // 退勤データを作成
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 2, // 退勤
            'end_time' => '18:00',
            'created_at' => $targetDate->copy()->setTime(18, 0, 0),
        ]);

        // 勤怠一覧画面を表示 (4月分)
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);

        // 退勤時刻「18:00」が表示されているか確認
        $response->assertSee('18:00');
    }
}