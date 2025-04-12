<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AdminLoginValidationTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力の場合、エラーメッセージが出るか確認するテスト
    public function test_email_is_required()
    {
        // メールアドレスが空の状態でログインを試す
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // パスワードが未入力の場合、エラーメッセージが出るか確認するテスト
    public function test_password_is_required()
    {
        // パスワードが空の状態でログインを試す
        $response = $this->post('/admin/login', [
            'email' => 'admin1@example.com',
            'password' => ''
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // 登録されていない管理者でログインしようとすると、エラーになるか確認するテスト
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin1@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // 正しい管理者の情報でログインできることを確認するテスト
    public function test_login_success_with_valid_credentials()
    {
        // ログインできる管理者を事前に作っておく
        User::factory()->create([
            'email' => 'admin1@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // 正しいメールとパスワードでログインを試す
        $response = $this->post('/admin/login', [
            'email' => 'admin1@example.com',
            'password' => 'adminpass'
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    // 一般ユーザーが管理者用ログインフォームからログインできないことを確認するテスト
    public function test_user_cannot_login_from_admin_login_form()
    {
        // 一般ユーザーを作成
        User::factory()->create([
            'email' => 'general1@example.com',
            'password' => bcrypt('password1'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // 管理者用フォームからログインしようとする
        $response = $this->post('/admin/login', [
            'email' => 'general1@example.com',
            'password' => 'password1',
        ]);

        $response->assertSessionHasErrors(['email' => '管理者アカウントではありません']);
        $this->assertGuest(); // ログインされていないことを確認
    }
}
