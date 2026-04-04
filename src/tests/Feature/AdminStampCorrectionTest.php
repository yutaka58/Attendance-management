<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AdminStampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // マスタ準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID 15:1-2. 修正申請一覧（承認待ち・承認済み）の表示テスト */
    public function test_admin_can_see_pending_and_approved_requests()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => '申請 太郎']);

        // 1. 承認待ちの申請を作成
        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => 1,
            'status' => 0, // 承認待ち
            'remarks_column' => '修正理由A',
        ]);

        // 2. 承認済みの申請を作成
        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => 2,
            'status' => 1, // 承認済み
            'remarks_column' => '修正理由B',
        ]);

        // 承認待ちタブの確認
        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertSee('申請 太郎');
        $response->assertSee('修正理由A');
        $response->assertDontSee('修正理由B');

        // 承認済みタブの確認
        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');
        $response->assertSee('修正理由B');
        $response->assertDontSee('修正理由A');
    }

    /** 3. 修正申請の詳細画面の表示テスト */
    public function test_admin_can_view_correction_request_detail()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => '申請 次郎']);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => 1,
            'status' => 0,
            'remarks_column' => '詳細確認用',
        ]);

        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('申請 次郎');
        $response->assertSee('詳細確認用');
    }

    /** 4. 承認ボタン押下による勤怠情報の更新テスト */
    public function test_admin_can_approve_request_and_update_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        // 元の勤怠データ (09:00)
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'start_time' => '09:00',
            'created_at' => now(),
        ]);

        // 修正申請 (10:30 に変更したい)
        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0,
            'start_time' => '10:30', // 修正後の値
            'remarks_column' => '承認テスト',
        ]);

        // 承認処理を実行 (POST /stamp_correction_request/approve/{id} 等)
        $response = $this->actingAs($admin)->post("/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect();

        // 【検証1】申請のステータスが「承認済み(1)」になっているか
        $this->assertEquals(1, $request->fresh()->status);

        // 【検証2】Attendanceテーブルの値が修正後の値に更新されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:30',
        ]);
    }
}