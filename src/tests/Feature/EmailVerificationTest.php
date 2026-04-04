<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;
use App\Models\WorkStatus;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** ID 16:1. 会員登録と認証メールの送信テスト */
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 登録後に「メール認証待ち画面」へリダイレクトされるか
        $response->assertRedirect('/certification');

        // ユーザーが作成されているか
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 認証メール（通知）が送信されたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 2. メール認証導線画面（メール認証待ち画面）の表示テスト */
    public function test_verification_notice_screen_can_be_rendered()
    {
        // 未認証ユーザーを作成
        $user = User::factory()->unverified()->create();

        // 認証が必要なページ（/attendanceなど）にアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // 認証待ち画面へ飛ばされるか
        $response->assertRedirect('/certification');

        // 認証待ち画面を表示
        $response = $this->actingAs($user)->get('/certification');
        $response->assertStatus(200);
        $response->assertSee('認証メールを送信しました');
    }

    /** 3. メール認証の完了と勤怠登録画面への遷移テスト */
    public function test_user_can_verify_email_and_access_attendance()
    {
        // 1. 未認証ユーザーと、必要なマスタ（勤務外）を作成
        WorkStatus::firstOrCreate(['name' => '勤務外']);
        $user = User::factory()->unverified()->create();

        // 2. Laravel標準の署名付き認証URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 3. 認証URLにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // 4. 認証完了後のリダイレクト先（/attendance）を確認
        $response->assertRedirect('/attendance');

        // ユーザーの email_verified_at が埋まっているか確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 5. 勤怠登録画面が正常に表示されるか
        $response = $this->actingAs($user->fresh())->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');
    }
}