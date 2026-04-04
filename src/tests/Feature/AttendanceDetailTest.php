<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // マスタデータの準備（出勤:1, 退勤:2, 休憩入:3, 休憩戻:4）
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
        WorkAction::firstOrCreate(['id' => 3, 'name' => '休憩入']);
        WorkAction::firstOrCreate(['id' => 4, 'name' => '休憩戻']);
    }

    /** ID 10:勤怠詳細画面の表示テスト（名前・日付・出退勤・休憩） */
    public function test_attendance_detail_page_display_correct_info()
    {
        // 1. ユーザー作成（名前を固定）
        $user = User::factory()->create(['name' => '津田 あすか']);
        $dateStr = '2026-04-15';
        $targetDate = Carbon::parse($dateStr);

        // 2. 打刻データ作成
        // 出勤 (09:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'created_at' => $targetDate->copy()->setTime(9, 0, 0),
        ]);

        // 休憩入 (12:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 3,
            'created_at' => $targetDate->copy()->setTime(12, 0, 0),
        ]);

        // 休憩戻 (13:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 4,
            'created_at' => $targetDate->copy()->setTime(13, 0, 0),
        ]);

        // 退勤 (18:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 2,
            'created_at' => $targetDate->copy()->setTime(18, 0, 0),
        ]);

        // 3. 詳細画面にアクセス
        $response = $this->actingAs($user)->get("/attendance/detail/{$dateStr}");

        $response->assertStatus(200);

        // 4. 各項目の検証
        // 名前
        $response->assertSee('津田 あすか');
        
        // 日付（コントローラーで $year, $month, $day に分解されているため、それらが含まれるか）
        $response->assertSee('2026');
        $response->assertSee('4');
        $response->assertSee('15');

        // 出勤・退勤時刻
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 休憩時刻
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}