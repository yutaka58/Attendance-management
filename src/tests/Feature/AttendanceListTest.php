<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkAction;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // マスタデータ（出勤:1, 退勤:2, 休憩入:3, 休憩戻:4）を準備
        WorkAction::firstOrCreate(['id' => 1, 'name' => '出勤']);
        WorkAction::firstOrCreate(['id' => 2, 'name' => '退勤']);
    }

    /** ID:9:1. 自分の勤怠情報がすべて表示されている */
    public function test_user_can_see_own_attendance_list()
    {
        // 1. 名前を指定してユーザーを作成
        $user = User::factory()->create([
            'name' => '津田 あすか',
        ]);

        // 2. 勤怠データもこの名前に合わせて作成
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => '津田 あすか',
            'work_action_id' => 1, // 出勤
            'start_time' => '09:00',
            'created_at' => now()->setTime(9, 0, 0),
        ]);

        // 3. ログインして一覧画面を取得
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        // 4. 検証
        $response->assertSee('津田 あすか');
        $response->assertSee('09:00');
    }

    /** 2. 勤怠一覧ページに現在の月が表示されている */
    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();
        $currentMonth = Carbon::now()->format('Y-m');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($currentMonth);
    }

    /** 3. 「前月」ボタンを押すと前月の情報が表示される */
    public function test_navigation_to_previous_month()
    {
        $user = User::factory()->create();
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)->get("/attendance/list?month={$prevMonth}");

        $response->assertStatus(200);
        $response->assertSee($prevMonth);
    }

    /** 4. 「翌月」ボタンを押すと翌月の情報が表示される */
    public function test_navigation_to_next_month()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)->get("/attendance/list?month={$nextMonth}");

        $response->assertStatus(200);
        $response->assertSee($nextMonth);
    }

    /** 5. 「詳細」ボタンを押下すると勤怠詳細画面に遷移する */
    public function test_transition_to_detail_page()
    {
        $user = User::factory()->create();
        // 詳細リンクに含まれる日付
        $dateStr = '2026-04-30';

        // リンクが存在するか確認
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');
        $response->assertSee("/attendance/detail/{$dateStr}");

        // 実際に詳細ページへアクセスできるか
        $detailResponse = $this->actingAs($user)->get("/attendance/detail/{$dateStr}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
    }
}