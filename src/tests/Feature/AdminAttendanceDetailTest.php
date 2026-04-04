<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // マスタ準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID 13:1. 管理者が詳細画面で選択した情報を正確に確認できる */
    public function test_admin_can_view_specific_attendance_detail()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['name' => '対象 太郎']);
        $dateStr = '2026-04-15';

        // 対象ユーザーの打刻データ
        Attendance::create([
            'user_id' => $targetUser->id,
            'user_name' => $targetUser->name,
            'work_action_id' => 1,
            'start_time' => '09:00',
            'created_at' => Carbon::parse($dateStr)->setTime(9, 0, 0),
        ]);

        // 管理者として詳細画面へアクセス（URL構造は /admin/attendance/detail/{id} 等を想定）
        $response = $this->actingAs($admin)->get("/admin/attendance/detail/{$targetUser->id}/{$dateStr}");

        $response->assertStatus(200);
        $response->assertSee('対象 太郎');
        $response->assertSee('09:00');
    }

    /** 2-5. 管理者による修正時のバリデーションテスト */
    public function test_admin_correction_validation_errors()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $targetUser->id,
            'work_action_id' => 1,
            'created_at' => now(),
        ]);

        $url = "/admin/attendance/update"; // 管理者用更新ルート

        // 2. 出勤時間 > 退勤時間
        $response = $this->actingAs($admin)->from("/admin/attendance/detail/{$targetUser->id}/2026-04-15")->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '18:00',
            'end_time' => '09:00',
            'remarks_column' => '修正',
        ]);
        $response->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);

        // 3. 休憩開始 > 退勤時間
        $response = $this->actingAs($admin)->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'rest_start' => ['18:00'],
            'rest_end' => ['19:00'],
            'remarks_column' => '修正',
        ]);
        $response->assertSessionHasErrors(['rest_start.0' => '休憩時間が不適切な値です']);

        // 4. 休憩終了 > 退勤時間
        $response = $this->actingAs($admin)->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'rest_start' => ['12:00'],
            'rest_end' => ['18:00'],
            'remarks_column' => '修正',
        ]);
        $response->assertSessionHasErrors(['rest_end.0' => '休憩時間もしくは退勤時間が不適切な値です']);

        // 5. 備考未入力
        $response = $this->actingAs($admin)->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks_column' => '',
        ]);
        $response->assertSessionHasErrors(['remarks_column' => '備考を記入してください']);
    }
}