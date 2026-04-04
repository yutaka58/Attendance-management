<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Event;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 1:名前が未入力
     */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * ID 1:メールアドレスが未入力
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ID 1:パスワードが8文字以下
     */
    public function test_password_characters_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * ID 1:パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '', // パスワードを空にする
            'password_confirmation' => '',
        ]);

        // エラーがあるか確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ID 1:確認用パスワードと一致しない
     */
    public function test_password_match_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    /**
     * ID 1:全ての項目が入力され、登録成功し勤怠画面へ遷移
     */
    public function test_registration_success_and_can_access_attendance_after_verify()
    {
        Event::fake();

        // 1. 会員登録を実行
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // この時点では「メール認証」画面へ遷移する
        $response->assertRedirect('/certification');

        // 2. 登録済みユーザーを取得し、「認証済み」状態にする
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $user->markEmailAsVerified();

        // 3. 認証済みユーザーとして「勤怠画面」にアクセス
        $response = $this->actingAs($user)->get('/attendance');
    }
}