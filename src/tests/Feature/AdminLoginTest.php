<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 3:メールアドレスが未入力
     */
    public function test_email_required()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ID 3:パスワードが未入力
     */
    public function test_password_required()
    {
        $response = $this->post('/login', [
            'email' => 'admintest@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ID 3:登録情報が一致しない
     */
    public function test_email_registration_required()
    {
        // テスト用のユーザーを作成
        $user = \App\Models\AdminUser::create([
            'name' => 'adminTest',
            'email' => 'admintest@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'name' => 'adminTest',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}