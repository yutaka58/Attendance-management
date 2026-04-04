<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use Carbon\Carbon;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 出勤:1, 退勤:2 のマスタを準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID 14:1. スタッフ一覧ページに全一般ユーザーの情報が表示されている */
    public function test_admin_can_see_all_staff_members()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['name' => 'スタッフA', 'email' => 'staff_a@example.com', 'role' => 'user']);
        $user2 = User::factory()->create(['name' => 'スタッフB', 'email' => 'staff_b@example.com', 'role' => 'user']);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('staff_a@example.com');
        $response->assertSee('スタッフB');
        $response->assertSee('staff_b@example.com');
    }

    /** 2. 選択したユーザーの勤怠一覧が正確に表示される */
    public function test_admin_can_view_selected_staff_attendance_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['name' => '検証対象者']);
        
        // 対象ユーザーの打刻データを作成
        Attendance::create([
            'user_id' => $targetUser->id,
            'user_name' => $targetUser->name,
            'work_action_id' => 1,
            'start_time' => '08:30',
            'created_at' => Carbon::now()->setTime(8, 30, 0),
        ]);

        // 管理者がそのユーザーの勤怠一覧へアクセス
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$targetUser->id}");

        $response->assertStatus(200);
        $response->assertSee('検証対象者');
        $response->assertSee('08:30');
    }

    /** 3-4. 前月・翌月ボタンによる月次遷移のテスト */
    public function test_admin_staff_attendance_navigation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create();
        
        $currentMonth = Carbon::now();
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 前月へ遷移
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$targetUser->id}?month=" . $prevMonth->format('Y-m'));
        $response->assertSee($prevMonth->format('Y-m'));

        // 翌月へ遷移
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$targetUser->id}?month=" . $nextMonth->format('Y-m'));
        $response->assertSee($nextMonth->format('Y-m'));
    }

    /** 5. 「詳細」ボタンから詳細画面に遷移する */
    public function test_admin_can_transition_to_detail_from_staff_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create();
        $dateStr = '2026-04-15';

        // 期待されるリンクが存在するか
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$targetUser->id}?month=2026-04");
        
        // 詳細リンクのパスを確認（プロジェクトのルート設計に合わせて調整してください）
        $response->assertSee("/admin/attendance/detail/{$targetUser->id}/{$dateStr}");

        // 実際に遷移できるか
        $detailResponse = $this->actingAs($admin)->get("/admin/attendance/detail/{$targetUser->id}/{$dateStr}");
        $detailResponse->assertStatus(200);
    }
}