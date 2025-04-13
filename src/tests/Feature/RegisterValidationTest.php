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
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // セッションに「名前を入力してください」というエラーがあることを確認
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // メールアドレス入力を促すエラーメッセージをチェック
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // パスワード未入力によるエラーが出ることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        // 8文字未満はエラーになることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function パスワードが不一致の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch',
        ]);

        // 一致していないエラーが出ることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function 正常な値で登録するとDBに登録される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // メール認証画面へリダイレクトされることを確認
        $response->assertRedirect(route('verification.notice'));

        // データベースにユーザーがちゃんと登録されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function 管理者ログイン画面で一般ユーザーIDでログインできない()
    {
        // 一般ユーザーを作成
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

        $response->assertSessionHasErrors(); // ログイン失敗のエラーが表示されること
        $this->assertGuest(); // ログイン状態ではないことを確認
    }

    /** @test */
    public function 一般ユーザーログイン画面で管理者IDでログインできない()
    {
        // 管理者ユーザーを作成
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

        $response->assertSessionHasErrors(); // ログイン失敗のエラーが表示されること
        $this->assertGuest(); // ログインしていない状態であること
    }
}
