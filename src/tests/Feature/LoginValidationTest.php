<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    
    // メールアドレスが未入力の場合、エラーメッセージが出るか確認するテスト
    public function test_email_is_required()
    {
        // メールアドレスが空の状態でログインを試す
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // セッションにエラーメッセージ「メールアドレスを入力してください」が含まれているか確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    // パスワードが未入力の場合、エラーメッセージが出るか確認するテスト
    public function test_password_is_required()
    {
        // パスワードが空の状態でログインを試す
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // セッションにエラーメッセージ「パスワードを入力してください」が含まれているか確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    // 登録されていないユーザーでログインしようとすると、エラーになるか確認するテスト
    public function test_login_fails_with_invalid_credentials()
    {
        // 存在しないユーザーのメール・パスワードでログインを試す
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        // 「ログイン情報が登録されていません」というエラーが出ることを確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // 正しいユーザー情報でログインできることを確認するテスト
    public function test_login_success_with_valid_credentials()
    {
        // ログインできるユーザーを事前に作っておく
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('password123'), //パスワードはbcryptで暗号化して保存
            'role' => 'user',
        ]);

        // 正しいメールとパスワードでログインを試す
        $response = $this->post('/login', [
            'email' => 'valid@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/'); // ログイン成功時は、トップページにリダイレクトされることを確認
        $this->assertAuthenticatedAs($user); // 認証されたユーザーが $user であることを確認
    }

    // 管理者アカウントで一般ユーザー用ログインフォームからログインできないことを確認するテスト
    public function test_admin_cannot_login_from_user_login_form()
    {
        // 管理者ユーザーを作成
        User::factory()->create([
            'email' => 'admin1@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー用フォームからログインを試みる
        $response = $this->post('/login', [
            'email' => 'admin1@example.com',
            'password' => 'adminpass'
        ]);

        // 管理者はログインできない旨のエラーが出ることを確認
        $response->assertSessionHasErrors(['email' => '一般ユーザーアカウントではありません']);
        // ゲスト（未ログイン）状態であることを確認
        $this->assertGuest();
    }
}
