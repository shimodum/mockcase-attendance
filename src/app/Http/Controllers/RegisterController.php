<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

        // ユーザー作成
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        // 作成したユーザーでログイン
        Auth::login($user);

        // メール認証通知を送信（Laravelが自動送信するので通常は省略可、明示してもOK）
         $user->sendEmailVerificationNotification();

        // メール認証誘導画面へリダイレクト
        return redirect()->route('verification.notice');
    }
}
