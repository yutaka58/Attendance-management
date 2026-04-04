<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // マスタ準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID 12:1. 管理者がその日の全ユーザーの勤怠を正確に確認できる */
    public function test_admin_can_see_all_users_attendance_on_specific_day()
    {
        // 管理者作成
        $admin = User::factory()->create(['role' => 'admin']);
        
        // テスト用の複数ユーザー
        $userA = User::factory()->create(['name' => 'ユーザーA']);
        $userB = User::factory()->create(['name' => 'ユーザーB']);

        $today = Carbon::today();

        // ユーザーAの打刻 (09:00 - 18:00)
        Attendance::create([
            'user_id' => $userA->id,
            'user_name' => $userA->name,
            'work_action_id' => 1,
            'created_at' => $today->copy()->setTime(9, 0, 0),
        ]);
        Attendance::create([
            'user_id' => $userA->id,
            'user_name' => $userA->name,
            'work_action_id' => 2,
            'created_at' => $today->copy()->setTime(18, 0, 0),
        ]);

        // 1. 管理者でログインして一覧へ
        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        
        // 2. 両ユーザーの名前と、正確な時刻が表示されているか
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB'); // 打刻がなくても名前はリストに出る設計なら
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 3. 現在の日付が表示されているか
        $response->assertSee($today->isoFormat('YYYY年M月D日'));
    }

    /** 2. 「前日」「翌日」ボタンによる日付遷移のテスト */
    public function test_admin_attendance_list_navigation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();
        $tomorrow = $today->copy()->addDay();

        // --- 前日への遷移 ---
        // コントローラーが ?date=YYYY-MM-DD で受け取っていると想定
        $response = $this->actingAs($admin)->get("/admin/attendance/list?date=" . $yesterday->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($yesterday->isoFormat('YYYY年M月D日'));

        // --- 翌日への遷移 ---
        $response = $this->actingAs($admin)->get("/admin/attendance/list?date=" . $tomorrow->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($tomorrow->isoFormat('YYYY年M月D日'));
    }
}