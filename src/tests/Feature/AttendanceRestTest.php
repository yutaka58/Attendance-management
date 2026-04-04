<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\WorkStatus;
use App\Models\WorkAction;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // マスターデータ作成
        WorkStatus::create(['id' => 2, 'name' => '出勤中']);
        WorkStatus::create(['id' => 3, 'name' => '休憩中']);
        WorkAction::create(['id' => 1, 'name' => '出勤']);
        WorkAction::create(['id' => 3, 'name' => '休憩入']);
        WorkAction::create(['id' => 4, 'name' => '休憩戻']);
    }

    private function createUser($name, $email)
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'work_status_id' => 2, // 出勤中
        ]);
    }

    /** ID 7:1 & 3: 休憩入・戻の基本機能 */
    public function test_rest_cycle()
    {
        $user = $this->createUser('RestUser', 'rest@example.com');
        Attendance::create(['user_id' => $user->id, 'user_name' => $user->name, 'work_action_id' => 1, 'created_at' => now()]);

        $this->actingAs($user)->post('/attendance', ['action_id' => 3]);
        $this->get('/attendance')->assertSee('休憩中')->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', ['action_id' => 4]);
        $this->get('/attendance')->assertSee('出勤中')->assertSee('休憩入');
    }

    /** 4: 2回目以降の休憩入・戻 */
    public function test_multiple_rests()
    {
        $user = $this->createUser('MultiUser', 'multi@example.com');
        Attendance::create(['user_id' => $user->id, 'user_name' => $user->name, 'work_action_id' => 1, 'created_at' => now()]);

        $this->actingAs($user);
        $this->post('/attendance', ['action_id' => 3]);
        $this->post('/attendance', ['action_id' => 4]);
        $this->post('/attendance', ['action_id' => 3]);

        $this->get('/attendance')->assertSee('休憩戻');
    }

    /** 5: 勤怠一覧画面に休憩時刻（合計時間）が正確に記録されている */
    public function test_rest_list_display()
    {
        $user = $this->createUser('ListUser', 'list' . time() . '@example.com');

        $targetDate = Carbon::create(2026, 4, 1, 10, 0, 0);

        // 1. 出勤レコード (9:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 1,
            'start_time' => '09:00',
            'created_at' => $targetDate->copy()->setTime(9, 0, 0),
        ]);

        // 2. 休憩入 (13:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 3,
            'rest_start' => '13:00',
            'created_at' => $targetDate->copy()->setTime(13, 0, 0),
        ]);

        // 3. 休憩戻 (14:00)
        Attendance::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'work_action_id' => 4,
            'rest_end' => '14:00',
            'created_at' => $targetDate->copy()->setTime(13, 0, 10),
        ]);

        // 4. 一覧画面取得 (4月を指定)
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);

        // 5. 検証
        $response->assertSee('01:00');
    }
}