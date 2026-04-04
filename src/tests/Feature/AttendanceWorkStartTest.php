<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\WorkStatus;
use App\Models\WorkAction;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceWorkStartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 認証・リダイレクト制限をスキップ
        $this->withoutMiddleware();

        // 1. ステータスマスター作成
        WorkStatus::create(['name' => '勤務外']);
        WorkStatus::create(['name' => '出勤中']);
        WorkStatus::create(['name' => '退勤済']);

        // 2. アクションマスター作成
        WorkAction::create(['id' => 1, 'name' => '出勤']);
        WorkAction::create(['id' => 2, 'name' => '退勤']);
    }

    /** ID 6:1. 出勤ボタンが正しく機能する */
    public function test_attendance_punch_in_works()
    {
        $offStatus = WorkStatus::where('name', '勤務外')->first();
        $user = User::create([
            'name' => 'Punch Test User',
            'email' => 'punch_test@example.com',
            'password' => bcrypt('password'),
            'work_status_id' => $offStatus->id,
        ]);

        // 画面に「出勤」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理（POST）を実行
        $response = $this->post('/attendance', ['action_id' => 1]);

        // 処理後にステータスが「出勤中」に変わっているか確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** 2. 出勤は一日一回のみできる */
    public function test_attendance_cannot_punch_in_twice()
    {
        $finishedStatus = WorkStatus::where('name', '退勤済')->first();
        $user = User::create([
            'name' => 'Double Punch User',
            'email' => 'double@example.com',
            'password' => bcrypt('password'),
            'work_status_id' => $finishedStatus->id,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'created_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('<button type="submit" class="attendance__button">出勤</button>', false);

        $response->assertDontSee('value="出勤"');
        $response->assertDontSee('btn-attendance">出勤');
    }

    /** 3. 出勤時刻が勤怠一覧画面で確認できる */
    public function test_attendance_time_is_recorded_in_list()
    {
        $offStatus = WorkStatus::where('name', '勤務外')->first();
        $user = User::create([
            'name' => 'List Test User',
            'email' => 'list_test@example.com',
            'password' => bcrypt('password'),
            'work_status_id' => $offStatus->id,
        ]);

        // 現在時刻を固定（秒まで固定）
        $now = Carbon::create(2026, 4, 4, 21, 46, 0);
        Carbon::setTestNow($now);

        // 直接DBに「出勤」データを入れる
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'created_at' => $now,
        ]);

        // 勤怠一覧画面を表示
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        
        // 「21:46」が表示されているか確認
        $response->assertSee('21:46');

        Carbon::setTestNow();
    }
}