<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー1 → メール認証済み（ログインして機能確認可）
        User::create([
            'name' => 'user1',
            'email' => 'general1@example.com',
            'password' => Hash::make('password1'),
            'role' => 'user',
            'email_verified_at' => now(), // 認証済み
        ]);

        // 一般ユーザー2 → メール認証済み
        User::create([
            'name' => 'user2',
            'email' => 'general2@example.com',
            'password' => Hash::make('password2'),
            'role' => 'user',
            'email_verified_at' => now(), // 認証済み
        ]);

        // 管理者ユーザー → メール認証不要のため、完了扱いで問題なし
        User::create([
            'name' => 'admin1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
