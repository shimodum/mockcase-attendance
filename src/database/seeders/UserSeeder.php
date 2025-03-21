<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => '一般ユーザー1',
            'email' => 'general1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(), //メール認証済みにしておく
        ]);

        User::create([
            'name' => '一般ユーザー2',
            'email' => 'general2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
