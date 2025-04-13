<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 名前が未入力の場合、バリデーションメッセージが表示される()
    {
        // 「名前」が空の状態でユーザー登録を試みる
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // セッションに「お名前を入力してください」というエラーがあることを確認
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        // 「メールアドレス」が空の状態で登録を試みる
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 「メールアドレスを入力してください」というエラーメッセージが出ることを確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        // パスワードが空の状態で登録を試みる
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // 「パスワードを入力してください」というエラーが出ることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        // パスワードが短すぎる状態で登録を試みる
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        // 「パスワードは8文字以上で入力してください」というエラーが表示されることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function パスワードが不一致の場合、バリデーションメッセージが表示される()
    {
        // パスワードとパスワード確認が一致しない状態で登録を試みる
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch',
        ]);

        // 「パスワードと一致しません」というエラーが出ることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function 正常な値で登録するとDBに登録される()
    {
        // 入力値がすべて正しい場合に登録できるかをテスト
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後、メール認証画面にリダイレクトされることを確認
        $response->assertRedirect(route('verification.notice'));

        // DBにユーザーが登録されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function 管理者ログイン画面で一般ユーザーIDでログインできない()
    {
        // 「一般ユーザー」としてのユーザーを作成
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);

        // 管理者ログイン画面からログインを試みる
        $response = $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        // エラーが表示されてログインできないことを確認
        $response->assertSessionHasErrors(); // ログイン失敗のエラーが表示されること
        $this->assertGuest(); // ログイン状態ではないことを確認
    }

    /** @test */
    public function 一般ユーザーログイン画面で管理者IDでログインできない()
    {
        // 「管理者ユーザー」としてユーザーを作成
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin'
        ]);

        // 一般ユーザー用ログイン画面から管理者がログインを試みる
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpass',
        ]);

        // エラーが出てログインできないことを確認
        $response->assertSessionHasErrors(); // ログイン失敗のエラーが表示されること
        $this->assertGuest(); // ログインしていない状態であること
    }
}
