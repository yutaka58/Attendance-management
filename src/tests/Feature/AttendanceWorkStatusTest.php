<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\WorkStatus;
use Carbon\Carbon;

class AttendanceWorkStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 全てのテストで共通のミドルウェアを無効化（メール認証リダイレクト対策）
        $this->withoutMiddleware();

        // 必要なマスターデータを事前に作成
        WorkStatus::create(['name' => '勤務外']);
        WorkStatus::create(['name' => '出勤中']);
        WorkStatus::create(['name' => '休憩中']);
        WorkStatus::create(['name' => '退勤済']);
    }

    /**
     * 各ステータスが正しく表示されるかを確認する共通ロジック
     */
    private function assertStatusDisplay($statusName)
    {
        $status = WorkStatus::where('name', $statusName)->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_' . $statusName . '@example.com',
            'password' => bcrypt('password'),
            'work_status_id' => $status->id,
        ]);

        // ステータスの上書きを防ぐために本日の打刻データを作成
        if ($statusName !== '勤務外') {
            \App\Models\Attendance::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'work_action_id' => 1,
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee($statusName);
    }

    /** ID 5:1. ステータスが「勤務外」の場合 */
    public function test_attendance_status_off_work()
    {
        $this->assertStatusDisplay('勤務外');
    }

    /** 2. ステータスが「出勤中」の場合 */
    public function test_attendance_status_working()
    {
        $this->assertStatusDisplay('出勤中');
    }

    /** 3. ステータスが「休憩中」の場合 */
    public function test_attendance_status_resting()
    {
        $this->assertStatusDisplay('休憩中');
    }

    /** 4. ステータスが「退勤済」の場合 */
    public function test_attendance_status_finished()
    {
        $this->assertStatusDisplay('退勤済');
    }
}