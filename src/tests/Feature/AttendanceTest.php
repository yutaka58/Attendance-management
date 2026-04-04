<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 4:勤怠画面の日時情報が現在と一致するか
     */
    public function test_attendance_time_match()
    {
        // 1. 全てのミドルウェアをこのテスト中だけ無効化する
        $this->withoutMiddleware();

        // 2. マスターデータ作成
        $offStatus = \App\Models\WorkStatus::create(['name' => '勤務外']);
        \App\Models\WorkStatus::create(['name' => '出勤中']);

        // 3. ユーザー作成
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test' . time() . '@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'work_status_id' => $offStatus->id,
        ]);

        // 4. アクセス
        $response = $this->actingAs($user)->get('/attendance');

        // 5. 検証
        $response->assertStatus(200);

        // 日時表示の確認
        $today = \Carbon\Carbon::today()->isoFormat('YYYY年M月D日(ddd)');
        $response->assertSee($today);
    }
}