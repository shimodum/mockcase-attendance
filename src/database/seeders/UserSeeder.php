<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ▼ 一般ユーザー1
        User::create([
            'name' => '一般ユーザー1',
            'email' => 'general1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(), //メール認証済みにしておく
        ]);

        // ▼ 一般ユーザー2
        User::create([
            'name' => '一般ユーザー2',
            'email' => 'general2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // ▼ 管理者ユーザー1
        User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
