<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class StampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 必要なマスタを準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID 11:1-3. 不適切な時間の入力に対するバリデーションテスト */
    public function test_time_correction_validation_errors()
    {
        $user = User::factory()->create(['name' => '津田 あすか']);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'created_at' => now(),
        ]);

        $url = "/attendance/detail/modify"; // timeCorrectionのルートへ

        // 1. 出勤時間 > 退勤時間
        $response = $this->actingAs($user)->from("/attendance/detail/2026-04-15")->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '18:00',
            'end_time' => '09:00',
            'remarks_column' => '修正理由',
        ]);
        $response->assertSessionHasErrors(['start_time' => '出勤時間が不適切な値です']);

        // 2. 休憩開始 > 退勤時間
        $response = $this->actingAs($user)->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'rest_start' => ['18:00'],
            'rest_end' => ['19:00'],
            'remarks_column' => '修正理由',
        ]);
        $response->assertSessionHasErrors(['rest_start.0' => '休憩時間が不適切な値です']);

        // 3. 備考未入力
        $response = $this->actingAs($user)->post($url, [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks_column' => '',
        ]);
        $response->assertSessionHasErrors(['remarks_column' => '備考を記入してください']);
    }

    /** 4-7. 修正申請後の申請一覧・承認フローのテスト */
    public function test_correction_request_flow()
    {
        $user = User::factory()->create(['name' => '津田 あすか']);
        $admin = User::factory()->create(['role' => 'admin']); // 管理者想定
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
        ]);

        // 1. 修正申請を保存
        $this->actingAs($user)->post("/attendance/detail/modify", [
            'attendance_id' => $attendance->id,
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks_column' => '電車遅延のため',
        ]);

        // 2. 申請一覧画面で自分の申請が表示されているか
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertSee('津田 あすか');
        $response->assertSee('承認待ち');

        // 3. 管理者が申請一覧画面を確認
        $response = $this->actingAs($admin)->get('/stamp_correction_request/list');
        $response->assertSee('津田 あすか');
        $response->assertSee('詳細'); // 詳細ボタンがあるか

        // 4. 承認済みへの遷移確認（管理者が承認したと仮定）
        $request = CorrectionRequest::where('user_id', $user->id)->first();
        $request->update(['status' => 1]); // 承認済みに手動更新

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertSee('承認済み');
        $response->assertSee('津田 あすか');
    }
}