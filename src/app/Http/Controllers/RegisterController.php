<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // 会員登録画面の表示
    public function showForm()
    {
        return view('auth.register');
    }

    // 会員登録処理
    public function store(RegisterRequest $request)
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // ユーザーを作成
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'=> 'user', // 一般ユーザーとして登録
        ]);

        // 登録後は勤怠登録画面（出勤前）へリダイレクト
        return redirect('/attendance');
    }
}
